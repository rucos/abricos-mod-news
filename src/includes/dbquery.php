<?php

/**
 * @package Abricos
 * @subpackage News
 * @copyright Copyright (C) 2010 Abricos. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */
class NewsQuery {

    public static function NewsAppend(Ab_Database $db, $userid, $d) {
        $d->body = isset($d->body) ? $d->body : '';
        $d->img = isset($d->img) ? $d->img : '';
        $d->srcnm = isset($d->srcnm) ? $d->srcnm : '';
        $d->srclnk = isset($d->srclnk) ? $d->srclnk : '';

        $contentid = Ab_CoreQuery::CreateContent($db, $d->body, 'news');
        $sql = "
			INSERT INTO ".$db->prefix."ns_news (
				userid, dateline, dateedit, published, 
				contentid, title, intro, imageid, source_name, source_link,
				language
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
				'".bkstr($d->srclnk)."',
				'".bkstr(Abricos::$LNG)."'
			)
		";
        $db->query_write($sql);
    }

    public static function NewsUpdate(Ab_Database $db, $d) {

        $info = NewsQuery::NewsInfo($db, $d->id);
        Ab_CoreQuery::ContentUpdate($db, $info['ctid'], $d->body);
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

    public static function News(Ab_Database $db, $newsid, $userid = 0, $retarray = false) {
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

    public static function NewsInfo(Ab_Database $db, $newsid) {
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
     * @param Ab_Database $db
     * @param integer $limit
     * @param integer $page
     * @param boolean $full Если true, содержит удаленные, черновики
     * @return resource
     */
    public static function NewsList(Ab_Database $db, $userid = 0, $page = 1, $limit = 10) {
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
			WHERE ((deldate=0 AND published>0) OR userid=".bkint($userid).") AND language='".bkstr(Abricos::$LNG)."' 
			ORDER BY dl DESC 
			LIMIT ".$from.",".bkint($limit)."
		";
        return $db->query_read($sql);
    }

    public static function NewsCount(Ab_Database $db, $userid = 0, $retvalue = false) {
        $sql = "
			SELECT count( newsid ) AS cnt
			FROM ".$db->prefix."ns_news
			WHERE ((deldate=0 AND published>0) OR userid=".bkint($userid).") AND language='".bkstr(Abricos::$LNG)."' 
			LIMIT 1 
		";
        if ($retvalue) {
            $row = $db->query_first($sql);
            return $row['cnt'];
        } else {
            return $db->query_read($sql);
        }
    }

    public static function NewsRemove(Ab_Database $db, $newsid) {
        $sql = "
			UPDATE ".$db->prefix."ns_news 
			SET deldate=".TIMENOW."
			WHERE newsid=".bkint($newsid)."
		";
        $db->query_write($sql);
    }

    public static function NewsRestore(Ab_Database $db, $newsid) {
        $sql = "
			UPDATE ".$db->prefix."ns_news 
			SET deldate=0
			WHERE newsid=".bkint($newsid)."
		";
        $db->query_write($sql);
    }

    public static function NewsRecycleClear(Ab_Database $db, $userid) {
        $sql = "
			DELETE FROM ".$db->prefix."ns_news
			WHERE deldate > 0 AND userid=".bkint($userid)."
		";
        $db->query_write($sql);
    }

    public static function NewsPublish(Ab_Database $db, $newsid) {
        $sql = "
			UPDATE ".$db->prefix."ns_news
			SET published='".TIMENOW."'
			WHERE newsid=".bkint($newsid)." 
		";
        $db->query_write($sql);
    }

}

?>