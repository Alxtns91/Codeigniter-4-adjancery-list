<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'parent_id'];
    protected $useTimestamps = true;

    /**
     * Get children of a node
     */
    public function getChildren(int $parentId): array
    {
        return $this->where('parent_id', $parentId)->findAll();
    }

    /**
     * Get parent of a node
     */
    public function getParent(int $id): ?array
    {
        $category = $this->find($id);
        if (!$category || !$category['parent_id']) return null;
        return $this->find($category['parent_id']);
    }

    /**
     * Get path from node to root
     */
    public function getPath(int $id): array
    {
        $path = [];
        $current = $this->find($id);
        while ($current) {
            $path[] = $current;
            if (!$current['parent_id']) break;
            $current = $this->find($current['parent_id']);
        }
        return array_reverse($path);
    }

    /**
     * Get full tree recursively (PHP-based)
     */
    public function getTree(int $parentId = 0): array
    {
        $children = $this->where('parent_id', $parentId)->findAll();
        $tree = [];
        foreach ($children as $child) {
            $child['children'] = $this->getTree($child['id']);
            $tree[] = $child;
        }
        return $tree;
    }

    /**
     * Get full tree using optimized CTE (single-query)
     */
    public function getTreeOptimized(int $parentId = 0): array
    {
        $sql = "
            WITH RECURSIVE cte AS (
                SELECT id, name, parent_id
                FROM {$this->table}
                WHERE id = ?
                UNION ALL
                SELECT c.id, c.name, c.parent_id
                FROM {$this->table} c
                INNER JOIN cte ON c.parent_id = cte.id
            )
            SELECT * FROM cte;
        ";
        $query = $this->db->query($sql, [$parentId]);
        $rows = $query->getResultArray();
        return $this->buildTree($rows);
    }

    /**
     * Get flat tree with depth levels
     */
    public function getFlatTree(int $parentId = 0): array
    {
        $sql = "
            WITH RECURSIVE cte AS (
                SELECT id, name, parent_id, 0 AS depth
                FROM {$this->table}
                WHERE id = ?
                UNION ALL
                SELECT c.id, c.name, c.parent_id, cte.depth + 1
                FROM {$this->table} c
                INNER JOIN cte ON c.parent_id = cte.id
            )
            SELECT * FROM cte ORDER BY depth, id;
        ";
        $query = $this->db->query($sql, [$parentId]);
        return $query->getResultArray();
    }

    /**
     * Get breadcrumb path (names) for a node
     */
    public function getBreadcrumb(int $id): array
    {
        $path = [];
        $current = $this->find($id);
        while ($current) {
            $path[] = $current['name'];
            if (!$current['parent_id']) break;
            $current = $this->find($current['parent_id']);
        }
        return array_reverse($path);
    }

    /**
     * Convert flat array to tree structure
     */
    protected function buildTree(array $elements, int $parentId = 0): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ((int)$element['parent_id'] === (int)$parentId) {
                $children = $this->buildTree($elements, $element['id']);
                $element['children'] = $children ?: [];
                $branch[] = $element;
            }
        }
        return $branch;
    }
}
