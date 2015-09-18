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

$newsid = intval(Abricos::$adress->dir[1]);

$newsItem = $app->NewsItem($newsid);

if (empty($newsItem)){
    $brick->content = $v['notfound'];
    return;
}

$v['title'] = Brick::ReplaceVar($v['title'], "val", $newsItem->title);
$v['date'] = Brick::ReplaceVar($v['date'], "val", $newsItem->published > 0 ? rusDateTime(date($newsItem->published)) : $v['notpub']);
$v['intro'] = Brick::ReplaceVar($v['intro'], "val", $newsItem->intro);
$v['body'] = Brick::ReplaceVar($v['body'], "val", $newsItem->body);

$v['source'] = '';
$v['image'] = '';

if (!empty($newsItem->title)){
    Brick::$builder->SetGlobalVar('meta_title', $newsItem->title);
}

?>