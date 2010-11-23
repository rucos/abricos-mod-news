<?php
/**
 * Вывод списка новостей
 * 
 * @version $Id$
 * @package Abricos
 * @subpackage News
 * @copyright Copyright (C) 2008 Abricos All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

$brick = Brick::$builder->brick;

$tag = CMSRegistry::$instance->adress->dir[1];
$page = intval(substr($tag, 4, strlen($tag)-4));

$mod = CMSRegistry::$instance->modules->GetModule('news');
$manager = $mod->GetManager();

// кол-во новостей на странице
$limit = Brick::$builder->phrase->Get('news', 'page_count', 10);
$dateFormat = Brick::$builder->phrase->Get('news', 'date_format', "Y-m-d");

$baseUrl = "/".$mod->takelink."/";

$lst = "";
$rows = $manager->NewsList($page, $limit);

while (($row = Brick::$db->fetch_array($rows))){
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
Brick::$builder->LoadBrickS('sitemap', 'paginator', $brick, array("p" => array(
	"total" => $newsCount,
	"page" => $page,
	"perpage" => $limit,
	"uri" => $baseUrl
)));


?>