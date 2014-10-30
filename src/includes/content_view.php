<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright Copyright (C) 2010 Abricos All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

$brick = Brick::$builder->brick;

$param = $brick->param;
$manager = Abricos::GetModule('news')->GetManager();

$newsid = intval(Abricos::$adress->dir[1]);

$row = $manager->News($newsid, true);
if (empty($row)) {
    $brick->content = $brick->param->var['notfound'];
    return;
}

$var = & $brick->param->var;

$var['title'] = Brick::ReplaceVar($var['title'], "val", $row['tl']);
$var['date'] = Brick::ReplaceVar($var['date'], "val", $row['dp'] > 0 ? rusDateTime(date($row['dp'])) : $brick->param->var['notpub']);
$var['intro'] = Brick::ReplaceVar($var['intro'], "val", $row['intro']);
$var['body'] = Brick::ReplaceVar($var['body'], "val", $row['body']);

$var['source'] = '';
$var['image'] = '';

if (!empty($row['tl'])) {
    Brick::$builder->SetGlobalVar('meta_title', $row['tl']);
}

Brick::$contentId = $row['contentid'];

?>