<?php

/**
 * @author alex
 */
class Administration_Service_RatingsController extends Default_Controller_AdministrationBase {

    /**
     * Table with all ratings
     * @author alex
     * @since 24.11.2011
     */
    public function indexAction() {

        // build select
        $db = Zend_Registry::get('dbAdapter');

        $select = $db
                ->select()
                ->from(array('rr' => 'restaurant_ratings'), array(
                    __b('Status') => 'rr.status',
                    __b('ID') => 'rr.id',
                        'Am' => new Zend_Db_Expr('DATE_FORMAT(rr.created, "%d.%m.%Y %H:%i")'),
                    __b('Author') => 'rr.author',
                    __b('Titel') => 'rr.title',
                    __b('Kommentar') => 'rr.comment',
                    __b('Qualität') => 'rr.quality',
                    __b('Lieferung') => 'rr.delivery',
                    __b('Empehlung') => 'rr.advise',
                    __b('Sorry') => 'rr.crmEmail',
                    __b('Top') => 'rr.topRating',
                ))
                ->joinLeft(array('oc' => 'orders_customer'), 'oc.orderId = rr.orderId', array(
                    __b('Kunde') => new Zend_Db_Expr('CONCAT (oc.prename, " ", oc.name)'),
                    __b('EMail') => 'oc.email',
                    __b('Bestellung') => 'oc.orderId'))
                ->joinLeft(array('r' => 'restaurants'), 'rr.restaurantId = r.id', array(
                    __b('Restaurant') => 'r.name',
                    __b('RestaurantId') => 'r.id'))
                ->order('rr.created DESC');

        // build grid
        $grid = Default_Helper::getTableGrid('ratings');
        $grid->setExport(array());
        $grid->setPagination(20);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('ID'), array('class' => 'ratingId'));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'ratingStatusToImg', 'params' => array('{{' . __b('ID') . '}}', '{{' . __b('Status') . '}}'))));
        $grid->updateColumn('Am', array('title' => __b('Am')));
        $grid->updateColumn(__b('Top'), array('title' => 'Top Bew.', 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{' . __b('Top') . '}}'))));
        $grid->updateColumn(__b('Kunde'), array('callback' => array('function' => 'getRegisteredCustomerLink', 'params' => array('{{' . __b('Kunde') . '}}', '{{' . __b('EMail') . '}}'))));
        $grid->updateColumn(__b('EMail'), array('decorator' => '<a href="mailto:{{' . __b('EMail') . '}}">{{' . __b('EMail') . '}}</a>'));
        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/index/id/{{' . __b('RestaurantId') . '}}" target="_blank">{{' . __b('Restaurant') . '}}</a>'));
        $grid->updateColumn(__b('Lieferung'), array('callback' => array('function' => 'ratingsToImg', 'params' => array('{{' . __b('Lieferung') . '}}'))));
        $grid->updateColumn(__b('Qualität'), array('callback' => array('function' => 'ratingsToImg', 'params' => array('{{' . __b('Qualität') . '}}'))));
        $grid->updateColumn(__b('Empehlung'), array('callback' => array('function' => 'adviseToImg', 'params' => array('{{' . __b('Empehlung') . '}}', '{{' . __b('ID') . '}}'))));
        $grid->updateColumn(__b('Sorry'), array('callback' => array('function' => 'crmEmailLink', 'params' => array('{{' . __b('ID') . '}}', '{{' . __b('Sorry') . '}}'))));
        $grid->updateColumn(__b('Bestellung'), array('callback' => array('function' => 'orderPopupLink', 'params' => array('{{' . __b('Bestellung') . '}}'))));

        // add row for editing ratings
        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('left')
                ->name(__b('Optionen'))
                ->decorator(
                        '<div>
                    <a href="/administration_service_ratings/edit/id/{{' . __b('ID') . '}}"><img src="/media/images/yd-backend/cust_edit.png"/ alt="' . __b("Bearbeiten") . '"></a>
                </div>'
        );
        $grid->addExtraColumns($option);

        // add filters
        $filters = new Bvb_Grid_Filters();
        $activeStatis = array(
            1 => __b('Online'),
            0 => __b('Offline'),
            '' => __b('Alle'),
        );

        $yesno = array(
            1 => __b('Ja'),
            0 => __b('Nein'),
            '' => __b('Alle'),
        );
        $filters
                ->addFilter(__b('ID'))
                ->addFilter('Am')
                ->addFilter(__b('Status'), array('values' => $activeStatis))
                ->addFilter(__b('Top'), array('values' => $yesno))
                ->addFilter(__b('Author'))
                ->addFilter(__b('Titel'))
                ->addFilter(__b('Kommentar'))
                ->addFilter(__b('Lieferung'))
                ->addFilter(__b('Qualität'))
                ->addFilter(__b('Empehlung'), array('values' => $yesno))
                ->addFilter(__b('Sorry'), array('values' => array(
                        1 => __b('Schon verschickt'),
                        0 => __b('Noch nicht verschickt'),
                        '' => __b('Alle'),
                        )))
                ->addFilter(__b('Kunde'))
                ->addFilter(__b('EMail'))
                ->addFilter(__b('Bestellung'))
                ->addFilter(__b('RestaurantId'))
                ->addFilter(__b('Restaurant'))
                ->addFilter(__b('CrmEmail'));

        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Create new rating for this restaurant
     * @author alex
     * @since 17.11.2010
     */
    public function createAction() {

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Administration_Service_Rating_Create();
            $post = $request->getPost();

            // validate the form
            if ($form->isValid($post)) {
                $values = $form->getValues();

                $rating = new Yourdelivery_Model_Servicetype_Rating();
                $rating->setData($values);
                $rating->save();

                $this->logger->adminInfo(sprintf('New rating #%d was created', $rating->getId()));

                $this->success(__b("Bewertung wurde erstellt"));
                $this->_redirect('/administration_service_ratings/');
            } else {
                $this->error($form->getMessages());
            }
        }

        // the list of all restaurants
        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());
    }

    /**
     * Edit rating
     * @author alex
     * @since 25.11.2010
     */
    public function editAction() {
        $admin = $this->session->admin;

        $request = $this->getRequest();
        $id = $request->getParam('id');

        if ($id === null) {
            $this->error(__b("Diese Bewertung gibt es nicht!"));
            $this->_redirect('/administration_service_ratings/');
        }

        //create rating object
        try {
            $rating = new Yourdelivery_Model_Servicetype_Rating($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diese Bewertung gibt es nicht!"));
            $this->_redirect('/administration_service_ratings/');
        }

        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Administration_Service_Rating_Edit();
            $post = $request->getPost();

            // validate the form
            if ($form->isValid($post)) {
                $values = $form->getValues();

                $oldValues = $rating->getData();

                $rating->setData($values);
                $rating->save();

                $this->logger->adminInfo(sprintf("Changed data for rating %d from Author: %s / Title : %s / Comment : %s / Quality : %s / Delivery : %s / Advise : %s to Author: %s / Title : %s / Comment : %s / Quality : %s / Delivery : %s / Advise : %s", $rating->getId(), $oldValues['author'], $oldValues['title'], $oldValues['comment'], $oldValues['quality'], $oldValues['delivery'], $oldValues['advise'], $values['author'], $values['title'], $values['comment'], $values['quality'], $values['delivery'], $values['advise']
                        ));

                $this->success(__b("Bewertung wurde bearbeitet"));
                $this->_redirect('/administration_service_ratings/');
            } else {
                $this->error($form->getMessages());
            }
        }

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rating->getRestaurantId());
        $this->view->assign('restaurant', $restaurant);

        $this->view->assign('rating', $rating);
    }

    /**
     * Activate ratings in batch process
     * @author Alex Vait <vait@lieferando.de>
     * @since 09.01.2012
     */
    public function batchactivateAction() {

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 1200);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Administration_Service_Rating_Editbatch();
            $post = $request->getPost();

            // validate the form
            if ($form->isValid($post)) {
                $values = $form->getValues();

                $from = date('Y-m-d', strtotime($values['from']));
                $until = date('Y-m-d', strtotime($values['until']));

                $ratings = Yourdelivery_Model_Servicetype_Rating::getRatingsInTimeslot($from, $until, $values['advise']);

                $countRatingsActivated = 0;
                $concernedRestaurants = array();

                foreach ($ratings as $r) {
                    try {
                        $rating = new Yourdelivery_Model_Servicetype_Rating($r['id']);
                        $rating->activate();

                        $countRatingsActivated++;
                        if (!in_array($r['restaurantId'], $concernedRestaurants)) {
                            $concernedRestaurants[] = $r['restaurantId'];
                        }

                        $this->logger->adminInfo(sprintf('Rating %d set online in batch processing', $r['id']));
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->debug($e->getMessage());
                    }
                }

                // clear cached for all restaurants with activated ratings so the changes are seen in frontend
                foreach ($concernedRestaurants as $restId) {
                    try {
                        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restId);
                        $restaurant->uncacheRating();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        
                    }
                }

                $this->success(sprintf("%d Bewertungen wurden freigeschaltet", $countRatingsActivated));
            } else {
                $this->error($form->getMessages());
            }
        }
        $this->_redirect('/administration_service_ratings/');
    }

}