<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'dbquery.php';

class NewsManager extends Ab_ModuleManager {

    /**
     * @var NewsManager
     */
    public static $instance;

    /**
     *
     * @var NewsModule
     */
    public $module = null;

    public function __construct(NewsModule $module){
        parent::__construct($module);
        NewsManager::$instance = $this;
    }

    /**
     * Роль администратора новостей: редактор всех новостей
     */
    public function IsAdminRole(){
        return $this->IsRoleEnable(NewsAction::ADMIN);
    }

    /**
     * Роль публикатора новостей: редактор только своих новостей
     */
    public function IsWriteRole(){
        if ($this->IsAdminRole()){
            return true;
        }
        return $this->IsRoleEnable(NewsAction::WRITE);
    }

    /**
     * Роль просмотра новостей: только просмотр опубликованных новостей
     */
    public function IsViewRole(){
        if ($this->IsWriteRole()){
            return true;
        }
        return $this->IsRoleEnable(NewsAction::VIEW);
    }

    public function IsNewsWriteAccess($newid){
        if (!$this->IsWriteRole()){
            return false;
        }
        if ($this->IsAdminRole()){
            return true;
        }

        $info = NewsQuery::NewsInfo($this->db, $newid);
        if (empty($info) || $info['uid'] != $this->userid){
            return false;
        }
        return true;
    }


    public function AJAX($d){
        if ($d->type == 'news'){
            switch ($d->do){
                case "remove":
                    return $this->NewsRemove($d->id);
                case "restore":
                    return $this->NewsRestore($d->id);
                case "rclear":
                    return $this->NewsRecycleClear();
                case "publish":
                    return $this->NewsPublish($d->id);
            }
        }
        return -1;
    }

    public function DSProcess($name, $rows){
        $p = $rows->p;
        $db = $this->db;

        switch ($name){
            case 'news':
                foreach ($rows as $r){
                    if ($r->f == 'u'){
                        $this->NewsUpdate($r->d);
                    }
                    if ($r->f == 'a'){
                        $this->NewsAppend($r->d);
                    }
                }
                break;
        }
    }

    public function DSGetData($name, $rows){
        $p = $rows->p;
        $db = $this->db;

        switch ($name){
            case 'newslist':
                return $this->NewsList($p->page, $p->limit);
            case 'newscount':
                return $this->NewsCount();
            case 'news':
                return $this->News($p->id);
            case 'online':
                return $this->NewsList(1, 3);
        }

        return null;
    }

    /* * * * * * * * * * * * Чтение новостей * * * * * * * * * * * */

    public function News($newsid, $retarray = false){
        if (!$this->IsViewRole()){
            return;
        }
        return NewsQuery::News($this->db, $newsid, $this->userid, $retarray);
    }

    public function NewsList($page = 1, $limit = 10){
        if (!$this->IsViewRole()){
            return;
        }
        return NewsQuery::NewsList($this->db, $this->userid, $page, $limit);
    }

    public function NewsCount($retvalue = false){
        if (!$this->IsViewRole()){
            return;
        }
        return NewsQuery::NewsCount($this->db, $this->userid, $retvalue);
    }

    /* * * * * * * * * * * * Управление новостями * * * * * * * * * * * */

    /**
     * Добавить новость
     *
     * @param Object $d
     */
    public function NewsAppend($d){
        if (!$this->IsWriteRole()){
            return;
        }
        NewsQuery::NewsAppend($this->db, $this->userid, $d);
    }

    /**
     * Обновить новость
     *
     * @param Object $d
     */
    public function NewsUpdate($d){
        if (!$this->IsNewsWriteAccess($d->id)){
            return;
        }
        NewsQuery::NewsUpdate($this->db, $d);
    }

    public function NewsRemove($id){
        if (!$this->IsNewsWriteAccess($id)){
            return;
        }
        NewsQuery::NewsRemove($this->db, $id);
    }

    public function NewsRestore($id){
        if (!$this->IsNewsWriteAccess($id)){
            return;
        }
        NewsQuery::NewsRestore($this->db, $id);
    }

    public function NewsRecycleClear(){
        if (!$this->IsWriteRole()){
            return;
        }
        NewsQuery::NewsRecycleClear($this->db, $this->userid);
    }

    public function NewsPublish($id){
        if (!$this->IsNewsWriteAccess($id)){
            return;
        }
        NewsQuery::NewsPublish($this->db, $id, $this->userid);
    }
}

?>