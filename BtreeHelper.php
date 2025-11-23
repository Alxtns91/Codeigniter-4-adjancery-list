<?php

if (!function_exists('build_bootstrap_menu')) {

    /**
     * Build a Bootstrap 5 collapsible multi-level menu
     *
     * @param array $tree The hierarchical tree
     * @param string $parentId Unique ID prefix for collapse elements
     * @return string HTML
     */
    function build_bootstrap_menu(array $tree, string $parentId = 'menu'): string
    {
        $html = '<ul class="list-group">';

        foreach ($tree as $node) {
            $collapseId = $parentId . '_' . $node['id'];
            $hasChildren = !empty($node['children']);

            $html .= '<li class="list-group-item">';
            
            if ($hasChildren) {
                $html .= '<a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#' . $collapseId . '" role="button" aria-expanded="false" aria-controls="' . $collapseId . '">';
                $html .= $node['name'];
                $html .= '<span class="badge bg-primary rounded-pill">+</span>';
                $html .= '</a>';
                $html .= '<div class="collapse mt-1" id="' . $collapseId . '">';
                $html .= build_bootstrap_menu($node['children'], $collapseId);
                $html .= '</div>';
            } else {
                $html .= $node['name'];
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }
}

if (!function_exists('build_bootstrap_table')) {

    /**
     * Build a Bootstrap 5 striped table for hierarchical data
     *
     * @param array $tree The hierarchical tree
     * @param int $level Current depth level (used internally)
     * @param array $options ['indent' => 20]
     * @return string HTML
     */
    function build_bootstrap_table(array $tree, int $level = 0, array $options = []): string
    {
        $indent = $options['indent'] ?? 20;
        $html = '';

        if ($level === 0) {
            $html .= '<table class="table table-striped table-bordered">';
            $html .= '<thead><tr><th>Name</th></tr></thead><tbody>';
        }

        foreach ($tree as $node) {
            $padding = $level * $indent;
            $html .= '<tr><td style="padding-left: ' . $padding . 'px">' . $node['name'] . '</td></tr>';

            if (!empty($node['children'])) {
                $html .= build_bootstrap_table($node['children'], $level + 1, $options);
            }
        }

        if ($level === 0) {
            $html .= '</tbody></table>';
        }

        return $html;
    }
}
