<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Menu list for admin (including out-of-stock items),
     * filterable by category and free text search.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $menus = Menu::query()
            ->with('category:id,name')
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, int $categoryId) => $query->where('category_id', $categoryId),
            )
            ->when(
                $filters['search'] ?? null,
                fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'),
            )
            ->orderBy('name')
            ->get();

        return MenuResource::collection($menus);
    }

    /**
     * Add a new menu item to the catalogue.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = Menu::create($request->validated() + [
            'is_available' => $request->boolean('is_available', true),
        ]);

        return (new MenuResource($menu->loadMissing('category:id,name')))->response()->setStatusCode(201);
    }

    /**
     * Edit menu data: price, stock, availability, image, etc.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): MenuResource
    {
        $menu->update($request->validated());

        return new MenuResource($menu->loadMissing('category:id,name'));
    }

    /**
     * Menus that were referenced by an order record may not be deleted
     * (the order item price would break), hidden them instead.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        if ($menu->orderItems()->exists()) {
            abort(409, 'Menu sudah pernah dipesan sehingga tidak dapat dihapus. Gunakan is_available=false untuk menyembunyikannya.');
        }

        $menu->delete();

        return response()->json(['message' => 'Menu dihapus.']);
    }
}
