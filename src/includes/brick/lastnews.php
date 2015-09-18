<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;

/** @var NewsModule $mod */
$mod = Abricos::GetModule('news');
$app = $mod->GetManager()->GetNews();
$config = $app->Config();

$limit = $p['count'];
$hideintro = $p['hideintro'];
if (empty($hideintro) && !empty($brick->parent) && isset($brick->parent->param->param['hideintro'])){
    $hideintro = $brick->parent->param->param['hideintro'];
}

$dateFormat = $config->date_format;
$baseUrl = "/".$mod->takelink."/";

$lst = "";
$newsList = $app->NewsList(1, $limit);
$count = $newsList->Count();

for ($i = 0; $i < $count; $i++){
    $news = $newsList->GetByIndex($i);
    $lst .= Brick::ReplaceVarByData($v['row'], array(
        "date" => date($dateFormat, $news->published),
        "link" => $baseUrl.$news->id."/",
        "title" => $news->title,
        "intro" => !empty($hideintro) ? '' : $news->intro
    ));
}

$brick->viewcount = $count;
$brick->param->var['result'] = $lst;

?>