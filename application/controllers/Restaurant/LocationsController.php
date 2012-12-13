<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * restaurant delivering locations management
 *
 * @author alex
 */
class Restaurant_LocationsController extends Default_Controller_RestaurantBase {

    /**
     * create meal option
     * @author alex
     */
    public function createAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        // courier restaurant cannot be added
        if ($restaurant->hasCourier()) {
            $this->error("Das Liefergebiet kann nicht hinzugefügt werden, siehe Kurierdienst " . $restaurant->getCourier()->getName());
            $this->_redirect('/restaurant/locations');
        }

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_PlzCreate();
            if($form->isValid($post)) {
                $values = $form->getValues();
                $restaurant->createLocation($values['cityId'], $values['mincost'], $values['delcost'], $values['deliverTime']*60, $values['noDeliverCostAbove']);
                $this->success('Address has been succesfully added');
            }
            else {
                $this->error($form->getMessages());
            }
        }
        
        //get path so the sorting and filtering will stay when we edit some location
        $path = $this->session->locationspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/locations');
        }
    }

    /**
     * edit location
     * @author alex
     */
    public function editAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        //create location object
        try {
            $location = new Yourdelivery_Model_Servicetype_Plz($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error('Dieses Liefergebiet gibt es nicht!');
            $this->_redirect('/restaurant/locations');
        }

        if ( $request->isPost() ) {
            $post = $request->getPost();

            //edit location
            if ($request->getParam('cancel') !== null) {
                //get path so the sorting and filtering will stay when we edit some location
                $path = $this->session->locationspath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/locations');
                }
            }

            $form = new Yourdelivery_Form_Restaurant_PlzEdit();

            if($form->isValid($post)) {
                $values = $form->getValues();

                $values['deliverTime'] = $values['deliverTime']*60;
                $values['mincost'] = priceToInt2($values['mincost']);
                $values['delcost'] = priceToInt2($values['delcost']);
                $values['noDeliverCostAbove'] = priceToInt2($values['noDeliverCostAbove']);

                // just edit deliverTime for courier restaurant
                if ($restaurant->hasCourier()) {
                    $location->setDeliverTime($values['deliverTime']);
                    $location->save();

                    $this->success("Nur die Lieferzeit konnte geändert werden, siehe Kurierdienst " . $restaurant->getCourier()->getName());
                    $this->_redirect('/restaurant/locations');
                }

                $location->setData($values);
                $location->save();
                $this->logger->adminInfo(sprintf('Daten für PLZ %s vom Restaurant %d wurden geändert', $location->getPlz(), $restaurant->getId()));
                
                $this->success('PLZ ' . $location->getPlz() . ' wurde geändert');
                //get path so the sorting and filtering will stay when we edit some location
                $path = $this->session->locationspath;
                if (!is_null($path)) {
                    $this->_redirect($path);
                }
                else {
                    $this->_redirect('/restaurant/locations');
                }
            }
            else {
                $this->error($form->getMessages());
                $this->_redirect('/restaurant_locations/edit/id/' . $location->getId());
            }
        }

        try {
            $city = new Yourdelivery_Model_City($location->getCityId());
            $this->view->assign('city', $city);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
        }

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('location', $location);
    }

    /**
     * delete location
     * @author alex
     */
    public function deleteAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }
        
        // courier restaurant cannot be deleted
        if ($restaurant->hasCourier()) {
            $this->error("Das Liefergebiet kann nicht gelöscht werden, siehe Kurierdienst " . $restaurant->getCourier()->getName());
            $this->_redirect('/restaurant/locations');
        }

        $request = $this->getRequest();

        if ($request->getParam('id', false)) {
            try {
                $location = new Yourdelivery_Model_Servicetype_Plz($request->getParam('id'));            
                $restaurant->deleteRange($request->getParam('id'));
                $this->logger->adminInfo(sprintf('PLZ %s vom Restaurant %d wurde gelöscht', $location->getPlz(), $restaurant->getId()));
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $this->error("Das Liefergebiet wurde nicht gefunden!");
            }
        }
        
        //get path so the sorting and filtering will stay when we edit some location
        $path = $this->session->locationspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/locations');
        }
    }

    /**
     * delete all locations
     * @author alex
     */
    public function deleteallAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        // courier restaurant cannot be deleted
        if ($restaurant->hasCourier()) {
            $this->error("Das Liefergebiet kann nicht gelöscht werden, siehe Kurierdienst " . $restaurant->getCourier()->getName());
            $this->_redirect('/restaurant/locations');
        }

        $request = $this->getRequest();
        $restaurant->deleteAllRanges();
        $this->logger->adminInfo(sprintf('Alle Liefergebiete vom Restaurant %d wurden gelöscht', $restaurant->getId()));

        $this->_redirect('/restaurant/locations');
    }

    /**
     * add entire city
     * @author alex
     */
    public function addcityAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        // courier restaurant cannot be added
        if ($restaurant->hasCourier()) {
            $this->error("Das Liefergebiet kann nicht hinzugefügt werden, siehe Kurierdienst " . $restaurant->getCourier()->getName());
            $this->_redirect('/restaurant/locations');
        }

        $request = $this->getRequest();

        $city =     $request->getParam('city');
        $deltime =  $request->getParam('deliverTime');
        $mincost =  $request->getParam('mincost');
        $delcost =  $request->getParam('delcost');
        $noDeliverCostAbove =  $request->getParam('noDeliverCostAbove');

        if ( !($restaurant->createCityRange($city, $mincost, $delcost, $deltime*60, $noDeliverCostAbove)) ) {
            $this->error('Fehler beim anlegen der Liefergebiete');
        }
        else {
            $this->logger->adminInfo(sprintf('Alle Liefergebiete vom Stadt %s wurden dem Restaurant %d hinzugefügt', $city, $restaurant->getId()));
            $this->success('Die Stadt wurde hinzugefügt');
        }

        //get path so the sorting and filtering will stay when we edit some location
        $path = $this->session->locationspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/locations');
        }
    }

    /**
     * change all locations
     * @author alex
     */
    public function changeallAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ( $request->isPost() ) {

            $post = $request->getPost();

            $time = $request->getParam('deliverTime', null);
            $mincost = $request->getParam('mincost', null);
            $delcost = $request->getParam('delcost', null);
            $isOnline = $request->getParam('status', null);
            $noDeliverCostAbove = $request->getParam('noDeliverCostAbove', null);

            $selectedLocations = $post['yd-location'];

            // just edit deliverTime for courier restaurant
            if ($restaurant->hasCourier()) {
                if (strcmp($time, 'Não alterar') == 0) {
                    $time = null;
                }
                if (strcmp($mincost, 'Não alterar') == 0) {
                    $mincost = null;
                }
                if (strcmp($delcost, 'Não alterar') == 0) {
                    $delcost = null;
                }
                if (strcmp($noDeliverCostAbove, 'Não alterar') == 0) {
                    $noDeliverCostAbove = null;
                }
                if ($isOnline == -1) {
                    $isOnline = null;
                }
                foreach ($selectedLocations as $plzId => $val) {
                    $plz = new Yourdelivery_Model_Servicetype_Plz($plzId);
                    if (!is_null($time)) {
                        $plz->setDeliverTime($time * 60);
                    }
                    if (!is_null($isOnline)) {
                        $plz->setStatus($isOnline);
                    }
                    $plz->save();
                }
                $this->success("Nur die Lieferzeit konnte geändert werden, siehe Kurierdienst " . $restaurant->getCourier()->getName());
                $this->_redirect('/restaurant/locations');
            }

            if (is_array($selectedLocations)) {
                foreach ($selectedLocations as $plzId => $val) {
                    if (isset($post['deleteSelected'])) {
                        $restaurant->deleteRange($plzId);
                    }
                    else {
                        // test if we should change this fields
                        if (strcmp($time, 'Não alterar') == 0) {
                            $time = null;
                        }
                        if (strcmp($mincost, 'Não alterar') == 0) {
                            $mincost = null;
                        }
                        if (strcmp($delcost, 'Não alterar') == 0) {
                            $delcost = null;
                        }
                        if (strcmp($noDeliverCostAbove, 'Não alterar') == 0) {
                            $noDeliverCostAbove = null;
                        }

                        if ($isOnline == -1) {
                            $isOnline = null;
                        }

                        try {                        
                            $plz = new Yourdelivery_Model_Servicetype_Plz($plzId);
                        }
                        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                            continue;
                        }

                        if (!is_null($isOnline)) {
                            $plz->setStatus($isOnline);
                        }

                        if (!is_null($time)) {
                            $plz->setDeliverTime($time*60);
                        }

                        if (!is_null($mincost)) {
                            $plz->setMincost(priceToInt2($mincost));
                        }

                        if (!is_null($delcost)) {
                            $plz->setDelcost(priceToInt2($delcost));
                        }

                        if (!is_null($noDeliverCostAbove)) {
                            $plz->setNoDeliverCostAbove(priceToInt2($noDeliverCostAbove));
                        }

                        $plz->save();
                    }
                }
            }


            if (isset($post['deleteSelected'])) {
                $this->success('Markierte Gebiete wurden gelöscht');
            }
            else {
                $this->success('Markierte Gebiete wurden geändert');
            }
            //get path so the sorting and filtering will stay when we edit some location
            $path = $this->session->locationspath;
            if (!is_null($path)) {
                $this->_redirect($path);
            }
        }
        $this->_redirect('/restaurant/locations');
    }

    /**
     * add all plz ranges, starting with specified string
     * @author Alex Vait <vait@lieferando.de>
     * @since 05.01.2012
     */
    public function addprefixplzAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        // courier restaurant cannot be added
        if ($restaurant->hasCourier()) {
            $this->error("Das Liefergebiet kann nicht hinzugefügt werden, siehe Kurierdienst " . $restaurant->getCourier()->getName());
            $this->_redirect('/restaurant/locations');
        }

        $request = $this->getRequest();

        $plzprefix = $request->getParam('plzprefix');
        $deltime =  $request->getParam('deliverTime');
        $mincost =  $request->getParam('mincost');
        $delcost =  $request->getParam('delcost');
        $noDeliverCostAbove =  $request->getParam('noDeliverCostAbove');

        $plzprefix = preg_replace("/[^-0-9.]/", "", $plzprefix);

        if ( !($restaurant->createPlzByPrefixRange($plzprefix, $mincost, $delcost, $deltime*60, $noDeliverCostAbove)) ) {
            $this->error('Fehler beim anlegen der Liefergebiete');
        }
        else {
            $this->logger->adminInfo(sprintf('Alle Liefergebiete mit PLZ %s... wurden dem Restaurant %d hinzugefügt', $plzprefix, $restaurant->getId()));
            $this->success('Die Liefergebiete wurden hinzugefügt');
        }

        //get path so the sorting and filtering will stay when we edit some location
        $path = $this->session->locationspath;
        if (!is_null($path)) {
            $this->_redirect($path);
        }
        else {
            $this->_redirect('/restaurant/locations');
        }
    }    

}
?>
