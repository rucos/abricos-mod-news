<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class NewsManager
 *
 * @property NewsManager $manager
 */
class News extends AbricosApplication {

    protected function GetClasses(){
        return array(
            'Config' => 'NewsConfig',
            'NewsItem' => 'NewsItem',
            'NewsList' => 'NewsList'
        );
    }

    protected function GetStructures(){
        return 'Config,NewsItem,NewsList';
    }

    public function ResponseToJSON($d){
        switch ($d->do){
            case "newsList":
                return $this->NewsListToJSON($d->page);
            case "newsCount":
                return $this->NewsCountToJSON($d->page);
            case "newsItem":
                return $this->NewsItemToJSON($d->newsid);
            case "newsSave":
                return $this->NewsSaveToJSON($d->news);
            case "newsRemove":
                return $this->NewsRemoveToJSON($d->newsid);
            case "newsRestore":
                return $this->NewsRestoreToJSON($d->newsid);
            case "newsRecucleClear":
                return $this->NewsRecycleClearToJSON();
            case "newsPublish":
                return $this->NewsPublishToJSON($d->newsid);

            case "config":
                return $this->ConfigToJSON();
            case "configSave":
                return $this->ConfigSaveToJSON($d->config);

        }
        return null;
    }

    protected $_cache = array();

    public function CacheClear(){
        $this->_cache = array();
    }

    public function NewsListToJSON($page){
        $res = $this->NewsList($page);
        return $this->ResultToJSON('newsList', $res);
    }

    /**
     * @return NewsList
     */
    public function NewsList($page, $limit = 20){
        $key = $page."_".$limit;
        if (!isset($this->_cache['NewsList'])){
            $this->_cache['NewsList'] = array();
        }
        if (isset($this->_cache['NewsList'][$key])){
            return $this->_cache['NewsList'][$key];
        }

        if (!$this->manager->IsViewRole()){
            return 403;
        }

        $list = $this->models->InstanceClass('NewsList');
        $rows = NewsQuery::NewsList($this->db, $page, $limit);
        while (($d = $this->db->fetch_array($rows))){
            $list->Add($this->models->InstanceClass('NewsItem', $d));
        }
        return $this->_cache['NewsList'][$key] = $list;
    }

    public function NewsCountToJSON(){
        $res = $this->NewsCount();
        return $this->ResultToJSON('newsCount', $res);
    }

    public function NewsCount(){
        if (!$this->manager->IsViewRole()){
            return 403;
        }

        $ret = new stdClass();
        $ret->count = NewsQuery::NewsCount($this->db);
        return $ret;
    }

    public function NewsItemToJSON($newsid){
        $res = $this->NewsItem($newsid);
        return $this->ResultToJSON('news', $res);
    }

    /**
     * @param $newsid
     * @return NewsItem
     */
    public function NewsItem($newsid){
        if (!isset($this->_cache['NewsItem'])){
            $this->_cache['NewsItem'] = array();
        }
        if (isset($this->_cache['NewsItem'][$newsid])){
            return $this->_cache['NewsItem'][$newsid];
        }
        if (!$this->manager->IsViewRole()){
            return 403;
        }

        $d = NewsQuery::NewsItem($this->db, $newsid);
        if (empty($d)){
            return 404;
        }

        /** @var NewsItem $news */
        $news = $this->models->InstanceClass('NewsItem', $d);
        return $this->_cache['NewsItem'][$newsid] = $news;
    }

    public function NewsRemoveToJSON($newsid){
        $res = $this->NewsRemove($newsid);
        return $this->ResultToJSON('newsRemove', $res);
    }

    public function NewsRemove($newsid){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }
        $news = $this->NewsItem($newsid);
        if (empty($news)){
            return 404;
        }
        NewsQuery::NewsRemove(Abricos::$db, $newsid);

        $ret = new stdClass();
        $ret->newsid = $newsid;
        return $ret;
    }


    public function ConfigToJSON(){
        $res = $this->Config();
        return $this->ResultToJSON('config', $res);
    }

    /**
     * @return NewsConfig
     */
    public function Config(){
        if (isset($this->_cache['Config'])){
            return $this->_cache['Config'];
        }

        if (!$this->manager->IsViewRole()){
            return 403;
        }

        $phrases = NewsModule::$instance->GetPhrases();

        $d = array();
        for ($i = 0; $i < $phrases->Count(); $i++){
            $ph = $phrases->GetByIndex($i);
            $d[$ph->id] = $ph->value;
        }
        if (!isset($d['date_format'])){
            $d['date_format'] = "Y-m-d";
        }

        if (!isset($d['page_count'])){
            $d['page_count'] = 20;
        }

        return $this->_cache['Config'] = $this->models->InstanceClass('Config', $d);
    }

    public function ConfigSaveToJSON($sd){
        $this->ConfigSave($sd);
        return $this->ConfigToJSON();
    }

    public function ConfigSave($sd){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }

        $phs = NewsModule::$instance->GetPhrases();
        $phs->Set("list_meta_title", $sd->list_meta_title);
        $phs->Set("date_format", $sd->date_format);
        $phs->Set("page_count", intval($sd->page_count));

        Abricos::$phrases->Save();
    }

}

?>