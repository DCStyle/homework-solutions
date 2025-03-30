<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $roleId = $request->role;
            $query->whereHas('roles', function($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Eager load roles
        $query->with('roles');

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.form', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|string|in:student,teacher,parent',
            'avatar' => 'nullable|image|max:2048',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
        ];

        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public/avatars', $filename);
            $userData['avatar'] = $filename;
        }

        $user = User::create($userData);

        // Assign roles if selected
        if ($request->has('roles')) {
            $user->roles()->attach($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được tạo thành công.');
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show form for editing a user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');
        return view('admin.users.form', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'user_type' => 'required|string|in:student,teacher,parent',
            'avatar' => 'nullable|image|max:2048',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_type' => $validated['user_type'],
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public/avatars', $filename);
            $userData['avatar'] = $filename;
        }

        $user->update($userData);

        // Handle roles
        if ($request->has('roles')) {
            // Check if user is removing admin role from themselves
            $isRemovingOwnAdmin = false;

            if ($user->id === auth()->id()) {
                $adminRole = Role::where('name', 'Administrator')->first();
                if ($adminRole) {
                    $currentHasAdmin = $user->roles->contains($adminRole->id);
                    $willHaveAdmin = in_array($adminRole->id, $request->roles ?? []);

                    if ($currentHasAdmin && !$willHaveAdmin) {
                        $isRemovingOwnAdmin = true;
                    }
                }
            }

            if ($isRemovingOwnAdmin) {
                return redirect()->route('admin.users.edit', $user)
                    ->with('error', 'Bạn không thể loại bỏ quyền quản trị của chính mình.');
            }

            $user->roles()->sync($request->roles);
        } else {
            // No roles selected - check if user is removing admin from themselves
            if ($user->id === auth()->id() && $user->isAdmin()) {
                return redirect()->route('admin.users.edit', $user)
                    ->with('error', 'Bạn không thể loại bỏ quyền quản trị của chính mình.');
            }

            $user->roles()->detach();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được cập nhật thành công.');
    }

    /**
     * Delete the specified user
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được xóa thành công.');
    }

    /**
     * Toggle administrator role for user
     */
    public function toggleAdmin(User $user)
    {
        $adminRole = Role::where('name', 'Administrator')->first();

        if (!$adminRole) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Không tìm thấy vai trò Quản trị viên.');
        }

        // Prevent removing admin status from yourself
        if ($user->id === auth()->id() && $user->isAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể loại bỏ quyền quản trị của chính mình.');
        }

        if ($user->roles->contains($adminRole->id)) {
            $user->roles()->detach($adminRole);
            $message = 'Đã loại bỏ quyền quản trị viên.';
        } else {
            $user->roles()->attach($adminRole);
            $message = 'Đã cấp quyền quản trị viên.';
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }
}
