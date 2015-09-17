<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$p = $brick->param->param;

if ($brick->child[0]->viewcount == 0 && !$p['showempty']){
    $brick->content = "";
    return;
}

$modRSS = Abricos::GetModule('rss');

$rss = "";
if (!empty($modRSS)){
    $rss = $brick->param->var['rss'];
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    'rss' => $rss
));


?>