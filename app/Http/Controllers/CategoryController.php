<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * Display a listing of all active categories.
     */
    public function getAllCategories(): JsonResponse
    {
        $categories = $this->categoryService->getAll();
        return encryptResponse(200, 'success', 'Categories retrieved', $categories);
    }

    /**
     * Store a newly created category.
     */
    public function addCategory(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());
        return encryptResponse(201, 'success', 'Category created successfully', $category);
    }

    /**
     * Update the specified category.
     */
    public function updateCategory(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = Category::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Category not found');
        }

        $category = $this->categoryService->getCategoryById($id);
        $category = $this->categoryService->updateCategory($category, $request->only(['name', 'is_active']));

        return encryptResponse(200, 'success', 'Category updated successfully', $category);
    }

    /**
     * Remove the specified category.
     */
    public function deleteCategory(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = Category::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'Category not found');
        }

        $category = $this->categoryService->getCategoryById($id);
        $this->categoryService->deleteCategory($category);

        return encryptResponse(200, 'success', 'Category deleted successfully');
    }
}
