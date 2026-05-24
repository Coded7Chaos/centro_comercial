<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Marcas;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarcasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Marcas');
    }

    public function view(AuthUser $authUser, Marcas $marcas): bool
    {
        return $authUser->can('View:Marcas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Marcas');
    }

    public function update(AuthUser $authUser, Marcas $marcas): bool
    {
        return $authUser->can('Update:Marcas');
    }

    public function delete(AuthUser $authUser, Marcas $marcas): bool
    {
        return $authUser->can('Delete:Marcas');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Marcas');
    }

    public function restore(AuthUser $authUser, Marcas $marcas): bool
    {
        return $authUser->can('Restore:Marcas');
    }

    public function forceDelete(AuthUser $authUser, Marcas $marcas): bool
    {
        return $authUser->can('ForceDelete:Marcas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Marcas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Marcas');
    }

    public function replicate(AuthUser $authUser, Marcas $marcas): bool
    {
        return $authUser->can('Replicate:Marcas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Marcas');
    }

}