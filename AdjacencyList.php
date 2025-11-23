<?php

namespace App\Libraries;

use App\Models\CategoryModel;
use CodeIgniter\Cache\CacheInterface;

class AdjacencyList
{
    protected CategoryModel $model;
    protected CacheInterface $cache;

    public function __construct()
    {
        $this->model = new CategoryModel();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Add a new node
     */
    public function addNode(string $name, ?int $parentId = null): int
    {
        $id = $this->model->insert(['name' => $name, 'parent_id' => $parentId]);
        $this->clearCache($parentId ?? 0);
        return $id;
    }

    /**
     * Update a node
     */
    public function updateNode(int $id, string $name, ?int $parentId = null): bool
    {
        $success = (bool)$this->model->update($id, ['name' => $name, 'parent_id' => $parentId]);
        if ($success) $this->clearCache($parentId ?? 0);
        return $success;
    }

    /**
     * Delete a node recursively
     */
    public function deleteNode(int $id): bool
    {
        $children = $this->model->getChildren($id);
        foreach ($children as $child) {
            $this->deleteNode($child['id']);
        }
        $parentId = $this->model->find($id)['parent_id'] ?? 0;
        $success = (bool)$this->model->delete($id);
        if ($success) $this->clearCache($parentId);
        return $success;
    }

    /**
     * Clear tree caches
     */
    public function clearCache(int $parentId = 0): void
    {
        $this->cache->delete("category_tree_{$parentId}");
        $this->cache->delete("category_flat_tree_{$parentId}");
    }

    /**
     * Get tree with caching (optimized CTE)
     */
    public function getTreeCached(int $parentId = 0): array
    {
        $cacheKey = "category_tree_{$parentId}";
        $tree = $this->cache->get($cacheKey);
        if (!$tree) {
            $tree = $this->model->getTreeOptimized($parentId);
            $this->cache->save($cacheKey, $tree, 3600);
        }
        return $tree;
    }

    /**
     * Get flat tree with depth levels and caching
     */
    public function getFlatTreeCached(int $parentId = 0): array
    {
        $cacheKey = "category_flat_tree_{$parentId}";
        $tree = $this->cache->get($cacheKey);
        if (!$tree) {
            $tree = $this->model->getFlatTree($parentId);
            $this->cache->save($cacheKey, $tree, 3600);
        }
        return $tree;
    }

    /**
     * Get children of a node
     */
    public function getChildren(int $id): array
    {
        return $this->model->getChildren($id);
    }

    /**
     * Get parent of a node
     */
    public function getParent(int $id): ?array
    {
        return $this->model->getParent($id);
    }

    /**
     * Get path to root
     */
    public function getPath(int $id): array
    {
        return $this->model->getPath($id);
    }

    /**
     * Get breadcrumb for a node
     */
    public function getBreadcrumb(int $id): array
    {
        return $this->model->getBreadcrumb($id);
    }
}
