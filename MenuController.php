<?php

namespace App\Controllers;

use App\Libraries\AdjacencyList;

class MenuController extends BaseController
{
    public function index()
    {
        helper('tree'); // Load the helper

        $adjacency = new AdjacencyList();
        $tree = $adjacency->getTreeCached();

        // Generate menu
        $menuHtml = build_menu($tree, ['ul_class' => 'nav', 'li_class' => 'nav-item']);

        // Generate table
        $tableHtml = build_table($tree, 0, ['table_class' => 'table table-bordered', 'indent' => 20]);

        echo "<h2>Menu:</h2>";
        echo $menuHtml;

        echo "<h2>Table:</h2>";
        echo $tableHtml;
    }
}
