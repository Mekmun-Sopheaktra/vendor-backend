<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    use BaseApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $search = $request->query('search', null);

        // Group users based on the specified conditions
        $superusers = User::query()
            ->where('is_superuser', true)
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%$search%");
            })
            ->paginate($perPage, ['*'], 'superusers_page'); // Separate pagination for superusers

        $vendors = User::query()
            ->where('is_superuser', false)
            ->where('is_vendor', true)
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%$search%");
            })
            ->paginate($perPage, ['*'], 'vendors_page'); // Separate pagination for vendors

        $regularUsers = User::query()
            ->where('is_superuser', false)
            ->where('is_vendor', false)
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%$search%");
            })
            ->paginate($perPage, ['*'], 'regular_users_page'); // Separate pagination for regular users

        return $this->success([
            'superusers' => $superusers,
            'vendors' => $vendors,
            'users' => $regularUsers,
        ], 'Users grouped and retrieved successfully');
    }

    //show
    public function show(User $user)
    {
        return $this->success($user, 'User retrieved successfully');
    }

    //update
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'status' => 'required',
        ]);

        //prevent update if own user is trying to update their status to inactive
        if ($user->id === auth()->id() && $validatedData['status'] === 0) {
            return $this->failed(null,'You cannot deactivate your own account', 403);
        }

        $user->update($validatedData);

        return $this->success($user, 'User updated successfully');
    }
}

