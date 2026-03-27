<?php

namespace App\Policies;

use App\Models\MasterApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('List master applications');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterApplication  $masterApplication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, MasterApplication $masterApplication)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create master applications');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterApplication  $masterApplication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, MasterApplication $masterApplication)
    {
        return $user->can('Update master applications');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterApplication  $masterApplication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, MasterApplication $masterApplication)
    {
        return $user->can('Delete master applications');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterApplication  $masterApplication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, MasterApplication $masterApplication)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MasterApplication  $masterApplication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, MasterApplication $masterApplication)
    {
        //
    }
}
