<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Infraestructuras;
use Illuminate\Auth\Access\HandlesAuthorization;

class InfraestructurasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Infraestructuras');
    }

    public function view(AuthUser $authUser, Infraestructuras $infraestructuras): bool
    {
        return $authUser->can('View:Infraestructuras');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'admin']);
    }

    public function update(AuthUser $authUser, Infraestructuras $infraestructuras): bool
    {
        return $authUser->can('Update:Infraestructuras');
    }

    public function delete(AuthUser $authUser, Infraestructuras $infraestructuras): bool
    {
        return $authUser->can('Delete:Infraestructuras');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Infraestructuras');
    }

    public function restore(AuthUser $authUser, Infraestructuras $infraestructuras): bool
    {
        return $authUser->can('Restore:Infraestructuras');
    }

    public function forceDelete(AuthUser $authUser, Infraestructuras $infraestructuras): bool
    {
        return $authUser->can('ForceDelete:Infraestructuras');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Infraestructuras');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Infraestructuras');
    }

    public function replicate(AuthUser $authUser, Infraestructuras $infraestructuras): bool
    {
        return $authUser->can('Replicate:Infraestructuras');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Infraestructuras');
    }

}