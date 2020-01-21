<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OrderRefund;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderRefundPolicy
{
    use HandlesAuthorization;

    public function own(User $user, OrderRefund $orderRefund)
    {
        return $user->id == $orderRefund->user_id;
    }
}
