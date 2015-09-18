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
    $d->title = "Рождение сайта";
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
    $d->published = TIMENOW;

    $oldUID = Abricos::$user->id;
    Abricos::$user->id = 1;
    $updateManager->module->GetManager()->GetNews();
    NewsQuery::NewsAppend(Abricos::$db, $d);
    Abricos::$user->id = $oldUID;
}

?>