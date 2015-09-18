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


$adr = Abricos::$adress;
$page = 1;
if ($adr->level > 1){
    $tag = $adr->dir[1];
    $page = intval(substr($tag, 4, strlen($tag) - 4));
}

// кол-во новостей на странице
$limit = $config->page_count;
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
        "intro" => $news->intro
    ));
}


$v['lst'] = $lst;

$newsCount = $app->NewsCount()->count;

// подгрузка кирпича пагинатора с параметрами
Brick::$builder->LoadBrickS('sitemap', 'paginator', $brick, array(
    "p" => array(
        "total" => $newsCount,
        "page" => $page,
        "perpage" => $limit,
        "uri" => $baseUrl
    )
));

$title = $config->list_meta_title;
if (empty($title)){
    $i18n = $mod->I18n();
    $title = $i18n->Translate('content.index.1');
}

// Вывод заголовка страницы
if (!empty($title)){
    Brick::$builder->SetGlobalVar('meta_title', $title);
}


?>