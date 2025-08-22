<?php

namespace LaravelObfuscator\LaravelObfuscator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelObfuscator\LaravelObfuscator\Models\User;
use LaravelObfuscator\LaravelObfuscator\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::with(['projects'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:obfuscator_users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,developer,user',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->is_active ?? true,
            'api_key' => Str::random(64),
            'preferences' => $request->preferences ?? []
        ]);

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'user_created',
            'user',
            $user->id,
            ['email' => $user->email, 'role' => $user->role]
        );

        return response()->json([
            'success' => true,
            'data' => $user->load('projects'),
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['projects', 'auditLogs']);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User retrieved successfully'
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:obfuscator_users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:admin,developer,user',
            'is_active' => 'sometimes|boolean',
            'preferences' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $oldData = $user->toArray();
        
        $user->update($request->only([
            'name', 'email', 'role', 'is_active', 'preferences'
        ]));

        if ($request->has('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'user_updated',
            'user',
            $user->id,
            [
                'old_data' => $oldData,
                'new_data' => $user->toArray()
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $user->fresh()->load('projects'),
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()?->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 422);
        }

        // Log the action before deletion
        AuditLog::logAction(
            $request->user()?->id,
            'user_deleted',
            'user',
            $user->id,
            ['email' => $user->email, 'role' => $user->role]
        );

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Generate new API key for user.
     */
    public function regenerateApiKey(Request $request, User $user): JsonResponse
    {
        $oldKey = $user->api_key;
        $user->update(['api_key' => Str::random(64)]);

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'api_key_regenerated',
            'user',
            $user->id,
            ['old_key' => $oldKey]
        );

        return response()->json([
            'success' => true,
            'data' => ['api_key' => $user->api_key],
            'message' => 'API key regenerated successfully'
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics(User $user): JsonResponse
    {
        $stats = [
            'total_projects' => $user->projects()->count(),
            'active_projects' => $user->projects()->where('status', 'active')->count(),
            'total_files_processed' => $user->projects()->withCount('files')->get()->sum('files_count'),
            'storage_used' => $user->projects()->sum('storage_used'),
            'last_activity' => $user->last_login_at,
            'recent_actions' => $user->auditLogs()->latest()->take(10)->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'User statistics retrieved successfully'
        ]);
    }
}
