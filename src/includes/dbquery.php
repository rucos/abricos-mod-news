<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class NewsQuery
 */
class NewsQuery {

    public static function NewsAppend(Ab_Database $db, $d){
        $d->imageid = '';
        $d->body = isset($d->body) ? $d->body : '';
        $d->sourceName = isset($d->sourceName) ? $d->sourceName : '';
        $d->sourceURI = isset($d->sourceURI) ? $d->sourceURI : '';

        $sql = "
			INSERT INTO ".$db->prefix."news (
				userid, dateline, upddate, published, 
				title, intro, body, imageid, 
				sourceName, sourceURI,
				language
			) VALUES (
				".bkint(Abricos::$user->id).",
				".TIMENOW.",
				".TIMENOW.",
				'".bkint($d->published)."',
				'".bkstr($d->title)."',
				'".bkstr($d->intro)."',
				'".bkstr($d->body)."',
				'".bkstr($d->imageid)."',
				'".bkstr($d->sourceName)."',
				'".bkstr($d->sourceURI)."',
				'".bkstr(Abricos::$LNG)."'
			)
		";
        $db->query_write($sql);
        return $db->insert_id();
    }

    public static function NewsUpdate(Ab_Database $db, $newsid, $d){
        $d->imageid = isset($d->imageid) ? $d->imageid : '';

        $sql = "
			UPDATE ".$db->prefix."news
			SET 
				upddate=".TIMENOW.",
				published=".bkint($d->published).",
				title='".bkstr($d->title)."',
				intro='".bkstr($d->intro)."',
				body='".bkstr($d->body)."',
				imageid='".bkstr($d->imageid)."',
				sourceName='".bkstr($d->sourceName)."',
				sourceURI='".bkstr($d->sourceURI)."'
			WHERE newsid=".bkint($newsid)."
		";
        $db->query_write($sql);
    }

    public static function NewsItem(Ab_Database $db, $newsid){
        $sql = "
			SELECT
				a.newsid as id,
				a.*
			FROM ".$db->prefix."news a
			WHERE a.newsid = ".bkint($newsid)." AND
			    (((a.deldate=0 AND a.published>0) OR userid=".bkint(Abricos::$user->id)."))
			LIMIT 1
		";
        return $db->query_first($sql);
    }

    /**
     * Список новостей
     *
     * @param Ab_Database $db
     * @param int $page
     * @param int $limit
     * @return int
     */
    public static function NewsList(Ab_Database $db, $page = 1, $limit = 20){
        $from = $limit * (max($page, 1) - 1);
        $sql = "
			SELECT
				n.newsid as id,
				n.title,
				n.intro,
				n.imageid,
				n.sourceName,
				n.sourceURI,
				n.dateline,
				n.published
			FROM ".$db->prefix."news n
			WHERE (published>0 OR userid=".bkint(Abricos::$user->id).")
			        AND deldate=0
			        AND language='".bkstr(Abricos::$LNG)."'
			ORDER BY dateline DESC
			LIMIT ".$from.",".bkint($limit)."
		";
        return $db->query_read($sql);
    }

    public static function NewsCount(Ab_Database $db, $admin){
    	if(!$admin){
    		$lst = "published>0	AND deldate=0 AND language='".bkstr(Abricos::$LNG)."'";
    	} else {
    		$lst = "language='".bkstr(Abricos::$LNG)."'";
    	}
    	 
        $sql = "
			SELECT count( newsid ) AS cnt
			FROM ".$db->prefix."news
			WHERE ".$lst."
			LIMIT 1 
		";
        $row = $db->query_first($sql);
        return $row['cnt'];
    }

    public static function NewsRemove(Ab_Database $db, $newsid){
        $sql = "
			UPDATE ".$db->prefix."news 
			SET deldate=".TIMENOW."
			WHERE newsid=".bkint($newsid)."
		";
        $db->query_write($sql);
    }

    public static function NewsPublish(Ab_Database $db, $newsid){
        $sql = "
			UPDATE ".$db->prefix."news
			SET published='".TIMENOW."'
			WHERE newsid=".bkint($newsid)." 
		";
        $db->query_write($sql);
    }

}

?>