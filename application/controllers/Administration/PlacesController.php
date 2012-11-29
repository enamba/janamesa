<?php

/**
 * Description of CmsController
 * Testlevel-2 und so
 *
 * @author mpantar,afrank
 * @since 24.3.2011
 *
 */
class Administration_PlacesController extends Default_Controller_Auth {

    public function indexAction() {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $ids = $request->getParam('serviceId');
            if (is_array($ids) && count($ids) > 0) {
                $placesEmail = array_reverse($request->getParam('placesEmail'));
                $placesPassword = array_reverse($request->getParam('placesPassword'));
                $placesComment = array_reverse($request->getParam('placesComment'));
                $placesStatus = array_reverse($request->getParam('placesStatus'));
                foreach ($ids as $id) {
                    try {
                        $service = new Yourdelivery_Model_Servicetype_Restaurant($id);
                        $service->setPlacesEmail(array_pop($placesEmail));
                        $service->setPlacesPassword(array_pop($placesPassword));
                        $service->setPlacesComments(array_pop($placesComment));
                        $service->setPlacesStatus(array_pop($placesStatus));
                        $service->save();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        continue;
                    }
                }
                $this->success = __b("Data succesfully saved");
            }
        }

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('r' => 'restaurants'), array(
                    __b('ID') => 'id',
                    __b('Name') => 'r.name',
                    __b('Adresse') => new Zend_Db_Expr('CONCAT (r.street, " ", r.hausnr)'),
                    __b('PLZ') => 'r.plz',
                    __b('Email places') => 'r.placesEmail',
                    __b('Password places') => 'r.placesPassword',
                    __b('Kommentar places') => 'r.placesComment',
                    __b('Status places') => 'r.placesStatus',
                    __b('Status') => 'r.isOnline',
                    __b('Offline Status') => 'r.status'
                ))
                ->joinLeft(array('ct' => 'city'), 'r.cityId = ct.id', array(__b('Stadt') => 'ct.city'))
                ->where('r.deleted=0')
                ->order('r.id DESC')
                ->order('r.isOnline DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(100);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn(__b('Offline Status'), array('callback' => array('function' => 'offlineStatusToReadable', 'params' => array('{{'.__b('Offline Status').'}}'))));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'statusToReadable', 'params' => array('{{'.__b('Status').'}}'))));

        $grid->updateColumn(__b('ID'), array(
            'callback' => array(
                'function' => function($id) {
                    return "<input type='hidden' name='serviceId[]' value='" . $id . "' />" . $id;
                },
                'params' => array('{{'.__b('ID').'}}')
            )
        ));
        $grid->updateColumn(__b('Email places'), array(
            'callback' => array(
                'function' => function($email) {
                    return "<input style='width:100px !important' type='text' name='placesEmail[]' value='" . $email . "' />";
                },
                'params' => array('{{'.__b('Email places').'}}')
            )
        ));
        $grid->updateColumn(__b('Password places'), array(
            'callback' => array(
                'function' => function($password) {
                    return "<input style='width:100px !important' type='text' name='placesPassword[]' value='" . $password . "' />";
                },
                'params' => array('{{'.__b('Password places').'}}')
            )
        ));
        $grid->updateColumn(__b('Kommentar places'), array(
            'callback' => array(
                'function' => function($comment) {
                    return "<input style='width:100px !important' type='text' name='placesComment[]' value='" . $comment . "' />";
                },
                'params' => array('{{'.__b('Kommentar places').'}}')
            )
        ));
        $grid->updateColumn(__b('Status places'), array(
            'callback' => array(
                'function' => function($status) {
                    $select = "<select name='placesStatus[]'>";
                    foreach (array(__b("ja"), __b("nein"), __b("hat nicht geklappt"), __b("anderer Benutzer")) as $option) {
                        if ($option == $status) {
                            $selected = "selected='selected'";
                        } else {
                            $selected = '';
                        }
                        $select .= "<option " . $selected . ">" . $option . "</option>";
                    }
                    $select .= "</select>";
                    return $select;
                },
                'params' => array('{{'.__b('Status places').'}}')
            )
        ));

        $this->view->grid = $grid->deploy();
    }

    public function placesAction() {
        
    }

    public function emailAction() {
        
    }

    public function editAction() {
        
    }

    /**
     * 401 Authorization Required
     * @author vpriem
     * @since 23.02.2011
     */
    public function requiredAction() {
        $this->getResponse()->setHttpResponseCode(401);
    }

}

?>
