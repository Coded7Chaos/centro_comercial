<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Categorias;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoriasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Categorias');
    }

    public function view(AuthUser $authUser, Categorias $categorias): bool
    {
        return $authUser->can('View:Categorias');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Categorias');
    }

    public function update(AuthUser $authUser, Categorias $categorias): bool
    {
        return $authUser->can('Update:Categorias');
    }

    public function delete(AuthUser $authUser, Categorias $categorias): bool
    {
        return $authUser->can('Delete:Categorias');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Categorias');
    }

    public function restore(AuthUser $authUser, Categorias $categorias): bool
    {
        return $authUser->can('Restore:Categorias');
    }

    public function forceDelete(AuthUser $authUser, Categorias $categorias): bool
    {
        return $authUser->can('ForceDelete:Categorias');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Categorias');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Categorias');
    }

    public function replicate(AuthUser $authUser, Categorias $categorias): bool
    {
        return $authUser->can('Replicate:Categorias');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Categorias');
    }

}