<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * List of categories along with the number of menus.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->withCount('menus')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Register a new menu category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    /**
     * Update the category name.
     */
    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Category removal is refused when there is still a menu attached,
     * so that the data history is not missing its group.
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->menus()->exists()) {
            abort(409, 'Kategori masih digunakan menu, pindahkan menu terlebih dahulu.');
        }

        $category->delete();

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
