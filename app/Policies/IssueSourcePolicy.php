<?php

namespace App\Policies;

use App\Models\IssueSource;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IssueSourcePolicy
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
        return $user->can('List issue sources');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\IssueSource  $issueSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, IssueSource $issueSource)
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
        return $user->can('Create issue sources');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\IssueSource  $issueSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, IssueSource $issueSource)
    {
        return $user->can('Update issue sources');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\IssueSource  $issueSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, IssueSource $issueSource)
    {
        return $user->can('Delete issue sources');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\IssueSource  $issueSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, IssueSource $issueSource)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\IssueSource  $issueSource
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, IssueSource $issueSource)
    {
        //
    }
}
