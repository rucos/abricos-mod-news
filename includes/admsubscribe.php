<?php
/**
 * @version $Id: admsubscribe.php 3 2009-06-09 13:41:52Z roosit $
 * @package CMSBrick
 * @copyright Copyright (C) 2008 CMSBrick. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

if (!Brick::$session->IsAdminMode()){ return; }

$brick = Brick::$builder->brick; 

$userid = Brick::$session->userinfo['userid'];

$p_newsid = Brick::$input->clean_gpc('g', 'newsid', TYPE_INT);
$data = CMSQNews::News(Brick::$db, $p_newsid, true);

if (empty($data)){
	$currBrick->content = ""; 
	return; 
}

$row = array();
$row['module'] = 'news';
$row['subject'] = $data['tl'];
$row['body'] = $data['intro'].$data['body'];

$brick->param->var['lst'] = json_encode_ext($row); 

?>