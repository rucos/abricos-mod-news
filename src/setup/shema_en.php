<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$updateManager = Ab_UpdateManager::$current;

if ($updateManager->isInstall()){
    $d = new stdClass();
    $d->title = "Birth site";
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
    $d->published = TIMENOW;

    $oldUID = Abricos::$user->id;
    Abricos::$user->id = 1;
    $updateManager->module->GetManager()->GetNews();
    NewsQuery::NewsAppend(Abricos::$db, $d);
    Abricos::$user->id = $oldUID;
}

?>