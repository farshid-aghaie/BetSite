<?php

namespace App\Policies\Admin\Admin;

use App\Models\Admin\Admin;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    public function adminIndex(User $user)
    {
        return $user->hasPermission('index admin');
    }

    public function adminCreate(User $user)
    {
        return $user->hasPermission('create admin');
    }

    public function adminEdit(User $user, Admin $admin)
    {
        return $user->hasPermission('edit admin');
    }

    public function adminDestroy(User $user, Admin $admin)
    {
        return $user->hasPermission('destroy admin');
    }

    public function adminSoftDelete(User $user, Admin $admin)
    {
        return $user->hasPermission('delete admin');
    }

    public function adminRestore(User $user, Admin $admin)
    {
        return $user->hasPermission('restore admin');
    }
}
