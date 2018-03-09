<?php

namespace App\Http\Controllers;

use App\FilePath;
use Illuminate\Http\Request;
use Unisharp\Laravelfilemanager\controllers\LfmController;

class ItemsController extends LfmController
{
    /**
     * Get the images to load for a selected folder
     *
     * @return mixed
     */
    public function getItems()
    {
        $internal_path = pathinfo(parent::getCurrentPath());
        if (! isset($internal_path['extension'])) {
            $internal_path = parent::getCurrentPath();
        } else {
            $internal_path = $internal_path['dirname'];
        }
        $sort_type = request('sort_type');

        $files = parent::sortFilesAndDirectories(parent::getFilesWithInfo($internal_path), $sort_type);
        $directories = parent::sortFilesAndDirectories(parent::getDirectories($internal_path), $sort_type);
        $w_dir = request('working_dir') == '/' ? '' : preg_replace("/^\/\//", "/", request('working_dir'));
        foreach ($files as $file) {
            $path = storage_path('app') . $w_dir . '/' . $file->name;
            $file->permission = FilePath::where('path', $path)->firstOrCreate(['path' => $path])->toArray();
            $file->download = route('download') . '?file=' . $w_dir . '/' . $file->name;
        }
        return [
            'html' => (string)view($this->getView())->with([
                'files'       => $files,
                'directories' => $directories,
                'items'       => array_merge($directories, $files),
            ]),
            'working_dir' => parent::getInternalPath($internal_path)
        ];
    }

    private function getView()
    {
        $view_type = 'grid';
        $show_list = request('show_list');

        if ($show_list === "1") {
            $view_type = 'list';
        } elseif (is_null($show_list)) {
            $type_key = parent::currentLfmType();
            $startup_view = config('lfm.' . $type_key . 's_startup_view');

            if (in_array($startup_view, ['list', 'grid'])) {
                $view_type = $startup_view;
            }
        }

        return 'laravel-filemanager::' . $view_type . '-view';
    }
}
