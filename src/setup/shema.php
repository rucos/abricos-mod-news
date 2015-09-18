<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isUpdate('0.2.2')){
    Abricos::GetModule('news')->permission->Install();
}

if ($updateManager->isUpdate('0.2.6') && !$updateManager->isInstall()){
    $db->query_write("
		ALTER TABLE ".$pfx."ns_news
		ADD language CHAR(2) NOT NULL DEFAULT '' COMMENT 'Язык'
	");
    $db->query_write("UPDATE ".$pfx."ns_news SET language='ru'");
}

if ($updateManager->isUpdate('0.2.8')){
    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."news (
		  newsid int(10) unsigned NOT NULL auto_increment,
		  userid int(10) unsigned NOT NULL,

		  title varchar(200) NOT NULL,
		  intro text NOT NULL,
		  body text NOT NULL,
		  imageid varchar(8) default NULL,

		  sourceName varchar(200) default NULL,
		  sourceURI varchar(200) default NULL,
		  language CHAR(2) NOT NULL DEFAULT '' COMMENT 'Язык',

		  published int(10) unsigned NOT NULL default 0,
		  dateline int(10) unsigned NOT NULL default 0,
		  upddate int(10) unsigned NOT NULL default 0,
		  deldate int(10) unsigned NOT NULL default 0,

		  PRIMARY KEY (newsid),
		  KEY news (published,deldate)
		)".$charset
    );
}

if ($updateManager->isUpdate('0.2.8') && !$updateManager->isInstall()){
    /* Old table
        $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."ns_news (
		  newsid int(10) unsigned NOT NULL auto_increment,
		  userid int(10) unsigned NOT NULL,
		  title varchar(200) NOT NULL,
		  intro text NOT NULL,
		  body text NOT NULL,
		  contentid int(10) unsigned NOT NULL,
		  imageid varchar(8) default NULL,
		  published int(10) unsigned NOT NULL default '0',
		  source_name varchar(200) default NULL,
		  source_link varchar(200) default NULL,
		  language CHAR(2) NOT NULL DEFAULT '' COMMENT 'Язык',
		  dateline int(10) unsigned NOT NULL default '0',
		  dateedit int(10) unsigned NOT NULL default '0',
		  deldate int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (newsid)
		)".$charset
    );

    /**/

    $db->query_write("DROP TABLE IF EXISTS ".$pfx."ns_cat");
    $db->query_write("ALTER TABLE ".$pfx."ns_news ADD body TEXT NOT NULL");

    $db->query_write("
		UPDATE ".$pfx."ns_news n
		INNER JOIN ".$pfx."content c ON n.contentid=c.contentid
		SET n.body=c.body
	");
    $db->query_write("DELETE FROM ".$pfx."content WHERE modman='news'");

    $db->query_write("
		INSERT INTO ".$pfx."news
		    (newsid,userid,title,intro,body,imageid,sourceName,sourceURI,language,published,dateline,upddate,deldate)
		SELECT
            newsid,userid,title,intro,body,imageid,source_name,source_link,language,published,dateline,dateedit,deldate
		FROM ".$pfx."ns_news
	");

    $db->query_write("DROP TABLE IF EXISTS ".$pfx."ns_news");
}


?>