<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage News
 * @copyright Copyright (C) 2010 Abricos. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

class NewsQuery {
	
	public static function NewsAppend(CMSDatabase $db, $userid, $d){
		$contentid = CoreQuery::CreateContent($db, $d->body, 'news');
		$sql = "
			INSERT INTO ".$db->prefix."ns_news (
				userid, dateline, dateedit, published, 
				contentid, title, intro, imageid, source_name, source_link
			) VALUES (
				".bkint($userid).",
				".TIMENOW.",
				".TIMENOW.",
				'".bkint($d->dp)."',
				'".bkint($contentid)."',
				'".bkstr($d->tl)."',
				'".bkstr($d->intro)."',
				'".bkstr($d->img)."',
				'".bkstr($d->srcnm)."',
				'".bkstr($d->srclnk)."'
			)
		";
		$db->query_write($sql);
	}
	
	public static function NewsUpdate(CMSDatabase $db, $d){
		
		$info = NewsQuery::NewsInfo($db, $d->id);
		CoreQuery::ContentUpdate($db, $info['ctid'], $d->body);
		$sql = "
			UPDATE ".$db->prefix."ns_news
			SET 
				dateedit=".TIMENOW.",
				published=".bkint($d->dp).",
				title='".bkstr($d->tl)."',
				intro='".bkstr($d->intro)."',
				imageid='".bkstr($d->img)."',
				source_name='".bkstr($d->srcnm)."',
				source_link='".bkstr($d->srclnk)."'
			WHERE newsid=".bkint($d->id)."
		";
		$db->query_write($sql);
	}
	
	public static function News(CMSDatabase $db, $newsid, $userid = 0, $retarray = false){
		$sql = "
			SELECT
				a.newsid as id,
				a.userid as uid,
				a.dateline as dl,
				a.dateedit as de,
				a.published as dp,
				a.deldate as dd,
				a.contentid as ctid,
				b.body as body,
				a.title as tl,
				a.intro,
				a.imageid as img,
				a.source_name as srcnm,
				a.source_link as srclnk
			FROM ".$db->prefix."ns_news a
			LEFT JOIN ".$db->prefix."content b ON a.contentid = b.contentid
			WHERE a.newsid = ".bkint($newsid)." AND 
				((a.deldate=0 AND a.published>0) OR a.userid=".bkint($userid).") 
			LIMIT 1
		";
		return $retarray ? $db->query_first($sql) : $db->query_read($sql);
	}
	
	public static function NewsInfo(CMSDatabase $db, $newsid){
		$sql = "
			SELECT 
				newsid as id,
				userid as uid,
				contentid as ctid,
				dateline as dl,
				dateedit as de,
				published as dp,
				newsid, userid, contentid, dateline, dateedit, published
			FROM ".$db->prefix."ns_news 
			WHERE newsid=".bkint($newsid)."
		";
		return $db->query_first($sql);
	}
	
	/**
	 * Список новостей
	 *
	 * @param CMSDatabase $db
	 * @param integer $limit
	 * @param integer $page
	 * @param boolean $full Если true, содержит удаленные, черновики 
	 * @return resource
	 */
	public static function NewsList(CMSDatabase $db, $userid = 0, $page=1, $limit=10){
		$from = $limit * (max($page, 1) - 1);
		$sql = "
			SELECT
				newsid as id,
				userid as uid,
				dateline as dl,
				dateedit as de,
				published as dp,
				deldate as dd,
				contentid as ctid,
				title as tl,
				imageid as img,
				source_name as srcnm,
				source_link as srclnk,
				intro
			FROM ".$db->prefix."ns_news
			WHERE (deldate=0 AND published>0) OR userid=".bkint($userid)." 
			ORDER BY dl DESC 
			LIMIT ".$from.",".bkint($limit)."
		";
		return $db->query_read($sql);
	}
	
	public static function NewsCount(CMSDatabase $db, $userid = 0, $retvalue = false){
		$sql = "
			SELECT count( newsid ) AS cnt
			FROM ".$db->prefix."ns_news
			WHERE (deldate=0 AND published>0) OR userid=".bkint($userid)." 
			LIMIT 1 
		";
		if ($retvalue){
			$row= $db->query_first($sql);
			return $row['cnt'];
		}else{
			return $db->query_read($sql);
		}
	}
	
	public static function NewsRemove(CMSDatabase $db, $newsid){
		$sql = "
			UPDATE ".$db->prefix."ns_news 
			SET deldate=".TIMENOW."
			WHERE newsid=".bkint($newsid)."
		";
		$db->query_write($sql);
	}
	
	public static function NewsRestore(CMSDatabase $db, $newsid){
		$sql = "
			UPDATE ".$db->prefix."ns_news 
			SET deldate=0
			WHERE newsid=".bkint($newsid)."
		";
		$db->query_write($sql);
	}
	
	public static function NewsRecycleClear(CMSDatabase $db, $userid){
		$sql = "
			DELETE FROM ".$db->prefix."ns_news
			WHERE deldate > 0 AND userid=".bkint($userid)."
		";
		$db->query_write($sql);
	}
	
	public static function NewsPublish(CMSDatabase $db, $newsid){
		$sql = "
			UPDATE ".$db->prefix."ns_news
			SET published='".TIMENOW."'
			WHERE newsid=".bkint($newsid)." 
		";
		$db->query_write($sql);
	}
	
}

?>