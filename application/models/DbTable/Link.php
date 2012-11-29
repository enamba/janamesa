<?php
/**
 * Link Db Table
 * @author vpriem
 */
class Yourdelivery_Model_DbTable_Link extends Zend_Db_Table_Abstract{

    protected $_defaultSource = self::DEFAULT_DB;
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Link';

    /**
     * Table name
     */
    protected $_name = 'links';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * Get domain list
     * @author vpriem
     * @since 03.12.2010
     * @return array
     */
    public static function getDomains(){

        $domains = array(
            "www.lieferando.de",
            "www.lieferando.at",
            "www.lieferando.ch",
            "www.taxiresto.fr",
            "www.elpedido.es",
            "www.appetitos.it",
            "www.smakuje.pl",
            "www.janamesa.com.br",
            "www.eat-star.de",
            "pyszne.pl"
            // RIP "www.yourdelivery.de",
        );
        if (!IS_PRODUCTION) {
            $domains[] = "www.yourdelivery.local";
        }
        return $domains;

    }

     /**
     * Find row
     * @author vpriem
     * @param int $id
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findRow ($id) {

        if ($id !== null) {
            $rows = $this->find($id);
            if ($rows->count()) {
                return $rows->current();
            }
        }
        return false;

    }

     /**
     * Find row by url
     * @author vpriem
     * @since 31.08.2010
     * @param string $domain
     * @param string $url
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findByUrl ($domain, $url) {

        $url = trim($url, "/");
        return $this->fetchRow(
            $this->select()
                ->where("`domain` = ?", $domain)
                ->where("`url` = ?", $url)
        );

    }

    /**
     * Get all links
     * @author vpriem
     * @since 30.08.2010
     * @return array
     */
    public function getAll(){

        return $this->fetchAll(
            $this
                ->select()
                ->order("url")
        );

    }

    /**
     * Start crawler
     * @author vpriem
     * @since 01.09.2010
     * @return void
     */
    public function startCrawler(){

        $this->getAdapter()->query(
            "DELETE FROM `links_to`"
        );
        $this->getAdapter()->query(
            "DELETE FROM `links_from`"
        );
        $this->getAdapter()->query(
            "UPDATE `links`
            SET `linksTo` = 0,
                `linksFrom` = 0"
        );

    }

    /**
     * Stop crawler
     * @author vpriem
     * @since 02.09.2010
     * @return void
     */
    public function stopCrawler(){
    }

    /**
     * Start auto linker
     * @author vpriem
     * @since 01.09.2010
     * @return void
     */
    public function startAutoLinker ($reset = false) {

        if ($reset) {
            $this->getAdapter()->query(
                "DELETE FROM `links_navigation` WHERE `manual` = 0"
            );
            $this->getAdapter()->query(
                "DELETE FROM `links_list` WHERE `manual` = 0"
            );
            $this->getAdapter()->query(
                "DELETE FROM `links_related` WHERE `manual` = 0"
            );
            $this->getAdapter()->query(
                "DELETE FROM `links_restaurant` WHERE `manual` = 0"
            );
        }

    }

    /**
     * Get links with deleted restaurant
     * @author vpriem
     * @since 09.06.2011
     * @return array
     */
    public function getWithWrongRestaurant(){

        return $this->getAdapter()->fetchAll(
            "SELECT lr.linkId, lr.restaurantId
            FROM `links_restaurant` lr
            INNER JOIN `restaurants` r ON lr.restaurantId = r.id
                AND (r.deleted = 1 OR r.isOnline = 0)
            ORDER BY lr.linkId"
        );

    }

}

/**
 * Link Db Table Row
 * @author vpriem
 */
class Yourdelivery_Model_DbTableRow_Link extends Zend_Db_Table_Row_Abstract{

    /**
     * @var Yourdelivery_Model_DbTable_Links_Category
     */
    private $_category = null;

    /**
     * Pre-update
     * @author vpriem
     * @since 29.11.2010
     */
    protected function _update(){

        parent::_update();
        
        $this->updated = date(DATETIME_DB);
    }
    
    /**
     * Get absolute url
     * @author vpriem
     * @since 03.12.2010
     * @return string
     */
    public function getAbsoluteUrl(){

        return $this->domain . "/" . $this->url;

    }

    /**
     * Get sitemap
     * @author vpriem
     * @since 03.12.2010
     * @return string
     */
    public function getSitemap(){

        return "sitemap-" . $this->domain . ".xml";

    }

    /**
     * Publish link
     * @author vpriem
     * @return boolean
     */
    public function publish(){

        $config = Zend_Registry::get('configuration');
        
        // cause we don't have the ua code for other domain
        if (IS_PRODUCTION && str_replace("www.", "", $this->domain) != $config->domain->base) {
            return false;
        }
        
        // compile
        $smarty = new Default_View_Smarty($config);
        $smarty->assign('APPLICATION_ENV', APPLICATION_ENV);
        $smarty->assign('link', $this);
        $smarty->assign('config', $config);
        $smarty->assign('domain_base', str_replace("www.", "", $this->domain));
        $smarty->assign('googleAccounts', $config->google->ua->toArray());
        $html = $smarty->fetch("administration/seo/links/_templates/" . $this->domain . ".htm");

        // write
        $storage = new Default_File_Storage();
        $storage->setSubFolder('public/' . dirname($this->getAbsoluteUrl() . ".html"));
        $storage->store(basename($this->getAbsoluteUrl() . ".html"), $html);

        //purge
        $varnish = new Yourdelivery_Api_Varnish_Purger();
        $varnish->addUrl($this->getAbsoluteUrl());
        $varnish->executePurge();
        
        return true;
    }

    /**
     * Erase link
     * @author vpriem
     * @since 31.08.2010
     * @return boolean
     */
    public function erase(){

        // storage
        $storage = new Default_File_Storage();
        $storage->setSubFolder('public');

        // delete
        if ($storage->exists($this->getAbsoluteUrl() . ".html")) {
            $storage->delete($this->getAbsoluteUrl() . ".html");

            // sitemap
            $sitemap = new Default_Writer_Sitemap(APPLICATION_PATH . "/../storage/public/" . $this->getSitemap());
            $sitemap->remove("http://" . $this->getAbsoluteUrl());
            $sitemap->save();

            return true;
        }
        return false;

    }

    /**
     * Get all links excepted this one
     * @author vpriem
     * @since 19.08.2010
     * @return array
     */
    public function getLinks(){

        $db = $this->_table->getAdapter();
        return $db->fetchAll(
            "SELECT `id`, `domain`, `url`, `title`
            FROM `links`
            ORDER BY `url`"
        );

    }

    /**
     * Get related links
     * @author vpriem
     * @since 19.08.2010
     * @return array
     */
    public function getRelatedLinks(){

        if ($this->id === null) {
            return array();
        }

        $db = $this->_table->getAdapter();
        return $db->fetchAll(
            "SELECT lr.linkId, lr.relatedLinkId, lr.manual, l.domain, l.url, l.title, l.previewImage, l.previewText
             FROM `links` l
             INNER JOIN `links_related` lr ON l.id = lr.relatedLinkId
                AND lr.linkId = ?", $this->id
        );

    }

    /**
     * Add related link
     * @author vpriem
     * @since 19.08.2010
     * @param $id int
     * @param $manual int
     * @return boolean
     */
    public function addRelatedLink ($id, $manual = 1) {

        // seo shield
        if ($this->id == $id) {
            return false;
        }

        $db = $this->_table->getAdapter();
        try {
            $db->insert("links_related", array(
                'linkId' => $this->id,
                'relatedLinkId' => $id,
                'manual' => $manual,
            ));
            return true;
        }
        catch (Zend_Db_Statement_Exception $e) {
            return false;
        }

    }

    /**
     * Remove related link
     * @author vpriem
     * @since 19.08.2010
     * @param $id int
     * @return boolean
     */
    public function removeRelatedLink ($id) {

        $db = $this->_table->getAdapter();
        return (boolean) $db->delete(
            "links_related", "`linkId` = " . ((integer) $this->id) . " AND `relatedLinkId` = " . ((integer) $id)
        );

    }

    /**
     * Get navigation links
     * @author vpriem
     * @since 23.09.2010
     * @return array
     */
    public function getNavigationLinks(){

        if ($this->id === null) {
            return array();
        }

        $db = $this->_table->getAdapter();
        return $db->fetchAll(
            "SELECT ln.linkId, ln.navigationLinkId, ln.manual, l.domain, l.url, l.title
             FROM `links` l
             INNER JOIN `links_navigation` ln ON l.id = ln.navigationLinkId
                AND ln.linkId = ?", $this->id
        );

    }

    /**
     * Add navigation link
     * @author vpriem
     * @since 22.09.2010
     * @param $id int
     * @param $manual int
     * @return boolean
     */
    public function addNavigationLink ($id, $manual = 1) {

        // seo shield
        if ($this->id == $id) {
            return false;
        }

        $db = $this->_table->getAdapter();
        try {
            $db->insert("links_navigation", array(
                'linkId' => $this->id,
                'navigationLinkId' => $id,
                'manual' => $manual,
            ));
            return true;
        }
        catch (Zend_Db_Statement_Exception $e) {
            return false;
        }

    }

    /**
     * Remove navigation link
     * @author vpriem
     * @since 22.09.2010
     * @param $id int
     * @return boolean
     */
    public function removeNavigationLink ($id) {

        $db = $this->_table->getAdapter();
        return (boolean) $db->delete(
            "links_navigation", "`linkId` = " . ((integer) $this->id) . " AND `navigationLinkId` = " . ((integer) $id)
        );

    }

    /**
     * Get list links
     * @author vpriem
     * @since 23.09.2010
     * @return array
     */
    public function getListLinks(){

        if ($this->id === null) {
            return array();
        }

        $db = $this->_table->getAdapter();
        return $db->fetchAll(
            "SELECT ll.linkId, ll.listLinkId, ll.manual, l.domain, l.url, l.title
             FROM `links` l
             INNER JOIN `links_list` ll ON l.id = ll.listLinkId
                AND ll.linkId = ?", $this->id
        );

    }

    /**
     * Add list link
     * @author vpriem
     * @since 22.09.2010
     * @param $id int
     * @param $manual int
     * @return boolean
     */
    public function addListLink ($id, $manual = 1) {

        // seo shield
        if ($this->id == $id) {
            return false;
        }

        $db = $this->_table->getAdapter();
        try {
            $db->insert("links_list", array(
                'linkId' => $this->id,
                'listLinkId' => $id,
                'manual' => $manual,
            ));
            return true;
        }
        catch (Zend_Db_Statement_Exception $e) {
            return false;
        }

    }

    /**
     * Remove list link
     * @author vpriem
     * @since 22.09.2010
     * @param $id int
     * @return boolean
     */
    public function removeListLink ($id) {

        $db = $this->_table->getAdapter();
        return (boolean) $db->delete(
            "links_list", "`linkId` = " . ((integer) $this->id) . " AND `listLinkId` = " . ((integer) $id)
        );

    }

    /**
     * Get restaurants
     * @author vpriem
     * @since 02.10.2010
     * @return array
     */
    public function getDirectLinks(){

        $db = $this->_table->getAdapter();
        return $db->fetchAll(
            "SELECT r.id, r.name, r.street, r.hausnr, r.plz, c.city
             FROM `restaurants` r
             LEFT JOIN `city` c ON r.cityId = c.id
             WHERE r.restUrl != ''
                OR r.restUrl IS NOT NULL
             ORDER BY r.name"
        );

    }

    /**
     * Get restaurants
     * @author vpriem
     * @since 06.12.2010
     * @return array
     */
    public function getRestaurantsFromUrl(){

        $db = $this->_table->getAdapter();
        return $db->fetchCol(
            "SELECT r.id
             FROM `restaurants` r
             INNER JOIN `city` c ON r.cityId = c.id
             WHERE r.deleted = 0 
                AND r.isOnline = 1
                AND r.restUrl != ''
                AND r.restUrl IS NOT NULL
                AND ? LIKE CONCAT('%', c.city, '%')
             ORDER BY RAND()", $this->url
        );

    }

    /**
     * Get restaurants
     * @author vpriem
     * @since 02.10.2010
     * @return array
     */
    public function getRestaurants($asObject = false) {

        if ($this->id === null) {
            return array();
        }

        $db = $this->_table->getAdapter();

        if (!$asObject) {
            return $db->fetchAll(
                "SELECT lr.restaurantId, lr.manual, r.name, r.street, r.hausnr, r.plz, c.city
                 FROM `restaurants` r
                 INNER JOIN `links_restaurant` lr ON r.id = lr.restaurantId
                    AND lr.linkId = ?
                 LEFT JOIN `city` c ON r.cityId = c.id", $this->id
            );
        }

        $results = $db->fetchAll(
            "SELECT lr.restaurantId
             FROM `links_restaurant` lr
             WHERE lr.linkId = ?", $this->id
        );

        $restaurants = new SplObjectStorage();
        foreach ($results as $result) {
            try {
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($result['restaurantId']);
                
                if ($restaurant->isCatering()) {
                    $restaurant = new Yourdelivery_Model_Servicetype_Cater($result['restaurantId']);

                } elseif ($restaurant->isGreat() || $restaurant->isFruit()) {
                    $restaurant = new Yourdelivery_Model_Servicetype_Great($result['restaurantId']);

                }
                
                $restaurants->attach($restaurant);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        
        return $restaurants;
    }

    /**
     * Add restaurant
     * @author vpriem
     * @since 03.10.2010
     * @param $id int
     * @return boolean
     */
    public function addRestaurant ($id, $manual = 1) {

        $db = $this->_table->getAdapter();
        try {
            $db->insert("links_restaurant", array(
                'linkId' => $this->id,
                'manual' => $manual,
                'restaurantId' => $id,
            ));
            return true;
        }
        catch (Zend_Db_Statement_Exception $e) {
            return false;
        }

    }

    /**
     * Remove restaurant
     * @author vpriem
     * @since 03.10.2010
     * @param $id int
     * @return boolean
     */
    public function removeRestaurant ($id) {

        $db = $this->_table->getAdapter();
        return (boolean) $db->delete(
            "links_restaurant", "`linkId` = " . ((integer) $this->id) . " AND `restaurantId` = " . ((integer) $id)
        );

    }

    /**
     * Add link to
     * @author vpriem
     * @since 01.09.2010
     * @param $url string
     * @return boolean
     */
    public function addLinkTo ($url) {

        $db = $this->_table->getAdapter();
        $res = (boolean) $db->insert("links_to", array(
            'linkId' => $this->id,
            'url' => $url,
        ));

        if ($res) {
            $this->linksTo++;
            $this->save();
        }
        return $res;

    }

    /**
     * Add link from
     * @author vpriem
     * @since 01.09.2010
     * @param $url string
     * @return boolean
     */
    public function addLinkFrom ($url) {

        $this->linksFrom++;
        $this->save();

        $db = $this->_table->getAdapter();
        try {
            $res = (boolean) $db->insert("links_from", array(
                'linkId' => $this->id,
                'url' => $url,
            ));
            if ($res) {
                $this->linksFrom++;
                $this->save();
            }
            return $res;
        }
        catch (Zend_Db_Statement_Exception $e) {
            return false;
        }

    }

    /**
     * Get all links to
     * @author vpriem
     * @since 02.09.2010
     * @return array
     */
    public function getLinksTo(){

        $db = $this->_table->getAdapter();
        return $db->fetchAll(
            "SELECT `url`
            FROM `links_to`
            WHERE `linkId` = ?", $this->id
        );

    }

    /**
     * Get all links from
     * @author vpriem
     * @since 02.09.2010
     * @return array
     */
    public function getLinksFrom(){

        $db = $this->_table->getAdapter();
        return $db->fetchAll(
            "SELECT `url`
            FROM `links_from`
            WHERE `linkId` = ?", $this->id
        );

    }

    /**
     * Get form action
     * @deprecated
     * @author vpriem
     * @since 13.09.2010
     * @return string
     */
    public function getFormAction(){

        switch ($this->tab) {
            case "catering":
                return "/ordering_private_single_catering/start";

            case "great":
                return "/ordering_private_single_great/start";

            case "fruit":
                return "/ordering_private_single_fruit/start";

            default:
                return "/ordering_private_single_restaurant/start";
        }

    }

    /**
     * Get category
     * @author vpriem
     * @since 23.09.2010
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getCategory(){

        if ($this->_category !== null) {
            return $this->_category;
        }

        if (!$this->categoryId) {
            return $this->_category = false;
        }

        $categoryTable = new Yourdelivery_Model_DbTable_Links_Category();
        return $this->_category = $categoryTable->findRow($this->categoryId);

    }

    /**
     * Get the height of the background image in pixels
     * @author vpriem
     * @since 19.01.2011
     * @param int $startHeight start height
     * @return int
     */
    public function getBackgroundImageHeight ($startHeight = 60) {

        $height = 400; // default height
        if ($this->backgroundImage && file_exists($file = APPLICATION_PATH . "/../storage/landingpages/images/backgrounds/" . $this->backgroundImage)) {
            if (is_array($size = getimagesize($file))) {
                $height = $size[1];
            }
        }
        return $startHeight + $height;

    }

}