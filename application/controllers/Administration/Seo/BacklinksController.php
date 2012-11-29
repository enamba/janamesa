<?php
/**
 * SEO Backlinks controller
 * @author vpriem
 * @since 20.09.2010
 */
class Administration_Seo_BacklinksController extends Default_Controller_AdministrationBase{

    /**
     * Index
     * @author vpriem
     * @since 20.09.2010
     */
    public function indexAction(){

        // create gid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select(Yourdelivery_Model_DbTable_Backlink::getGrid()));
        $grid->updateColumn('id',  array('decorator' => "#{{id}}"));
        $grid->updateColumn('id',              array('searchType' => '='));
        $grid->updateColumn('url', array('decorator' => '<a href="http://{{url}}" target="_blank">{{url}}</a>'));
        $grid->updateColumn('Fehler', array('title'=>__b('Fehler')));

        // add extra columns
        $col = new Bvb_Grid_Extra_Column();
        $col->position('right')
            ->name(__b('Optionen'))
            ->decorator(
                '<div>
                    <a href="/administration_seo_backlinks/edit/id/{{id}}">'.__b("Editieren").'</a><br />
                    <a href="/administration_seo_backlinks/check/id/{{id}}">'.__b("Prüfen").'</a><br />
                    <a href="#" onclick="return popup(\'/administration_seo_backlinks/result/id/{{id}}\', \'Seo\', 400, 400);">'.__b("Ergebnis").'</a><br />
                    <a href="/administration_seo_backlinks/delete/id/{{id}}" class="yd-are-you-sure">'.__b("Löschen").'</a>
                </div>'
            );
        $grid->addExtraColumns($col);

        // deploy grid to view
        $this->view->grid = $grid->deploy();

    }

    /**
     * Edit backlink
     * @author vpriem
     * @since 20.09.2010
     */
    public function editAction(){

        // load table
        $backlinkTable = new Yourdelivery_Model_DbTable_Backlink();

        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_seo_backlinks/');
            }

            // create
            $backlink = $backlinkTable->findRow($post['id']);
            if (!$backlink) {
                $backlink = $backlinkTable->createRow();
            }

            // form
            $form = new Yourdelivery_Form_Administration_Seo_Backlinks_Edit();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();
                $values['url'] = str_replace("http://", "", $values['url']);
                $backlink->setFromArray($values);

                // save
                try {
                    $backlink->save();
                    $this->success(__b("Backlink successfully saved"));

                } catch (Zend_Db_Statement_Exception $e) {
                    $this->error(__b("Backlink NOT saved"));
                }

                // redirect
                if (isset($post['save'])) {
                    $this->_redirect('/administration_seo_backlinks/');
                }
                $this->_redirect('/administration_seo_backlinks/edit/id/' . $backlink->id);

            // error
            } else {
                $backlink->setFromArray($post);
                $this->error($form->getMessages());
            }

        // get
        } else {
            $id = $request->getParam('id');
            $backlink = $backlinkTable->findRow($id);
            if (!$backlink) {
                $backlink = $backlinkTable->createRow();
            }
        }

        // assign backlink
        $this->view->assign('backlink', $backlink);

    }

    /**
     * Delete backlink
     * @author vpriem
     * @since 20.09.2010
     */
    public function deleteAction(){

        // get parameters
        $request = $this->getRequest();
        $id = $request->getParam('id');

        // load table
        $backlinkTable = new Yourdelivery_Model_DbTable_Backlink();

        // create and delete
        if ($backlink = $backlinkTable->findRow($id)) {
            $backlink->delete();
            $this->success(__b("Backlink successfully deleted"));

        } else {
            $this->error(__b("Backlink was NOT found"));
        }

        // redirect
        $this->_redirect('/administration_seo_backlinks/');

    }

    /**
     * Check backlink
     * @author vpriem
     * @since 22.09.2010
     */
    public function checkAction(){

        // get parameters
        $request = $this->getRequest();
        $id = $request->getParam('id');

        // load table
        $backlinkTable = new Yourdelivery_Model_DbTable_Backlink();

        // create and delete
        if ($backlink = $backlinkTable->findRow($id)) {
            if ($backlink->check()) {
                $this->success(__b("Backlink is correct"));
            }
            else {
                $this->error(__b("Backlink is NOT correct"));
            }

        }
        else {
            $this->error(__b("Backlink was NOT found"));
        }

        // redirect
        $this->_redirect('/administration_seo_backlinks/');

    }

    /**
     * Check all backlinks
     * @author vpriem
     * @since 22.09.2010
     */
    public function checkallAction(){

        // load table
        $backlinkTable = new Yourdelivery_Model_DbTable_Backlink();

        // get all
        $rows = $backlinkTable->fetchAll();
        foreach ($rows as $row) {
            $row->check();
        }

        // redirect
        $this->_redirect('/administration_seo_backlinks/');

    }

    /**
     * Print the catched result
     * @author vpriem
     * @since 18.11.2010
     */
    public function resultAction(){

        // get parameters
        $request = $this->getRequest();
        $id = $request->getParam('id');

        // load table
        $backlinkTable = new Yourdelivery_Model_DbTable_Backlink();

        // create and delete
        if ($backlink = $backlinkTable->findRow($id)) {
            $this->view->assign('backlink', $backlink);
        }
        else {
            $this->error(__b("Backlink was NOT found"));
            $this->_redirect('/administration_seo_backlinks/');
        }

    }

}
