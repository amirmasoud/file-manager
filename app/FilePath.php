<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FilePath extends Model
{
    protected $table = "file_paths";
    protected $guarded = [];

    public function roles()
    {
        $roles = [];
        $available_roles = ['anonymouse', 'authenticated user', 'premium', 'vip', 'administrator'];
        foreach ($available_roles as $ar) {
            $ar_col = str_replace(' ', '_', $ar);
            if ($this->{$ar_col}) {
                $roles[] = $ar;
            }
        }
        return $roles;
    }
}
