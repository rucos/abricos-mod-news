<?php
/**
 * @package Abricos
 * @subpackage News
 * @copyright 2008-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class NewsItem
 *
 * @property string $title
 * @property string $intro
 * @property string $body
 * @property string $sourceName
 * @property string $sourceURI
 * @property int $dateline Date Create
 * @property int $upddate Update Date
 * @property int $published Date Published
 */
class NewsItem extends AbricosModel {
    protected $_structModule = 'news';
    protected $_structName = 'NewsItem';
}

/**
 * Class NewsList
 * @method NewsItem Get($newsid)
 * @method NewsItem GetByIndex($index)
 */
class NewsList extends AbricosModelList {
    /**
     * @var int Number of Page
     */
    public $page = 1;

    public function ToJSON(){
        $ret = parent::ToJSON();
        $ret->page = $this->page;
        return $ret;
    }
}

/**
 * Class NewsConfig
 *
 * @property string $date_format Date Format
 * @property int $page_count Number of items per page
 * @property string $list_meta_title Meta Title in News List Page
 */
class NewsConfig extends AbricosModel {
    protected $_structModule = 'news';
    protected $_structName = 'Config';
}

?>