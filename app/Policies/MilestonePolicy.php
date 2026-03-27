<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MilestonePolicy
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
        return $user->can('List milestones');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Milestone $milestone)
    {
        // return $user->can('View milestone');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create milestone');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Milestone $milestone)
    {
        return $user->can('Update milestone');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Milestone $milestone)
    {
        return $user->can('Delete milestone');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Milestone $milestone)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Milestone $milestone)
    {
        //
    }
}
