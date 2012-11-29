<?php
/**
 * Internal links controller
 *
 * @author vpriem
 */
class Administration_Seo_LinksController extends Default_Controller_AdministrationBase {

    protected function _isLocaleFrozen() {
        return true;
    }
    
    private $_blacklist;

    /**
     * Init
     * @author vpriem
     */
    public function init(){
        parent::init();

        $this->_blacklist = APPLICATION_PATH . '/../storage/blacklist_seo.txt';
        if (!file_exists($this->_blacklist)) {
            touch($this->_blacklist);
        }

        $storage = new Default_File_Storage();
        $storage->setSubFolder('landingpages/images/backgrounds');
        $storage->setSubFolder('../buttons');
        $storage->setSubFolder('../disturbers');
        $storage->setSubFolder('../previews');
    }

    /**
     * Show internal links
     * @author vpriem
     */
    public function indexAction() {

        // create gid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select(Yourdelivery_Model_Link::getGrid()));
        $grid->setPagination(20);
        $grid->updateColumn('id', array('decorator' => "#{{id}}"));
        $grid->updateColumn('id',              array('searchType' => '='));
        $grid->updateColumn('url', array('decorator' => '<a href="http://{{domain}}/{{url}}" target="_blank">/{{url}}</a>'));
        $grid->updateColumn('Reiter', array('title'=>__b('Reiter')));
        $grid->updateColumn('Title', array('title'=>__b('Title')));
        $grid->updateColumn('Ausgehende', array('title'=>__b('Ausgehende')));
        $grid->updateColumn('Eingehende', array('title'=>__b('Eingehende')));
        // add extra columns
        $col = new Bvb_Grid_Extra_Column();
        $col->position('right')
                ->name(__b('Optionen'))
                ->decorator(
                    '<a href="/administration_seo_links/edit/id/{{id}}">'.__b("Editieren").'</a>
                     <a href="/administration_seo_links/disable/id/{{id}}" class="yd-are-you-sure">'.__b("Deaktivieren").'</a>
                     <a href="/administration_seo_links/delete/id/{{id}}" class="yd-are-you-sure">'.__b("Löschen").'</a>'
                );
        $grid->addExtraColumns($col);

        // deploy grid to view
        $this->view->grid = $grid->deploy();

    }

    /**
     * Regeneratel all links
     * @author vpriem
     * @since 30.08.2010
     */
    public function regenerateAction(){

        set_time_limit(180);

        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();

        // regenerate
        $i = 0;
        $rows = $linkTable->getAll();
        foreach ($rows as $row) {
            $row->publish() ? $i++ : null;
        }

        // redirect
        $this->success(__b("%s von %s Verlinkung wurden neu generiert", $i, count($rows)));
        $this->_redirect('/administration_seo_links/');
    }

    /**
     * Secret action for developers
     * @author vpriem
     * @since 02.09.2010
     */
    public function degenerateAction(){

        if (APPLICATION_ENV == "production") {
            $this->_redirect('/administration_seo_links/');
        }

        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();

        // regenerate
        $i = 0;
        $rows = $linkTable->getAll();
        foreach ($rows as $row) {
            $row->erase() ? $i++ : null;
        }

        // redirect
        $this->success($i . __b(" Verlinkung wurden gelöscht"));
        $this->_redirect('/administration_seo_links/');

    }

    /**
     * Create internal link
     * @author vpriem
     */
    public function editAction(){

        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();

        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_seo_links/');
            }

            // create
            $link = $linkTable->findRow($post['id']);
            if (!$link) {
                $link = $linkTable->createRow();
            }

            // form
            $form = new Yourdelivery_Form_Administration_Seo_Links_Edit();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();
                if (is_array($values['payments'])) {
                    $values['payments'] = array_sum($values['payments']);
                }
                else{
                    $values['payments'] = 0;
                }

                // change url
                if ($link->id && $link->url != $values['url']) {
                    $link->erase();
                }

                $link->setFromArray($values);

                // upload
                if ($form->_image->isUploaded()) {
                    $link->backgroundImage = $form->_image->getFileName(null, false);
                }
                if ($form->_button->isUploaded()) {
                    $link->buttonImage = $form->_button->getFileName(null, false);
                }
                if ($form->_preview->isUploaded()) {
                    $link->previewImage = $form->_preview->getFileName(null, false);
                }

                // save
                try {
                    $link->save();

                    if (is_array($values['addNavigationLinks'])) {
                        foreach ($values['addNavigationLinks'] as $id) {
                            $link->addNavigationLink($id);
                        }
                    }
                    if (is_array($values['rmNavigationLinks'])) {
                        foreach ($values['rmNavigationLinks'] as $id) {
                            $link->removeNavigationLink($id);
                        }
                    }
                    
                    if (is_array($values['addListLinks'])) {
                        foreach ($values['addListLinks'] as $id) {
                            $link->addListLink($id);
                        }
                    }
                    if (is_array($values['rmListLinks'])) {
                        foreach ($values['rmListLinks'] as $id) {
                            $link->removeListLink($id);
                        }
                    }

                    if (is_array($values['addRelatedLinks'])) {
                        foreach ($values['addRelatedLinks'] as $id) {
                            $link->addRelatedLink($id);
                        }
                    }
                    if (is_array($values['rmRelatedLinks'])) {
                        foreach ($values['rmRelatedLinks'] as $id) {
                            $link->removeRelatedLink($id);
                        }
                    }

                    if (is_array($values['addRestaurants'])) {
                        foreach ($values['addRestaurants'] as $id) {
                            $link->addRestaurant($id);
                        }
                    }
                    if (is_array($values['rmRestaurants'])) {
                        foreach ($values['rmRestaurants'] as $id) {
                            $link->removeRestaurant($id);
                        }
                    }

                    $this->success(__b("Der Verlinkung wurde erfolgreich gespeichert"));
                    
                    // publish
                    if (!$link->publish()) {
                        $this->error(__b("Der Verlinkung konnte nicht erstellt werden"));
                    }
                }
                catch (Zend_Db_Statement_Exception $e) {
                    $this->error(__b("Der Verlinkung konnte nicht gespeichert werden"));
                }

                // redirect
                if (isset($post['save'])) {
                    $this->_redirect('/administration_seo_links/');
                }
                $this->_redirect('/administration_seo_links/edit/id/' . $link->id);

            // error
            }
            else {
                $link->setFromArray($post);
                $this->error($form->getMessages());
            }

        // get
        }
        else {
            $id  = $request->getParam('id');
            $link = $linkTable->findRow($id);
            if (!$link) {
                $link = $linkTable->createRow();
                $link->domain = HOSTNAME;
            }
        }

        // assign link
        $this->view->assign('link', $link);

        // assign domains
        $this->view->assign('domains', $linkTable::getDomains());

        // assign robots
        $this->view->assign('robots', array(
            'all'            => "index,follow",
            'index,nofollow' => "index,nofollow",
            'none'           => "noindex,nofollow",
            'noindex,follow' => "noindex,follow",
        ));

        // assign tabs
        $this->view->assign('tabs', array(
            'rest' => "Lieferservice",
            'cater'   => "Catering",
            'great'      => "Getränkemarkt",
        ));

        /**
         * @todo http://ticket/browse/YD-1026
         * we should use the find method of the storage folder
         */
        
        // assign images
        $images = @scandir(APPLICATION_PATH . '/../storage/landingpages/images/backgrounds');
        if ($images) {
            foreach ($images as $key => $image) {
                if (!in_array(pathinfo($image, PATHINFO_EXTENSION), array("jpg", "gif", "png"))) {
                    unset($images[$key]);
                }
            }
            $this->view->assign('images', $images);
        }

        // assign buttons images
        $buttons = @scandir(APPLICATION_PATH . '/../storage/landingpages/images/buttons');
        if ($buttons) {
            foreach ($buttons as $key => $button) {
                if (!in_array(pathinfo($button, PATHINFO_EXTENSION), array("jpg", "gif", "png"))) {
                    unset($buttons[$key]);
                }
            }
            $this->view->assign('buttons', $buttons);
        }

        // assign preview images
        $previews = @scandir(APPLICATION_PATH . '/../storage/landingpages/images/previews');
        if ($previews) {
            foreach ($previews as $key => $preview) {
                if (!in_array(pathinfo($preview, PATHINFO_EXTENSION), array("jpg", "gif", "png"))) {
                    unset($previews[$key]);
                }
            }
            $this->view->assign('previews', $previews);
        }

        // assign positions
        $this->view->assign('positions', array(
//            "top-left",    "top-center",    "top-right",
            "middle-left", "middle-center", "middle-right",
//            "bottom-left", "bottom-center", "bottom-right",
        ));

        // assign categories
        $categoryTable = new Yourdelivery_Model_DbTable_Links_Category();
        $this->view->assign('categories', $categoryTable->fetchAll());

    }

    /**
     * Remove internal link
     * @author vpriem
     */
    public function disableAction(){

        // get parameters
        $request = $this->getRequest();
        $id = $request->getParam('id');

        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();

        // create and delete
        if ($link = $linkTable->findRow($id)) {
            $link->robots = "none";
            $link->save();
            $link->publish();
            $this->success(__b("Der Verlinkung wurde erfolgreich deaktiviert"));
        } else {
            $this->error(__b("Der Verlinkung wurde nicht gefunden"));
        }

        // redirect
        $this->_redirect('/administration_seo_links/');
    }

    /**
     * Remove internal link
     * @author vpriem
     */
    public function deleteAction(){

        // get parameters
        $request = $this->getRequest();
        $id = $request->getParam('id');

        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();

        // create and delete
        if ($link = $linkTable->findRow($id)) {
            $link->erase();
            $link->delete();
            $this->success(__b("Der Verlinkung wurde erfolgreich gelöscht"));
        } else {
            $this->error(__b("Der Verlinkung wurde nicht gefunden"));
        }

        // redirect
        $this->_redirect('/administration_seo_links/');
    }

    /**
     * Crawler
     * @author vpriem
     * @since 02.09.2010
     */
    public function crawlerAction(){
    
        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();
        $links = $linkTable->getAll();
        $this->view->assign('links', $links);

    }

    /**
     * Auto linker
     * @author vpriem
     * @since 27.09.2010
     */
    public function autolinkerAction(){

    }

    /**
     * Auto linking
     * @author vpriem
     * @since 27.09.2010
     */
    public function autolinkingAction(){

        set_time_limit(180);

        // get parameters
        $request = $this->getRequest();
        $reset = $request->getParam("reset", false);

        $hits = 0;

        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();
        $linkTable->startAutoLinker($reset);

        $_entries = array();
        $categoryTable = new Yourdelivery_Model_DbTable_Links_Category();
        $categories = $categoryTable->fetchAll();
        foreach ($categories as $category) {
            $links = $category->getLinks();
            shuffle($links);
            foreach ($links as $link) {
                $_entries[$category->level][$category->subLevel][$link['domain']][] = $link['id'];
            }
        }
        $entries = $_entries;

        //
        $rows = $linkTable->getAll();
        foreach ($rows as $row) {
            // get category
            $category = $row->getCategory();
            if (!is_object($category)) {
                continue;
            }
            $domain   = $row->domain;

            // put 10 links into the navigation sidebar
            // getting from the link's sublevel
            $count = count($row->getNavigationLinks());
            if ($count < 10) {
                $level    = $category->level;
                $subLevel = $category->subLevel;
                $w = 0; // avoid infinite loop
                while ($count < 10 && $w < 25) {
                    if (!array_key_exists($domain, $entries[$level][$subLevel])) {
                        break;
                    }
                    if (!count($entries[$level][$subLevel][$domain])) {
                        $links = $_entries[$level][$subLevel][$domain];
                        shuffle($links);
                        $entries[$level][$subLevel][$domain] = $links;
                    }
                    $id = array_shift($entries[$level][$subLevel][$domain]);
                    if ($id === null) {
                        break;
                    }
                    if ($row->addNavigationLink($id, 0)) {
                        $count++;
                        $hits++;
                    }
                    $w++;
                }
            }

            // put 10 links into the list sidebar
            // getting from the link's level
            $count = count($row->getListLinks());
            if ($count < 10) {
                $level  = $category->level;
                $w = 0;
                while ($count < 10 && $w < 25) {
                    if (count($_entries[$level]) < 2) {
                        break;
                    }
                    while (($subLevel = array_rand($_entries[$level])) == $category->subLevel) {
                    }
                    if (!array_key_exists($domain, $entries[$level][$subLevel])) {
                        break;
                    }
                    if (!count($entries[$level][$subLevel][$domain])) {
                        $links = $_entries[$level][$subLevel][$domain];
                        shuffle($links);
                        $entries[$level][$subLevel][$domain] = $links;
                    }
                    $id = array_shift($entries[$level][$subLevel][$domain]);
                    if ($id === null) {
                        break;
                    }
                    if ($row->addListLink($id, 0)) {
                        $count++;
                        $hits++;
                    }
                    $w++;
                }
            }

            // put 9 links into related sidebar
            // getting from the rest
            $count = count($row->getRelatedLinks());
            if ($count < 9) {
                $w = 0; // avoid infinite loop
                while ($count < 9 && $w < 25) {
                    if (count($_entries) < 2) {
                        break;
                    }
                    while (($level = array_rand($_entries)) == $category->level) {
                    }
                    $subLevel = array_rand($_entries[$level]);
                    if (!array_key_exists($domain, $entries[$level][$subLevel])) {
                        break;
                    }
                    if (!count($entries[$level][$subLevel][$domain])) {
                        $links = $_entries[$level][$subLevel][$domain];
                        shuffle($links);
                        $entries[$level][$subLevel][$domain] = $links;
                    }
                    $id = array_shift($entries[$level][$subLevel][$domain]);
                    if ($id === null) {
                        break;
                    }
                    if ($row->addRelatedLink($id, 0)) {
                        $count++;
                        $hits++;
                    }
                    $w++;
                }
            }

            // put 9 links into restaurant sidebar
            $count = count($row->getRestaurants());
            if ($count < 10) {
                $w = 0; // avoid infinite loop
                $restaurants = $row->getRestaurantsFromUrl();
                if (count($restaurants) > 0) {
                    shuffle($restaurants);
                    while ($count < 10 && $w < 25) {
                        $id = array_shift($restaurants);
                        if ($id === null) {
                            break;
                        }
                        if ($row->addRestaurant($id, 0)) {
                            $count++;
                            $hits++;
                        }
                        $w++;
                    }
                }
            }
            
        }

        // publish all
        foreach ($rows as $row) {
            $row->publish();
        }

        // redirect
        $this->success(__b("%s Verlinkung erstellt", $hits));
        $this->_redirect('/administration_seo_links/');

    }

    /**
     * Blacklist
     * @author vpriem
     * @since 08.09.2010
     */
    public function blacklistAction(){

        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_seo_links/crawler');
            }

            // form
            $form = new Yourdelivery_Form_Administration_Seo_Links_Blacklist();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();
                file_put_contents($this->_blacklist, $values['blacklist']);

                // redirect
                if (isset($post['save'])) {
                    $this->_redirect('/administration_seo_links/crawler');
                }
                $this->_redirect('/administration_seo_links/blacklist');

            // error
            } else {
                $this->error($form->getMessages());
            }

        }

        // assign
        $this->view->assign('blacklist', file_get_contents($this->_blacklist));

    }

    /**
     * Crawl all the links
     * @author vpriem
     * @since 01.09.2010
     */
    public function crawlingAction(){
        
        return $this->_redirect('/administration_seo_links/index');
        
        set_time_limit(240);

        // load blacklist
        $blacklist = file_get_contents($this->_blacklist);
        $words = explode(",", $blacklist);
        foreach ($words as &$word) {
            $word = strtolower(trim($word));
        }
        unset($word);
        $badLinks = array();

        // load table
        $linkTable = new Yourdelivery_Model_DbTable_Link();
        $linkTable->startCrawler();

        // get all
        $rows = $linkTable->getAll();
        foreach ($rows as $row) {
            if ($html = @file_get_contents("http://" . $row->getAbsoluteUrl())) {
                preg_match_all('/<a(?:\s+)href="([^"]+)"/', $html, $matches);
                foreach ($matches[1] as $url) {
                    $components = parse_url($url);
                    if ($components !== false && count($components) > 0 && $components['scheme'] != "mailto") {
                        if (!isset($components['host'])) {
                            $components['host'] = $row->domain;
                        }
                        $row->addLinkTo($components['host'] . $components['path']);
                        $link = $linkTable->findByUrl($components['host'], $components['path']);
                        if ($link !== null) {
                            $link->addLinkFrom($row->getAbsoluteUrl());
                        }
                    }
                }

                $html = strtolower($html);
                foreach ($words as $word) {
                    if (substr_count($html, $word)) {
                        $badLinks[] = array($word, $row);
                    }
                }
            }
        }
        $linkTable->stopCrawler();

        // assign
        $this->view->assign("links", $badLinks);

    }

}
