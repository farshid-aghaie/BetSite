<?php

namespace App\Policies\Admin\CardTransfer;

use App\Models\CardTransfer\CardTransfer;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardTransferPolicy
{
    use HandlesAuthorization;

    public function cardTransferIndex(User $user)
    {
        return $user->hasPermission('index card transfer');
    }

    public function cardTransferEdit(User $user, CardTransfer $cardTransfer)
    {
        return $user->hasPermission('edit card transfer');
    }

}
