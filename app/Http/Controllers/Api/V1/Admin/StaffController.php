<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Staff list (admin and kasir) filterable by role.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $role = $request->validate([
            'role' => ['sometimes', 'nullable', Rule::enum(UserRole::class)],
        ])['role'] ?? null;

        $staff = User::query()
            ->when($role !== null, fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 15), 50));

        return StaffResource::collection($staff);
    }

    /**
     * Add staff member (admin or kasir) with a hashed password
     * that is done automatically by the model's 'hashed' cast.
     */
    public function store(StoreStaffRequest $request): JsonResponse
    {
        $staff = User::create($request->validated());

        return (new StaffResource($staff))->response()->setStatusCode(201);
    }

    /**
     * Edit staff member data; password is only changed when sent.
     */
    public function update(UpdateStaffRequest $request, User $staff): StaffResource
    {
        $data = collect($request->validated())
            ->filter(fn ($value): bool => $value !== null)
            ->all();

        $staff->update($data);

        return new StaffResource($staff);
    }

    /**
     * Remove a staff member. Guardrails:
     * the admin may not remove his own account, and the last admin
     * may never be removed so the system remains manageable.
     */
    public function destroy(Request $request, User $staff): JsonResponse
    {
        if ($staff->getKey() === $request->user()->getKey()) {
            abort(409, 'anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($staff->role === UserRole::Admin && User::query()->where('role', UserRole::Admin)->count() <= 1) {
            abort(409, 'Tidak boleh menghapus admin terakhir.');
        }

        $staff->delete();

        return response()->json(['message' => 'Staf dihapus.']);
    }
}
