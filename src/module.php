<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */


/**
 * Class NewsModule
 */
class NewsModule extends Ab_Module {

    /**
     * @var NewsModule
     */
    public static $instance;

    private $_manager = null;

    public function NewsModule(){
        NewsModule::$instance = $this;

        $this->version = "0.2.8";
        $this->name = "news";
        $this->takelink = "news";

        $this->permission = new NewsPermission($this);
		
		$this->GetPhrases()->Set("page_count", 20);
    }

    /**
     * Получить имя кирпича контента
     *
     * @return string
     */
    public function GetContentName(){
        $adress = Abricos::$adress;

        if ($adress->level == 2 && substr($adress->dir[1], 0, 4) != 'page'){
            return "view";
        }
        return "index";
    }

    /**
     * Получить менеджер
     *
     * @return NewsManager
     */
    public function GetManager(){
        if (is_null($this->_manager)){
            require_once 'includes/manager.php';
            $this->_manager = new NewsManager($this);
        }
        return $this->_manager;
    }

    public function GetLink($newsid){
        return Ab_URI::fetch_host()."/".$this->takelink."/".$newsid."/";
    }

    public function RSS_GetItemList($inBosUI = false){
        $ret = array();

        $i18n = $this->GetI18n();
        $rows = $this->GetManager()->NewsList(1, 10);
        while (($row = Abricos::$db->fetch_array($rows))){
            $item = new RSSItem($row['tl'], $this->GetLink($row['id']), $row['intro'], $row['dp']);
            $item->modTitle = $i18n['title'];
            array_push($ret, $item);
        }
        return $ret;
    }

    public function RssMetaLink(){
        return Ab_URI::fetch_host()."/rss/news/";
    }

    public function Bos_IsMenu(){
        return true;
    }

}


class NewsAction {
    const ADMIN = 50;
    const WRITE = 30;
    const VIEW = 10;
}

class NewsPermission extends Ab_UserPermission {

    public function __construct(NewsModule $module){
        $defRoles = array(
            new Ab_UserRole(NewsAction::VIEW, Ab_UserGroup::GUEST),
            new Ab_UserRole(NewsAction::VIEW, Ab_UserGroup::REGISTERED),
            new Ab_UserRole(NewsAction::VIEW, Ab_UserGroup::ADMIN),

            new Ab_UserRole(NewsAction::WRITE, Ab_UserGroup::ADMIN),
            new Ab_UserRole(NewsAction::ADMIN, Ab_UserGroup::ADMIN)
        );
        parent::__construct($module, $defRoles);
    }

    public function GetRoles(){
        return array(
            NewsAction::VIEW => $this->CheckAction(NewsAction::VIEW),
            NewsAction::WRITE => $this->CheckAction(NewsAction::WRITE),
            NewsAction::ADMIN => $this->CheckAction(NewsAction::ADMIN)
        );
    }
}

Abricos::ModuleRegister(new NewsModule());

?>