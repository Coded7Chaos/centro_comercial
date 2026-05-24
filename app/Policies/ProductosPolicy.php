<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Productos;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductosPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Productos');
    }

    public function view(AuthUser $authUser, Productos $productos): bool
    {
        return $authUser->can('View:Productos');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Productos');
    }

    public function update(AuthUser $authUser, Productos $productos): bool
    {
        return $authUser->can('Update:Productos');
    }

    public function delete(AuthUser $authUser, Productos $productos): bool
    {
        return $authUser->can('Delete:Productos');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Productos');
    }

    public function restore(AuthUser $authUser, Productos $productos): bool
    {
        return $authUser->can('Restore:Productos');
    }

    public function forceDelete(AuthUser $authUser, Productos $productos): bool
    {
        return $authUser->can('ForceDelete:Productos');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Productos');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Productos');
    }

    public function replicate(AuthUser $authUser, Productos $productos): bool
    {
        return $authUser->can('Replicate:Productos');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Productos');
    }

}