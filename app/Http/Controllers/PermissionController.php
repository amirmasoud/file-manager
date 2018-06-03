<?php

namespace App\Http\Controllers;

use App\FilePath;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    private $isSingle = true;

    public function takeAction($role, $action, $file)
    {
        // Should be dynamic
        $roles = ['anonymouse', 'authenticated_user', 'premium', 'vip', 'administrator'];
        $actions = ['allow', 'disallow'];

        // Should be middleware
        if (!in_array($role, $roles) || !in_array($action, $actions)) {
            return;
        }
        $path = FilePath::findOrFail($file)->path;
        $root = $this->getPlaylistsAndSegments($path);
        if (! $this->isSingle) {
            FilePath::findOrFail($file)
                ->where('path', 'LIKE', $root . '%')
                ->update([
                    $role => ($action == 'allow' ? true : false)
                ]);
        } else {
            FilePath::findOrFail($file)
                ->update([
                    $role => ($action == 'allow' ? true : false)
                ]);
        }
    }

    /**
     * Get file directory only to find all playlists and segments of a streaming
     * video.
     *
     * @param  string $file
     * @return string
     */
    private function getPlaylistsAndSegments($file)
    {
        $explode = explode('/', $file);
        array_pop($explode);
        $implode = implode('/', $explode);
        if (is_numeric(end($explode))) {
            $this->isSingle = false;
        }
        return $implode;
    }
}
