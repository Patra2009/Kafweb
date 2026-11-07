<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    /**
     * Public catalogue for customers: only menus that are currently available.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $menus = Menu::query()
            ->with('category:id,name')
            ->available()
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->orderBy('name')
            ->get();

        return MenuResource::collection($menus);
    }

    /**
     * Menu detail page of the customer app.
     */
    public function show(Menu $menu): MenuResource
    {
        abort_unless($menu->is_available, 404);

        return new MenuResource($menu->loadMissing('category:id,name'));
    }
}
