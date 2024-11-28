<?php

namespace App\Traits;

use App\Constants\RoleConstants;
use App\Http\Requests\Validator;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

trait CheckUserPermission
{
    public function userPermission(User $user)
    {
        $user = User::query()->select('id', 'email', 'is_superuser', 'is_vendor')->where('email', $user->email)->first();
        if ($user->is_superuser) {
            return RoleConstants::SUPERUSER;
        }

        if ($user->is_vendor) {
            return RoleConstants::VENDOR;
        }

        //check if user is user
        if (! $user->is_vendor && ! $user->is_superuser) {
            return RoleConstants::USER;
        }

        throw new HttpResponseException(Validator::failed('Permission denied', 'Permission', 403));
    }

    public function userRole($role)
    {
        if ($role === RoleConstants::SUPERUSER) {
            return RoleConstants::getStatusFromString(RoleConstants::SUPERUSER);
        }

        if ($role === RoleConstants::VENDOR) {
            return RoleConstants::getStatusFromString(RoleConstants::VENDOR);
        }

        if ($role === RoleConstants::USER) {
            return RoleConstants::getStatusFromString(RoleConstants::USER);
        }

        throw new HttpResponseException(Validator::failed('Permission denied', 'Permission', 403));
    }

    public function userPermissionRole($user)
    {
        if (! $user) {
            return RoleConstants::PUBLIC;
        }

        $role = $this->userRole($this->userPermission($user));

        $permissions = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $role)
            ->select('id', 'permissions.name', 'permissions.guard_name')
            ->get();

        if ($permissions->isEmpty()) {
            return RoleConstants::PUBLIC;
        }

        return $permissions;
    }

}
