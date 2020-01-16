<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * @param User  $user
     * @param Order $order
     *
     * @return bool
     */
    public function own(User $user, Order $order)
    {
        return $user->id == $order->user_id;
    }
}
