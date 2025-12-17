<?php

namespace Modules\UsersGuard\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsersGuard\ManagePermissionsRequest;
use App\Http\Requests\UsersGuard\StoreUserRequest;
use App\Http\Requests\UsersGuard\UpdateUserRequest;
use App\Http\Resources\UsersGuard\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\UsersGuard\Entities\TenantUser;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $application = $request->input('application');
        $isActive = $request->input('is_active');

        $query = TenantUser::query()->with(['roles', 'permissions']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%");
            });
        }

        if ($application) {
            $query->where('application', $application);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $users = $query->latest()->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['password'] = bcrypt($data['password']);
        $data['is_active'] = $data['is_active'] ?? true;

        $user = TenantUser::create($data);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        $user = TenantUser::with(['roles', 'permissions'])->findOrFail($id);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = TenantUser::findOrFail($id);

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return response()->json([
            'message' => 'Utilisateur modifié avec succès.',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ]);
    }

    /**
     * Remove the specified user (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        $user = TenantUser::findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès.',
        ]);
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(string $id): JsonResponse
    {
        $user = TenantUser::onlyTrashed()->findOrFail($id);

        $user->restore();

        return response()->json([
            'message' => 'Utilisateur restauré avec succès.',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ]);
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete(string $id): JsonResponse
    {
        $user = TenantUser::withTrashed()->findOrFail($id);

        $user->forceDelete();

        return response()->json([
            'message' => 'Utilisateur supprimé définitivement.',
        ]);
    }

    /**
     * Add permissions to a user.
     */
    public function addPermissions(ManagePermissionsRequest $request, string $id): JsonResponse
    {
        $user = TenantUser::findOrFail($id);

        $permissions = $request->validated()['permissions'];

        $user->givePermissionTo($permissions);

        return response()->json([
            'message' => 'Permissions ajoutées avec succès.',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ]);
    }

    /**
     * Remove permissions from a user.
     */
    public function removePermissions(ManagePermissionsRequest $request, string $id): JsonResponse
    {
        $user = TenantUser::findOrFail($id);

        $permissions = $request->validated()['permissions'];

        $user->revokePermissionTo($permissions);

        return response()->json([
            'message' => 'Permissions retirées avec succès.',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ]);
    }

    /**
     * Sync user permissions (replace all permissions).
     */
    public function syncPermissions(ManagePermissionsRequest $request, string $id): JsonResponse
    {
        $user = TenantUser::findOrFail($id);

        $permissions = $request->validated()['permissions'];

        $user->syncPermissions($permissions);

        return response()->json([
            'message' => 'Permissions synchronisées avec succès.',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ]);
    }
}
