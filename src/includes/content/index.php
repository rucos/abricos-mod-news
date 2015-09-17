<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;

$adr = Abricos::$adress;
$page = 1;
if ($adr->level > 1){
    $tag = $adr->dir[1];
    $page = intval(substr($tag, 4, strlen($tag) - 4));
}

$mod = NewsModule::$instance;
$manager = $mod->GetManager();

$phs = $mod->GetPhrases();

// кол-во новостей на странице
$limit = $phs->Get('page_count', 10)->value;
$dateFormat = $phs->Get('date_format', "Y-m-d")->value;

$baseUrl = "/".$mod->takelink."/";

$lst = "";
$rows = $manager->NewsList($page, $limit);

while (($row = Abricos::$db->fetch_array($rows))){
    $lst .= Brick::ReplaceVarByData($brick->param->var['row'], array(
        "date" => date($dateFormat, $row['dp']),
        "link" => $baseUrl.$row['id']."/",
        "title" => $row['tl'],
        "intro" => $row['intro']
    ));
}

$brick->param->var['lst'] = $lst;

$newsCount = $manager->NewsCount(true);

// подгрузка кирпича пагинатора с параметрами
Brick::$builder->LoadBrickS('sitemap', 'paginator', $brick, array(
    "p" => array(
        "total" => $newsCount,
        "page" => $page,
        "perpage" => $limit,
        "uri" => $baseUrl
    )
));

$i18n = $mod->GetI18n();

$title = $i18n['content']['index']['1'];
$title = $phs->Get('list_meta_title', $title);

// Вывод заголовка страницы
if (!empty($title)){
    Brick::$builder->SetGlobalVar('meta_title', $title);
}


?>