<?php

require_once APPLICATION_PATH . '/../library/Default/IdnaConverter.php';

/**
 * Satellite controller
 * @author vpriem
 */
class Administration_SatelliteController extends Default_Controller_AdministrationBase {

    protected $satellite = null;

    /**
     * Init
     * @author vpriem
     */
    public function init() {
        parent::init();
        $request = $this->getRequest();

        //create satellite object

        try {
            $satId = (integer) $request->getParam('id', 0);
            if ($satId > 0) {
                $this->satellite = new Yourdelivery_Model_Satellite($satId);
                $this->view->satellite = $this->satellite;
            } else {
                $this->satellite = new Yourdelivery_Model_Satellite();
                $this->view->satellite = $this->satellite;
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Satellite does not exist!"));
            $this->_redirect('/administration_satellite');
        }
    }

    /**
     * Show satellites
     * @author alex
     * @since 27.04.2011
     */
    public function indexAction() {
        // create gid
        $db = Zend_Registry::get('dbAdapter');
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);

        $select = $db->select()
                ->from(array('s' => 'satellites'), array(
                    'id',
                    'domain',
                    'url',
                    __b('status') => 'title',
                    'title',
                    __b('Beschreibung') => 'description',
                    'keywords',
                    'robots',
                    'disabled',
                ))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = s.restaurantId', array(
                    __b('RestaurantId') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Strasse') => new Zend_Db_Expr("CONCAT(r.street, ' ', r.hausnr)"),
                ))
                ->joinLeft(array('c' => 'city'), 'r.cityId = c.id', array(
                    __b('PLZ') => 'c.plz',
                    __b('Stadt') => 'c.city',
                ))
                ->order('s.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn('id', array('decorator' => "#{{id}}"));
        $grid->updateColumn('id', array('searchType' => '='));
        $grid->updateColumn('domain', array('callback' => array(
                'function' => function ($domain, $disabled) {
                    return $disabled ? '<a href="http://' . $domain . '" style="text-decoration:line-through;" target="_blank">' . $domain . '</a>' : '<a href="http://' . $domain . '" target="_blank">' . $domain . '</a>';
                },
                'params' => array('{{domain}}', '{{disabled}}')
                )));

        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/index/id/{{' . __b('RestaurantId') . '}}">{{' . __b('Restaurant') . '}}</a>'));
        $grid->updateColumn('keywords', array('hidden' => 1));
        $grid->updateColumn(__b('status'), array('callback' => array('function' => 'dataComplete', 'params' => array('{{title}}', '{{keywords}}', '{{' . __b('Beschreibung') . '}}'))));
        //$grid->updateColumn('Beschreibung', array('hidden' => 1));
        $grid->updateColumn('robots', array('hidden' => 1));
        $grid->updateColumn('disabled', array('hidden' => 1));

        // add extra columns
        $col = new Bvb_Grid_Extra_Column();
        $col->position('right')
                ->name(__b('Optionen'))
                ->decorator(
                        '<a href="/administration_satellite/edit/id/{{id}}">' . __b("Editieren") . '</a>
                        <a href="/satellite/index/id/{{id}}" target="_blank">' . __b("Vorschau") . '</a>'
        );
        $grid->addExtraColumns($col);


        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Create or edit satellite
     * 
     * @since 28.04.2011
     * @author Alex Vait <vait@lieferando.de>
     * @modified Matthias Laug <laug@lieferando.de>
     */
    public function editAction() {
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_satellite/');
            }

            // strip http at the start and all slashes in domain
            $post['domain'] = str_replace("http://", "", $post['domain']);
            $post['domain'] = str_replace("/", "", $post['domain']);

            //check for leading slash
            if (strlen($post['url']) > 0 && !preg_match('/^\//', $post['url'])) {
                $post['url'] = '/' . $post['url'];
            }

            // form
            $form = new Yourdelivery_Form_Administration_Satellite_Edit();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();

                $IDN = new idna_convert(array('idn_version' => 2008));
                $values['domain'] = $IDN->encode($values['domain']);

                $this->satellite->setData($values);

                // save
                try {
                    $this->satellite->save();
                } catch (Exception $e) {
                    $this->error(__b("Der Satellite konnte nicht gespeichert werden: ") . $e->getMessage());
                    $this->logger->adminWarn(sprintf("Satellite #%d could not be edited with message %s", $this->satellite->getId(), $e->getMessage()));
                    $this->_redirect('/administration_satellite/edit/id/' . $this->satellite->getId());
                }

                // upload logo image
                if ($form->_logo->isUploaded()) {
                    $this->satellite->addPicture($form->_logo->getFileName(), null, 'logo');
                }

                // upload background image
                if ($form->_background->isUploaded()) {
                    $this->satellite->addPicture($form->_background->getFileName(), null, 'background');
                }

                // upload certification image
                if ($form->_certification->isUploaded()) {
                    $this->satellite->addPicture($form->_certification->getFileName(), null, 'certification');
                }

                if (is_null($request->getParam('id', null))) {
                    $this->success(__b("Satellite was created"));
                    $this->logger->adminInfo(sprintf("Satellite #%d was created", $this->satellite->getId()));
                } else {
                    $this->success(__b("Satellite was edited"));
                    $this->logger->adminInfo(sprintf("Satellite #%d was edited", $this->satellite->getId()));
                }

                if ($this->satellite->getService()) {
                    $this->satellite->getService()->setKommSat($form->getValue('kommSat'));
                    $this->satellite->getService()->setItemSat($form->getValue('itemSat'));
                    $this->satellite->getService()->setFeeSat($form->getValue('feeSat'));
                    $this->satellite->getService()->save();
                }

                $this->_redirect('/administration_satellite/edit/id/' . $this->satellite->getId());
            } else {
                $this->error($form->getMessages());
                $this->_redirect('/administration_satellite/edit/id/' . $this->satellite->getId());
            }
        }

        // set satellite
        $this->view->assign('satellite', $this->satellite);

        // get robots
        $this->view->assign('robots', array(
            'all' => "index,follow",
            'index,nofollow' => "index,nofollow",
            'none' => "noindex,nofollow",
            'noindex,follow' => "noindex,follow",
        ));

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());
    }

    /**
     * Add/remove pictures for the satellite
     * @since 28.04.2011
     * @author alex
     */
    public function addpictureAction() {
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_satellite/');
            }

            // form
            $form = new Yourdelivery_Form_Administration_Satellite_Addpicture();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();

                $spic = new Yourdelivery_Model_Satellite_Picture();
                $spic->setSatelliteId($this->satellite->getId());
                $spic->setDescription($values['description']);
                $spic->save();


                // upload new image
                if ($form->_picture->isUploaded()) {
                    $spic->setPicture($form->_picture->getFileName());
                }

                $this->success(__b("Picture was added"));
                $this->logger->adminInfo(sprintf("New picture #%d for satellite #%d was added ", $spic->getId(), $this->satellite->getId()));

                $this->_redirect('/administration_satellite/editpictures/id/' . $this->satellite->getId());
            } else {
                $this->error($form->getMessages());
            }
        }

        // set satellite
        $this->view->assign('satellite', $this->satellite);
    }

    /**
     * Edit certain picture
     * @since 02.05.2011
     * @author alex
     */
    public function editpictureAction() {
        $request = $this->getRequest();

        $pictureId = $request->getParam('pictureId', null);

        if (is_null($pictureId)) {
            $this->error(__b("No picture id was provided!"));
            $this->_redirect('/administration_satellite/editpictures/id/' . $this->satellite->getId());
        }

        //create satellite picture object
        try {
            $picture = new Yourdelivery_Model_Satellite_Picture($pictureId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This picture does not exist!"));
            $this->_redirect('/administration_satellite/editpictures/id/' . $this->satellite->getId());
        }

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_satellite/');
            }

            // form
            $form = new Yourdelivery_Form_Administration_Satellite_Editpicture();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();

                $picture->setData($values);
                $picture->save();

                // upload new image
                if ($form->_picture->isUploaded()) {
                    $picture->setPicture($form->_picture->getFileName());
                }

                $this->success(__b("Picture was edited"));
                $this->logger->adminInfo(sprintf("Picture #%d for satellite #%d was edited", $picture->getId(), $this->satellite->getId()));
            } else {
                $this->error($form->getMessages());
            }
        }

        $this->_redirect('/administration_satellite/editpictures/id/' . $this->satellite->getId());
    }

    /**
     * Add picture for the satellite
     * @since 28.04.2011
     * @author alex
     */
    public function editpicturesAction() {
        $request = $this->getRequest();
        // set satellite
        $this->view->assign('satellite', $this->satellite);
    }

    /**
     * Add/remove random pictures for the satellite
     * @since 02.05.2011
     * @author alex
     */
    public function editrandompicturesAction() {
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_satellite/');
            }

            // form
            $form = new Yourdelivery_Form_Administration_Satellite_Addrandompicture();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();

                // upload new image
                if ($form->_picture->isUploaded()) {
                    $this->satellite->addPicture($form->_picture->getFileName(), 'random', 'random' . time());
                }

                $this->success(__b("Random picture was added"));
                $this->logger->adminInfo(sprintf("New random picture for satellite #%d was added ", $this->satellite->getId()));

                $this->_redirect('/administration_satellite/editrandompictures/id/' . $this->satellite->getId());
            } else {
                $this->error($form->getMessages());
            }
        }

        // set satellite
        $this->view->assign('satellite', $this->satellite);
    }

    /**
     * Add/remove pictures for the satellite
     * @since 28.04.2011
     * @author alex
     */
    public function editcssAction() {

        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_satellite/');
            }
            if ($this->satellite->isPremium()) {
                $this->error(__b("Couldn\'t update css from Premium Satellite"));
                $this->_redirect('/administration_satellite/editcss/id/' . $this->satellite->getId());
            }
            // save css values and remove possible default css template assiciation
            if (isset($post['css']) && is_array($post['css'])) {
                $templateName = null;

                // also save data as template
                if (isset($post['saveTemplate'])) {
                    if (!empty($post['templateName'])) {
                        $templateName = preg_replace("/[^a-zA-Z0-9_-]/i", "_", strtolower($post['templateName']));

                        $storage = new Default_File_Storage();
                        $storage->setSubFolder('satellites/css');

                        $cssData = "";
                        $cssData = $cssData . "[colors]" . LF;
                        foreach ($post['css'] as $key => $value) {
                            if (strlen($value) > 0) {
                                $cssData = $cssData . $key . ' = "' . $value . '"' . LF;
                            }
                        }
                        try {
                            $storage->store("color-" . $templateName . ".ini", $cssData);
                        } catch (Exception $e) {
                            return $this->error(__b("Could not save template. (%s)", $e->getMessage()));
                        }
                    } else {
                        return $this->error(__b("Name for template was not specified! Template was not saved"));
                    }
                }

                $this->satellite->setCssTemplate($templateName);
                $this->satellite->setCssProperties($post['css']);
                $this->satellite->save();
                $this->success(__b("CSS successfully edited."));

                $this->_redirect('/administration_satellite/editcss/id/' . $this->satellite->getId());
            }


            $this->warn(__b("No changes was made."));
        }

        // set satellite
        $this->view->assign('satellite', $this->satellite);
        $this->view->assign('properties', $this->satellite->getCssProperties());

        // get css docs
        $doc = @parse_ini_file(APPLICATION_PATH . '/../public/media/css/satellites/color.ini');
        $this->view->assign('doc', is_array($doc) ? $doc : array());


        if (!is_dir(APPLICATION_PATH . '/../storage/satellites/css')) {
            mkdir(APPLICATION_PATH . '/../storage/satellites/css');
        }
        $tmpl = glob(APPLICATION_PATH . '/../storage/satellites/css/color-*.ini');
        $templates = array();
        foreach ($tmpl as $t) {
            $templates[] = substr(basename($t), 6, strpos(basename($t), '.') - 6);
        }
        $this->view->assign('templates', $templates);
    }

    /**
     * Set css-template
     * @since 23.05.2011
     * @author alex
     */
    public function settemplateAction() {
        $request = $this->getRequest();
        if ($this->satellite->isPremium()) {
            $this->error(__b("Couldn\'t update css from Premium Satellite"));
            $this->_redirect('/administration_satellite/editcss/id/' . $this->satellite->getId());
        }

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['selectedTemplate'])) {
                $this->satellite->setCssTemplate($post['selectedTemplate']);
                $this->satellite->save();
                $this->success(__b("CSS template successfully set"));
            }
        }
        $this->_redirect('/administration_satellite/editcss/id/' . $this->satellite->getId());
    }

    /**
     * delete picture associated with this satellite
     * @since 28.04.2011
     * @author alex
     */
    public function deletepictureAction() {
        $request = $this->getRequest();

        if (is_null($request->getParam('pictureId', null))) {
            $this->error(__b("Missing Parameter!"));
            $this->_redirect('/administration_satellite');
        }

        $this->satellite->removePicture($request->getParam('pictureId'));

        $this->success(__b("Picture was deleted"));
        $this->logger->adminInfo(sprintf("Successfully deleted picture %s for satellite #%d", $request->getParam('pictureId'), $this->satellite->getId()));

        $this->_redirect('/administration_satellite/editpictures/id/' . $this->satellite->getId());
    }

    /**
     * delete random picture associated with this satellite
     * @since 02.05.2011
     * @author alex
     */
    public function deleterandompictureAction() {
        $request = $this->getRequest();

        if (is_null($request->getParam('picture', null))) {
            $this->error(__b("Missing Parameter!"));
            $this->_redirect('/administration_satellite');
        }

        if ($this->satellite->removeRandomPicture($request->getParam('picture'))) {
            $this->success(__b("Random picture was deleted"));
            $this->logger->adminInfo(sprintf("Successfully deleted random picture %s for satellite #%d", $request->getParam('picture'), $this->satellite->getId()));
        } else {
            $this->error(__b("Couldn\'t delete random picture"));
        }


        $this->_redirect('/administration_satellite/editrandompictures/id/' . $this->satellite->getId());
    }

    /**
     * Create or edit satellite
     * @since 28.04.2011
     * @author alex
     */
    public function editjobsAction() {
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // form
            $form = new Yourdelivery_Form_Administration_Satellite_Editjobs();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();
                $this->satellite->setData($values);

                // save
                try {
                    $this->satellite->save();
                } catch (Exception $e) {
                    $this->error(__b("Satellite couldn't be edited: ") . $e->getMessage());
                    $this->logger->adminWarn(sprintf("Satellite #%d could not be edited: %s", $this->satellite->getId(), $e->getMessage()));
                    $this->_redirect('/administration_satellite/edit/id/' . $this->satellite->getId());
                }

                // upload job formular image
                if ($form->_jobFormularImg->isUploaded()) {
                    $this->satellite->addPicture($form->_jobFormularImg->getFileName(), null, 'jobFormularImg');
                }

                // upload job text image
                if ($form->_jobTextImg->isUploaded()) {
                    $this->satellite->addPicture($form->_jobTextImg->getFileName(), null, 'jobTexImg');
                }

                $this->success(__b("Changes succesfully saved!"));
            } else {
                $this->error($form->getMessages());
            }
            $this->_redirect('/administration_satellite/editjobs/id/' . $this->satellite->getId());
        }

        // set satellite
        $this->view->assign('satellite', $this->satellite);
    }

    public function htmleditAction() {
        
    }

}
