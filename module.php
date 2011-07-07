<?php 
/**
 * Модуль "Новости"
 * 
 * @version $Id$
 * @package Abricos 
 * @subpackage News
 * @copyright Copyright (C) 2008 Abricos All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

/**
 * Модуль "Новости" 
 * @package Abricos 
 * @subpackage News
 */
class NewsModule extends CMSModule {
	
	private $_manager = null;
	
	public function NewsModule(){
		$this->version = "0.2.3";
		$this->name = "news";
		$this->takelink = "news";
		
		$this->permission = new NewsPermission($this);
	}

	/**
	 * Получить имя кирпича контента
	 *
	 * @return string
	 */
	public function GetContentName(){
		$adress = $this->registry->adress;
		
		if($adress->level == 2 && substr($adress->dir[1], 0, 4) != 'page'){
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
		return $this->registry->adress->host."/".$this->takelink."/".$newsid."/";
	}
	
	public function RssWrite(CMSRssWriter2_0 $writer){
		require_once 'includes/dbquery.php';
		$rows = $this->GetManager()->NewsList(1, 10);
		while (($row = $this->registry->db->fetch_array($rows))){
			$item = new CMSRssWriter2_0Item($row['tl'], $this->GetLink($row['id']), $row['intro']);
			$item->pubDate = $row['dp'];
			$writer->WriteItem($item);
		}
	}
	
	public function RssMetaLink(){
		return $this->registry->adress->host."/rss/news/";
	}
}


class NewsAction {
	const VIEW			= 10;
	const WRITE			= 30;
	const ADMIN			= 50;
}

class NewsPermission extends CMSPermission {
	
	public function NewsPermission(NewsModule $module){
		$defRoles = array(
			new CMSRole(NewsAction::VIEW, 1, User::UG_GUEST),
			new CMSRole(NewsAction::VIEW, 1, User::UG_REGISTERED),
			new CMSRole(NewsAction::VIEW, 1, User::UG_ADMIN),
			
			new CMSRole(NewsAction::WRITE, 1, User::UG_ADMIN),
			new CMSRole(NewsAction::ADMIN, 1, User::UG_ADMIN)
		);
		parent::CMSPermission($module, $defRoles);
	}
	
	public function GetRoles(){
		return array(
			NewsAction::VIEW => $this->CheckAction(NewsAction::VIEW),
			NewsAction::WRITE => $this->CheckAction(NewsAction::WRITE), 
			NewsAction::ADMIN => $this->CheckAction(NewsAction::ADMIN) 
		);
	}
}

CMSRegistry::$instance->modules->GetModule('comment');
$modNews = new NewsModule();
CMSRegistry::$instance->modules->Register($modNews);

?>