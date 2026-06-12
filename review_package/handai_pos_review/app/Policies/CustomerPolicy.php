<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Customer $customer)
    {
        // allow if user owns the store or created the customer
        return $user->id === $customer->created_by
            || ($customer->store && $customer->store->owner_id === $user->id);
    }

    public function create(User $user)
    {
        // maybe everyone with manager role can create
        return $user->hasRole('Manager-Marketing');
    }

    public function update(User $user, Customer $customer)
    {
        return $this->view($user, $customer);
    }

    public function delete(User $user, Customer $customer)
    {
        return $this->view($user, $customer);
    }
}
