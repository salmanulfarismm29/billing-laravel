<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Get all active categories.
     */
    public function getAll(): Collection
    {
        return Category::active()->orderBy('name')->get();
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Get a category by ID.
     */
    public function getCategoryById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    /**
     * Soft delete a category.
     */
    public function deleteCategory(Category $category): bool
    {
        return $category->delete();
    }
}
