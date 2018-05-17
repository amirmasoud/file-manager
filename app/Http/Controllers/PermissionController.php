<?php

namespace App\Http\Controllers;

use App\FilePath;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function takeAction($role, $action, $file)
    {
        // Should be dynamic
        $roles = ['anonymouse', 'authenticated_user', 'premium', 'vip', 'administrator'];
        $actions = ['allow', 'disallow'];

        // Should be middleware
        if (!in_array($role, $roles) || !in_array($action, $actions)) {
            return;
        }

        FilePath::findOrFail($file)
            ->where('path', 'LIKE', $this->getPlaylistsAndSegments($file) . '%')
            ->update([
                $role => ($action == 'allow' ? true : false)
            ]);
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
        return $implode;
    }
}
