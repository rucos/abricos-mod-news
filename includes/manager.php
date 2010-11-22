<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage News
 * @copyright Copyright (C) 2008 Abricos. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

require_once 'dbquery.php';

class NewsManager extends ModuleManager {
	
	/**
	 * 
	 * @var NewsModule
	 */
	public $module = null;
	
	/**
	 * User
	 * @var User
	 */
	public $user = null;
	
	public $userid = 0;

	public function NewsManager(NewsModule $module){
		parent::ModuleManager($module);
		
		$this->user = CMSRegistry::$instance->user;
		$this->userid = $this->user->info['userid'];
	}
	
	public function IsAdminRole(){
		return $this->module->permission->CheckAction(NewsAction::ADMIN) > 0;
	}
	
	public function IsWriteRole(){
		return $this->module->permission->CheckAction(NewsAction::WRITE) > 0;
	}
	
	public function IsViewRole(){
		return $this->module->permission->CheckAction(NewsAction::VIEW) > 0;
	}
		
	public function AJAX($d){
		if ($d->type == 'news'){
			switch($d->do){
				case "remove": return $this->NewsRemove($d->id);
				case "restore": return $this->NewsRestore($d->id);
				case "rclear": return $this->NewsRecycleClear();
				case "publish": return $this->NewsPublish($d->id); 
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
					if ($r->f == 'u'){ $this->NewsUpdate($r->d); }
					if ($r->f == 'a'){ $this->NewsAppend($r->d); }
				}
				break;
		}
	}
	
	public function DSGetData($name, $rows){
		$p = $rows->p;
		$db = $this->db;
		
		switch ($name){
			case 'newslist': return $this->NewsList($p->page, $p->limit);
			case 'newscount': return $this->NewsCount();
			case 'news': return $this->News($p->id);
			case 'online': return $this->NewsList(1, 3);
		}
		
		return null;
	}
	
	/**
	 * Добавить новость
	 * @param Object $d
	 */
	public function NewsAppend($d){
		if (!$this->IsWriteRole()){ return; }
		NewsQuery::NewsAppend($this->db, $this->userid, $d);
	}
	
	/**
	 * Обновить новость
	 * @param Object $d
	 */
	public function NewsUpdate($d){
		if (!$this->IsWriteRole()){ return; }
		
		$info = NewsQuery::NewsInfo($this->db, $d->id);
		if (empty($info) || $info['uid'] != $this->userid) { return false; }

		NewsQuery::NewsUpdate($this->db, $this->userid, $d);
	}
	
	public function News($newsid, $retarray = false){
		if (!$this->IsViewRole()){ return; }
		return NewsQuery::News($this->db, $newsid, $this->userid, $retarray);
	}
	
	public function NewsList($page = 1, $limit = 10){
		if (!$this->IsViewRole()){ return; }
		return NewsQuery::NewsList($this->db, $this->userid, $page, $limit);
	}
	
	public function NewsCount($retvalue = false){
		if (!$this->IsViewRole()){ return; }
		return NewsQuery::NewsCount($this->db, $this->userid, $retvalue);		
	}
	
	public function NewsRemove($id){
		if (!$this->IsWriteRole()){ return; }
		NewsQuery::NewsRemove($this->db, $id, $this->userid);
	}
	
	public function NewsRestore($id){
		if (!$this->IsWriteRole()){ return; }
		NewsQuery::NewsRestore($this->db, $id, $this->userid);
	}
	
	public function NewsRecycleClear(){
		if (!$this->IsWriteRole()){ return; }
		NewsQuery::NewsRecycleClear($this->db, $this->userid);
	}
	
	public function NewsPublish($id){
		if (!$this->IsWriteRole()){ return; }
		NewsQuery::NewsPublish($this->db, $id, $this->userid);
	}
}

?>