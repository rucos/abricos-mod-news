<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

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

    private $_news = null;

    /**
     * @return News
     */
    public function GetNews() {
        if (empty($this->_news)) {
            require_once 'classes/models.php';
            require_once 'dbquery.php';
            require_once 'classes/news.php';
            $this->_news = new News($this);
        }
        return $this->_news;
    }

    public function AJAX($d) {
        return $this->GetNews()->AJAX($d);
    }

    public function Bos_MenuData(){
        if (!$this->IsAdminRole()){
            return null;
        }
        $i18n = $this->module->I18n();
        return array(
            array(
                "name" => "news",
                "title" => $i18n->Translate('title'),
                "icon" => "/modules/news/images/cp_icon.gif",
                "url" => "news/wspace/ws",
                "parent" => "controlPanel"
            )
        );
    }
}

?>