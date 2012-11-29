<?php

/**
 * restaurant settings management
 *
 * @author alex
 */
class Restaurant_SettingsController extends Default_Controller_RestaurantBase {


    /**
     * show forms for settings editing
     * @author alex
     */
    public function indexAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
           return  $this->_redirect('/index');
        }

        $this->view->assign('restaurant', $restaurant);
        $this->view->assign('openings', $restaurant->getRegularOpenings());
        $this->view->assign('holidayOpenings', $restaurant->getRegularOpenings(10));
        $this->view->assign('specialopenings', Yourdelivery_Model_Servicetype_OpeningsSpecial::getSpecialOpening($restaurant->getId()));
        $this->view->assign('categories', Yourdelivery_Model_Servicetype_Categories::all());
        
        $tagsFormatted = array();
        
        foreach ($restaurant->getAllTagsWithFlag() as $tag) {
            $tagsFormatted[$tag['id']] = array('name' => htmlspecialchars($tag['name']), 'flag' => ($tag['tagAssoc'] > 0));
        }
        $this->view->assign('tags', $tagsFormatted);

    }

    /**
     * edit restaurant settings
     * @author alex
     */
    public function editAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Restaurant_Settings();
            if ( $form->isValid($post) ) {
                $values = $form->getValues();

                //if restaurant is set as offline, also change the status to 'offline'
                if ($values['isOnline'] == 1) {
                    $values['status'] = 1;
                }
                else {
                    $values['status'] = 0;
                }

                if($form->img->isUploaded() ) {
                    $restaurant->setImg($form->img->getFileName());
                }

                $restaurant->setData($values);
                $restaurant->save();
                $this->success("Restaurant Daten wurden gespeichert");
            }
            else {
                $this->error($form->getMessages());
            }
        }
        return $this->_redirect('/restaurant_settings');
    }

    /**
     * add new opening time
     * @author alex
     */
    public function addopeningAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            setcookie("addedOpeningDay", $post['day'], time()+3600);
            setcookie("addedOpeningTimeFrom", substr($post['addFrom'], 0, 2) . ":" . substr($post['addFrom'], 2, 2) . ":00", time()+3600);
            setcookie("addedOpeningTimeUntil", substr($post['addUntil'], 0, 2) . ":" . substr($post['addUntil'], 2, 2) . ":00", time()+3600);
            
            $from = $post['addFrom'];
            $until = $post['addUntil'];

            if ($from > $until) {
                $this->error('Die Öffnungszeit kann nicht größer als Schließzeit sein!');
                return $this->_redirect('/restaurant_settings');
            }

            $req_from = intval($from/100);
            $req_until = intval($until/100);

            // check if this opening intersects with already available opening times on that day
            foreach ($restaurant->getRegularOpenings($post['day']) as $o) {
                $o_from     = intval(substr($o->from, 0, 2) . substr($o->from, 3, 2));
                $o_until    = intval(substr($o->until, 0, 2) . substr($o->until, 3, 2));

                if ( ( ($req_from <= $o_from) &&  ($req_until > $o_from)) ||
                        ( ($req_from < $o_until) &&  ($req_until > $o_until)) ||
                        ( ($req_from > $o_from) &&  ($req_until <= $o_until))
                    ) {
                    $this->error('Diese Öffnungszeit überschneidet sich mit einer anderen!');
                    return $this->_redirect('/restaurant_settings');
                }
            }

            $openingsObj = new Yourdelivery_Model_Servicetype_Openings($restaurant);
            $values = array (
                'day'=> intval($post['day']),
                'from'=> $from,
                'until'=> $until
            );
            $oid = $openingsObj->addNormalOpening($values);
            
            $this->logger->adminInfo(sprintf('Successfully added opening time %d for service %s (%s)',
                        $oid,
                        $restaurant->getName(),
                        $restaurant->getId()));

            return $this->_redirect('/restaurant_settings');
        }
    }

    /**
     * edit new opening time
     * @author alex
     */
    public function editopeningAction(){
        
        $restaurant = $this->initRestaurant();
        if ($restaurant->getId() === null) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->_redirect('/restaurant_settings');
        }
        $post = $request->getPost();

        $from = $post['from'];
        $until = $post['until'];
        if ($from > $until) {
            $this->error('Die Öffnungszeit kann nicht größer als Schließzeit sein!');
            return $this->_redirect('/restaurant_settings');
        }
        
        // check if this opening intersects with already available opening times on that day
        foreach ($restaurant->getOpeningsForDay($post['saveday']) as $o) {
            $o_from = str_replace(':', '', $o->from);
            $o_until = str_replace(':', '', $o->until);

            if ($o->id != $post['openingid']) {
                if ((($from <= $o_from) &&  ($until >= $o_from)) ||
                    (($from <= $o_until) &&  ($until >= $o_until)) ||
                    (($from >= $o_from) &&  ($until <= $o_until))) {
                    $this->error('Diese Öffnungszeit überschneidet sich mit einer anderen!');
                    return $this->_redirect('/restaurant_settings');
                }
            }
        }

        // for the case if the opening was deleted in another tab or by another admin while the older page is still open
        try {
            $openings = new Yourdelivery_Model_Servicetype_Openings(null, $post['openingid']);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
            $this->error('Die Öffnungszeit existiert nicht mehr!');
            return $this->_redirect('/restaurant_settings');
        }
        
        $openings->setData(array (
            'restaurantId'=> $restaurant->getId(),
            'day'=> intval($post['saveday']),
            'from'=> $from,
            'until'=> $until
        ));
        $openings->save();

        $this->logger->adminInfo(sprintf('Successfully edited opening time %d for service %s (%s)',
            $openings->getId(),
            $restaurant->getName(),
            $restaurant->getId()));

        return $this->_redirect('/restaurant_settings');
    }

    /**
     * edit restaurant tags
     * @author alex
     */
    public function edittagsAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            //remove alls tags so we can set the new tags
            $restaurant->removeAllTags();

            $tags = $post['tags'];

            if (is_array($tags)) {
                foreach ($tags as $tagId => $value) {
                    $restaurant->addTag($tagId);
                }
            }
        }

        $this->logger->adminInfo(sprintf('Successfully edited tags for service %s (%s)',
                    $restaurant->getName(),
                    $restaurant->getId()));

        return $this->_redirect('/restaurant_settings/#tags');
    }


    /**
     * delete opening time
     * @author alex
     */
    public function deleteopeningAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        $openingId = $request->getParam('id');

        if(!is_null($openingId)) {
            $restaurant->deleteOpening($openingId);
        }

        $this->logger->adminInfo(sprintf('Successfully deleted opening time for service %s (%s)',
                    $restaurant->getName(),
                    $restaurant->getId()));

        return $this->_redirect('/restaurant_settings');
    }

    /**
     * add new special opening time
     * @author alex
     */
    public function addspecialopeningAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            if (!is_null($post['spec_date']) && (strlen(trim($post['spec_date'])) > 0) ) {
                $date = $post['spec_date'];
                $dateFormatted = mktime(0, 0, 0, substr($date, 3, 2), substr($date, 0, 2), substr($date, 6));

                $from = $post['spectimeFrom'];
                $until = $post['spectimeUntil'];

                setcookie("addedSpecialOpeningTimeFrom", substr($post['spectimeFrom'], 0, 2) . ":" . substr($post['spectimeFrom'], 2, 2) . ":00", time()+3600);
                setcookie("addedSpecialOpeningTimeUntil", substr($post['spectimeUntil'], 0, 2) . ":" . substr($post['spectimeUntil'], 2, 2) . ":00", time()+3600);
                
                if ($from > $until) {
                    $this->error('Die Öffnungszeit kann nicht größer als Schließzeit sein!');
                    return $this->_redirect('/restaurant_settings');
                }

                // temp vars to compare new opening times wiht already available
                $req_from = intval($from/100);
                $req_until = intval($until/100);

                $openingsTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
                $testOpening = $openingsTable->getOpeningsAtDate($restaurant->getId(), $dateFormatted);

                // check if this opening intersects with already available opening times on that day or if the restaurant is closed on this day
                foreach ($testOpening as $to) {
                    if ($to['closed'] == 1) {
                        $this->error('An diesem Tag ist das Restaurant geschlossen!');
                        return $this->_redirect('/restaurant_settings');
                    }

                    if ( ($to['closed'] == 0) && ($post['closed']) ) {
                        $this->error('An diesem Tag sind bereits Öffnungszeiten eingetragen. Wenn das Restaurant an dem Tag doch geschlossen ist, löschen Sie zuerst alle Öffnungszeiten für den Tag!');
                        return $this->_redirect('/restaurant_settings');
                    }

                    $o_from     = intval(substr($to['from'], 0, 2) . substr($to['from'], 3, 2));
                    $o_until    = intval(substr($to['until'], 0, 2) . substr($to['until'], 3, 2));

                    if ( ( ($req_from <= $o_from) &&  ($req_until > $o_from)) ||
                            ( ($req_from < $o_until) &&  ($req_until > $o_until)) ||
                            ( ($req_from > $o_from) &&  ($req_until <= $o_until))
                        ) {
                        $this->error('Diese Öffnungszeit überschneidet sich mit einer anderen!');
                        return $this->_redirect('/restaurant_settings');
                    }
                }

                //restaurant is closed
                if ($post['closed'] == 1) {
                    $openings = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
                    $values = array (
                        'restaurantId'=> $restaurant->getId(),
                        'specialDate'=> substr($date, 6) . substr($date, 3, 2) . substr($date, 0, 2),
                        'closed'=> 1);

                    $openings->setData($values);
                    $openings->save();
                }
                //restaurant has special opening times
                else if (!is_null($post['spectimeFrom']) && !is_null($post['spectimeUntil'])) {
                    $openings = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
                    $values = array (
                        'restaurantId'=> $restaurant->getId(),
                        'specialDate'=> substr($date, 6) . substr($date, 3, 2) . substr($date, 0, 2),
                        'from'=> $post['spectimeFrom'],
                        'until'=> $post['spectimeUntil']);
                    
                    $openings->setData($values);
                    $openings->save();
                }

                $this->logger->adminInfo(sprintf('Successfully added special opening date %s for service %s (%s)',
                            $date,
                            $restaurant->getName(),
                            $restaurant->getId()));
            }
        }
        return $this->_redirect('/restaurant_settings');
    }


    /**
     * add vacancy
     * @author alex
     */
    public function addvacationAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $from = $post['vacation-from'];
            $until = $post['vacation-until'];

            $fromFormatted = mktime(0, 0, 0, substr($from, 3, 2), substr($from, 0, 2), substr($from, 6));
            $untilFormatted = mktime(0, 0, 0, substr($until, 3, 2), substr($until, 0, 2), substr($until, 6));

            if ($untilFormatted < $fromFormatted) {
                $this->error('Falsche Angabe des Zeitintervalls');
                $this->_redirect('/restaurant_settings');
            }

            $secondInDay = 60*60*24;
            $days = ($untilFormatted -$fromFormatted)/$secondInDay + 1;

            for ($index = 0; $index < $days; $index++) {
                $date = $fromFormatted + $index*60*60*24;
                $dateAsStr = date('Y-m-d', $date);

                $openingsTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
                $testOpenings = $openingsTable->getOpeningsAtDate($restaurant->getId(), $date);

                if (!is_null($testOpenings)) {
                    //remove all special time on this day
                    foreach ($testOpenings as $to) {
                        Yourdelivery_Model_DbTable_Restaurant_Openings_Special::remove($to['id']);
                        //if we want the entered special openings to stay
                        //$this->error("Für den Tag " . $dateAsStr . " wurde bereits spezielle Öffnungszeit eingetragen!");
                        //$errorDays++;
                    }
                }

                $opening = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
                $values = array (
                    'restaurantId'=> $restaurant->getId(),
                    'specialDate'=> $dateAsStr,
                    'closed'=> 1);
                $opening->setData($values);
                $opening->save();
            }

            $this->logger->adminInfo(sprintf('Successfully added vacation %s-%s for service %s (%s)',
                        $from,
                        $until,
                        $restaurant->getName(),
                        $restaurant->getId()));

            $this->success($days . ' Urlaubstage wurden hinzugefügt');

        }
        return $this->_redirect('/restaurant_settings');
    }
    
    /**
     * delete special opening time
     * @author alex
     */
    public function deletespecialopeningAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();

        $openingId = $request->getParam('id');
        if(!is_null($openingId)) {
            Yourdelivery_Model_DbTable_Restaurant_Openings_Special::remove($openingId);
        }

        $this->logger->adminInfo(sprintf('Successfully deleted special opening time for service %s (%s)',
                    $restaurant->getName(),
                    $restaurant->getId()));
        
        return $this->_redirect('/restaurant_settings');
    }

    /**
     * delete all special opening times for this restaurant
     * @author alex
     * @since 21.11.2011
     */
    public function deleteallspecialopeningsAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            Yourdelivery_Model_DbTable_Restaurant_Openings_Special::removeAll($restaurant->getId());
            
            $this->success('Alle spezielle Öffungszeiten wurden gelöscht!');
            $this->logger->adminInfo(sprintf('Successfully deleted all special opening times for service %s (%s)',
                        $restaurant->getName(),
                        $restaurant->getId()));
        }

        return $this->_redirect('/restaurant_settings');
    }
    
    /**
     * set offline status of the restaurant to "ready to check"
     * @author alex
     * @since 26.01.2011
     */
    public function editstatusAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        
        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            //create restaurant object
            try {
                $service = new Yourdelivery_Model_Servicetype_Restaurant($post['restaurantId']);
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                $this->error('Konnte Restaurant Objekt nicht erstellen!');
                return $this->_redirect('/restaurant_settings');
            }
            
            $oldStatus = $service->getStatus();
            
            if ($oldStatus == $post['status']) {
                return $this->_redirect('/restaurant_settings');
            }
            else {
                $service->setStatus($post['status']);
                $service->save();
                $this->success('Status wurde geändert!');
            }
            
            
            Yourdelivery_Model_DbTable_Restaurant_StatusHistory::logStatusChange((int)$post['status'], (int)$oldStatus);
            
            //if some reason for status change was given, write it to the restaurant notepad
            if (strlen(trim($post['offline-change-reason-text'])) > 0) {
                $madmin = $this->session->masterAdmin;
                $admin = $this->session->admin;
    
                if ( is_null($admin) && is_null($madmin) ) {
                    $this->error("Kein Admin wurde in der Sitzung gefunden, kann die Begründung für die Statusänderung nicht eintragen");
                    return $this->_redirect('/restaurant_settings');
                }
                else {
                    $offlineStati = Yourdelivery_Model_Servicetype_Abstract::getStati();
                    $newStatus = $offlineStati[$post['status']];
                    
                    $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                    
                    if (!is_null($madmin)) {
                        $comment->setMasterAdmin(1);
                        $comment->setAdminId($madmin->getId());
                    }
                    else {
                        $comment->setMasterAdmin(0);
                        $comment->setAdminId($admin->getId());
                    }

                    $comment->setRestaurantId($service->getId());
                    $comment->setComment("[offline status gesetzt: '" . $newStatus . "']. Begründung: " . trim($post['offline-change-reason-text']));
                    $comment->setTime(date("Y-m-d H:i:s", time()));
                    $comment->save();
                    $this->logger->adminInfo(sprintf('Status fürs Restaurant %d wurde geändert auf "%s"', $service->getId(), $newStatus));
                }
            }

            $this->success('Status wurde geändert!');
            return $this->_redirect('/restaurant_settings');
        }
    }
    
    /**
     * add new opening times for several days
     * @author Alex Vait 
     * @since 27.08.2012
     */
    public function addopeningsbatchAction(){
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            return $this->_redirect('/index');
        }

        $weekdayNames = Default_Helpers_Date::getDays();
        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            $from = $post['addFrom'];
            $until = $post['addUntil'];
            // this way we turn day 0 to 7, because our sundays are saved as 0 in the database
            
            $firstDay = (intval($post['firstDay']) + 6) % 7 + 1;
            $lastDay = (intval($post['lastDay']) + 6) % 7 + 1;
            
            if ($firstDay > $lastDay) {
                $this->error('Der erste Tag ist größer als der letzte Tag!');
                return $this->_redirect('/restaurant_settings');
            }
            
            if ($from > $until) {
                $this->error('Die Öffnungszeit kann nicht größer als Schließzeit sein!');
                return $this->_redirect('/restaurant_settings');
            }

            $errors = array();
            
            // iterate over all posted weekdays and set the opening
            for ($wd = $firstDay; $wd <= $lastDay; $wd++) {
                // convert sunday to 0 again 
                $weekday = $wd % 7;
                
                $req_from = intval($from/100);
                $req_until = intval($until/100);

                $conflicts = false;
                
                // check if this opening intersects with already saved opening times on that day                
                foreach ($restaurant->getRegularOpenings($weekday) as $o) {
                    $o_from     = intval(substr($o->from, 0, 2) . substr($o->from, 3, 2));
                    $o_until    = intval(substr($o->until, 0, 2) . substr($o->until, 3, 2));

                    if ( ( ($req_from <= $o_from) &&  ($req_until > $o_from)) ||
                            ( ($req_from < $o_until) &&  ($req_until > $o_until)) ||
                            ( ($req_from > $o_from) &&  ($req_until <= $o_until))
                        ) 
                        {                        
                            $errors[] = __b("Diese Öffnungszeit am %s wurde nicht gesetzt - sie überschneidet sich mit einer anderen Öffnungszeit", $weekdayNames[$weekday]);
                            $conflicts = true;
                    }
                }

                if (!$conflicts) {
                    $openingsObj = new Yourdelivery_Model_Servicetype_Openings($restaurant);
                    $values = array (
                        'day'=> intval($weekday),
                        'from'=> $from,
                        'until'=> $until
                    );
                    $oid = $openingsObj->addNormalOpening($values);

                    $this->logger->adminInfo(sprintf('Successfully added opening time %d for service %s (%s)',
                                $oid,
                                $restaurant->getName(),
                                $restaurant->getId()));                    
                }
            }

            foreach ($errors as $err) {
                $this->error($err);
            }
            
            return $this->_redirect('/restaurant_settings');
        }
    }    
}
?>
