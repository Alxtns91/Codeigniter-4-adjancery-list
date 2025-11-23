<?php

if (!function_exists('build_menu')) {

    /**
     * Recursively build a <ul> menu from a tree
     *
     * @param array $tree The hierarchical tree
     * @param array $options ['class' => 'menu-class', 'ul_class' => 'sub-menu', 'li_class' => 'item']
     * @return string HTML
     */
    function build_menu(array $tree, array $options = []): string
    {
        $ulClass = $options['ul_class'] ?? '';
        $liClass = $options['li_class'] ?? '';
        $html = "<ul" . ($ulClass ? " class=\"$ulClass\"" : "") . ">";

        foreach ($tree as $node) {
            $html .= "<li" . ($liClass ? " class=\"$liClass\"" : "") . ">";
            $html .= $node['name'];

            if (!empty($node['children'])) {
                $html .= build_menu($node['children'], $options);
            }

            $html .= "</li>";
        }

        $html .= "</ul>";
        return $html;
    }
}

if (!function_exists('build_table')) {

    /**
     * Recursively build an HTML table with indentation for hierarchy
     *
     * @param array $tree The hierarchical tree
     * @param int $level Current depth level (used internally)
     * @param array $options ['table_class' => '', 'indent' => 20]
     * @return string HTML
     */
    function build_table(array $tree, int $level = 0, array $options = []): string
    {
        $tableClass = $options['table_class'] ?? '';
        $indent = $options['indent'] ?? 20;

        // Only wrap table on first call
        $html = '';
        if ($level === 0) {
            $html .= "<table" . ($tableClass ? " class=\"$tableClass\"" : "") . ">";
            $html .= "<thead><tr><th>Name</th></tr></thead><tbody>";
        }

        foreach ($tree as $node) {
            $padding = $level * $indent;
            $html .= "<tr><td style='padding-left: {$padding}px'>{$node['name']}</td></tr>";

            if (!empty($node['children'])) {
                $html .= build_table($node['children'], $level + 1, $options);
            }
        }

        if ($level === 0) {
            $html .= "</tbody></table>";
        }

        return $html;
    }
}
