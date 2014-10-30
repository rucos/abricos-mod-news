<?php
/**
 * Структура таблиц модуля
 *
 * @version $Id$
 * @package Abricos
 * @subpackage News
 * @copyright Copyright (C) 2008 Abricos All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isInstall()) {
    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."ns_cat (
		  `catid` int(10) unsigned NOT NULL auto_increment,
		  `parentcatid` int(10) unsigned NOT NULL,
		  `name` varchar(250) NOT NULL,
		  `phrase` varchar(250) NOT NULL,
		  PRIMARY KEY  (`catid`)
		)".$charset
    );

    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."ns_news (
		  `newsid` int(10) unsigned NOT NULL auto_increment,
		  `language` CHAR(2) NOT NULL DEFAULT '' COMMENT 'Язык',
		  `userid` int(10) unsigned NOT NULL,
		  `dateline` int(10) unsigned NOT NULL default '0',
		  `dateedit` int(10) unsigned NOT NULL default '0',
		  `deldate` int(10) unsigned NOT NULL default '0',
		  `contentid` int(10) unsigned NOT NULL,
		  `title` varchar(200) NOT NULL,
		  `intro` text NOT NULL,
		  `imageid` varchar(8) default NULL,
		  `published` int(10) unsigned NOT NULL default '0',
		  `source_name` varchar(200) default NULL,
		  `source_link` varchar(200) default NULL,
		  PRIMARY KEY  (`newsid`)
		)".$charset
    );

    if (Ab_UpdateManager::$isCoreInstall) {
        // Идет инсталляция платформа

        $d = new stdClass();

        if (Abricos::$LNG == 'ru') {
            $d->tl = "Рождение сайта";
            $d->intro = "
				<p>Уважаемые посетители!</p>
				<p>
					Мы рады сообщить Вам о запуске нашего сайта.
				</p>
				<p>
					Для работы сайта мы используем платформу
					<a href='http://abricos.org' title='Платформа Абрикос - система управления сайтом'>Абрикос</a>,
					потому что именно на этой платформе мы сможем реализовать для Вас
					практически безграничные возможности.
				</p>
			";
        } else {
            $d->tl = "Birth site";
            $d->intro = "
				<p>Dear visitors!</p>
				<p>
					We are pleased to announce the launch of our website.
				</p>
				<p>
					For site work, we use <a href='http://abricos.org' title='Abricos Platform - content managment system, WebOS'>Abricos Platrofm</a>,
					because it was on this platform, we can realize for you virtually limitless possibilities.
				</p>
			";
        }

        $d->dp = TIMENOW;
        require_once 'dbquery.php';
        NewsQuery::NewsAppend($db, 1, $d);
    }
}
if ($updateManager->isUpdate('0.2.2')) {

    Abricos::GetModule('news')->permission->Install();

}

if ($updateManager->isUpdate('0.2.6') && !$updateManager->isInstall()) {

    $db->query_write("
		ALTER TABLE ".$pfx."ns_news
		ADD `language` CHAR(2) NOT NULL DEFAULT '' COMMENT 'Язык'
	");
    $db->query_write("UPDATE ".$pfx."ns_news SET language='ru'");

}

?>