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
            ->update([
                $role => ($action == 'allow' ? true : false)
            ]);
    }
}
