<?php

function str_replace_assocs($string) {
    $replace = array(
        ' strasse' => 'str.',
        ' Str.' => 'str.',
        ' str.' => 'str.',
        ' Strasse' => 'str.',
        ' Straße' => 'str.',
        ' straße' => 'str.',
        'Strasse' => 'str.',
        'strasse' => 'str.',
        'Str.' => 'str.',
        'Straße' => 'str.',
        'straße' => 'str.',
        'ö' => 'oe',
        'ë' => 'ee',
        'ä' => 'ae',
        'ß' => 'ss',
        'ü' => 'ue',
        'Lieferservice' => ''
    );
    $from_array = array();
    $to_array = array();

    foreach ($replace as $k => $v) {
        $from_array[] = $k;
        $to_array[] = $v;
    }

    return str_replace($from_array, $to_array, $string);
}

function inToPercent($value) {
    if ($value == '' || $value == null) {
        $value = '-';
    }
    return '<font size="2"><b>' . $value . ' %</b></font>';
}

function checkFavourite($favourites, $kind, $mode, $id, $state, $rid, $cust = null, $rated = null, $rateable = null) {
    if (in_array($id, $favourites)) {
        $fav = '<a title="' . __('Favorit löschen') . '" href="#" class="td-star inactive  yd-del-fav  td-tooltip" id="yd-orders-fav-' . Default_Helpers_Crypt::hash($id) . '"></a>';
    } else {
        $fav = '<a title="' . __('Lieferservice als Favorit hinzufügen') . '" href="#" class="td-star  yd-add-fav  td-tooltip" id="yd-orders-fav-' . Default_Helpers_Crypt::hash($id) . '"></a>';
    }
    $fav .= '<a title="' . __('Bestellung wiederholen') . '" href="#" id="yd-repeat-order-' . Default_Helpers_Crypt::hash($id) . '" class="td-repeat yd-link-repeat-lastOrder yd-nowrap  td-tooltip"></a>';
    $fav .= '<a title="' . __('Bestellzettel ansehen') . '" href="/ordercoupon/' . Default_Helpers_Crypt::hash($id) . '" class="td-show yd-popup  td-tooltip"></a>';

    // check order is rated already
    if (is_object($cust)) {

        if (!$rated && $state > 0 && $rateable) {
            $fav .= '<a title="' . __('Bestellung bewerten') . '" href="/rate/' . Default_Helpers_Crypt::hash($id) . '" class="td-thumb cursor td-tooltip"></a>';
        } elseif ((!$rated && $state <= 0 ) || !$rateable) {
            $fav .= '<a title="' . __('Bestellung kann nicht bewertet werden') . '" href="" onclick="return false" class="td-thumb inactive-error cursor td-tooltip"></a>';
        } else {
            $fav .= '<a title="' . __('Bestellung wurde bewertet') . '" href="" onclick="return false" class="td-thumb inactive cursor td-tooltip"></a>';
        }
    }

    return $fav;
}

function checkKindMode($kind, $mode, $id, $rid, $budget) {
    if ($kind == "comp" && $mode == "rest") {
        if ($budget > 0) {
            $wiederholen = '<a href="/order/repeat/order/' . Default_Helpers_Crypt::hash($id) . '/" >«&nbsp;Bestellung&nbsp;wiederholen</a><br />';
        }
    } else {
        $wiederholen = '<a href="javascript:void(0);" id="yd-repeat-order-' . Default_Helpers_Crypt::hash($id) . '-' . $rid . '" class="yd-link-repeat-lastOrder">«&nbsp;Bestellung&nbsp;wiederholen</a><br />';
    }
    return $wiederholen;
}

function checkCostcenter($state) {
    if ($state != null) {
        return $state;
    } else {
        return 'k.A';
    }
}

//check meta tag for every satellite
function checkSatelliteMetaTag($meta1, $meta2, $meta3, $meta4, $meta5, $meta6) {
    $correct = array();
    $correctList = '';
    $errorList = '';
    $error = array();
    $meta['text'] = $meta1;
    $meta['title'] = $meta2;
    $meta['keywords'] = $meta3;
    $meta['robots'] = $meta4;
    $meta['logoAlt'] = $meta5;
    $meta['Satellite description'] = $meta6;
    foreach ($meta as $key => $data) {
        //check if the value of each meta tag is 0 / '' / null / ' '
        if ($data == '0' || $data == '' || $data = null || $data == ' ') {
            $error[] = $key;
            $errorList .= $key . ';';
        } else {
            $correct[] = $key;
            $correctList .= $key . ';';
        }
    }
    $countError = count($error);
    $countCorrect = count($correct);
    //if there is error / invalid meta tag, it will show cross logo otherwise, show check.
    if ($countError == 0) {
        return "<img src='/media/images/yd-backend/grid/certo.png' alt='komplett' /></a>";
    } else {
        return "<img src='/media/images/yd-icons/cross.png' alt='" . $errorList . "' /></a>";
    }
}

//check several field on satellite overview
function checkSatellite($field, $value) {
    //check description column
    if ($field == 'Description') {
        if ($value == '' || $value == null || $value == ' ' || $value == '0') {

            return "<img src='/media/images/yd-icons/cross.png' alt='nicht komplett' /></a>";
        } else {

            return $value;
        }
    }
    //check logo column
    else if ($field == 'Logo') {
        if ($value == '' || $value == null) {
            $error = 1;
        } else {
            $error = 0;
        }
    }
    //check satellite description
    else if ($field == 'Sat.Descr') {
        if ($value == '' || $value == null || $value == '0') {
            $error = 1;
        } else {
            $error = 0;
        }
    }
    //check online status
    else if ($field == 'Online') {
        if ($value == '0') {
            $error = 0;
        } else {
            $error = 1;
        }
    }
    //if there is an error than show croos logo
    if ($error == 1) {
        return "<img src='/media/images/yd-icons/cross.png' alt='nicht komplett' /></a>";
    } else if ($error == 0) {
        return "<img src='/media/images/yd-backend/grid/certo.png' alt='komplett' /></a>";
    }
}

/*
 * check bilder kategorie for each satellite
 */

function checkBilderKategorie($restaurantId) {
    $error = array();
    $errorList = '';
    $correct = array();
    $correctList = '';

    $restaurantTable = new Yourdelivery_Model_DbTable_Restaurant();
    $restaurantData = $restaurantTable->checkBilderKategorie($restaurantId);

    foreach ($restaurantData as $data) {
        //check if each categoryPictureId been fullfilled
        if ($data['categoryPictureId'] == null || $data['categoryPictureId'] == '' || $data['categoryPictureId'] == '0') {
            $error[] = $data['id'];
            $errorList .= $data['name'] . ';';
        } else {
            $correct[] = $data['id'];
            $correctList .= $data['name'] . ';';
        }
    }
    if (count($error) != 0) {
        return "<img src='/media/images/yd-icons/cross.png' alt='" . $errorList . "' /></a>";
    } else if (count($error) == 0) {
        return "<img src='/media/images/yd-backend/grid/certo.png' alt='Komplett' /></a>";
    }
}

/**
 * @param int $value
 * @param string $link
 * @return converted status as icon
 */
function convertStatusLink($value, $link) {
    if ($value == '0') {
        $status = '<img src="/media/images/yd-backend/grid/certo.png" alt="OK" />';
    } else if ($value == '1') {
        $status = '<a href="' . $link . '"><img src="/media/images/yd-backend/grid/fechar.png" alt="NOT OK" /></a>';
    } else if ($value == '-1') {
        $status = '<a href="' . $link . '"><img src="/media/images/yd-icons/icon-info.png" alt="Invalid" /></a>';
    }
    return $status;
}

/*
 * drop down list for billing status
 */

function billingStatusDropdown($id, $mode, $path) {
    $billing = new Yourdelivery_Model_Billing($id);
    if ($billing == null) {
        return "";
    }

    $status = $billing->getStatus();
    $form = "
        <form method=\"post\" action=\"/administration_billing/changestatus\" onChange=\"this.submit();\">
            <select name=\"status\" id=\"yd-bill-status-" . $id . "\">";

    foreach (Yourdelivery_Model_Billing_Abstract::getStatusse() as $ind => $value) {
        $form = $form . "<option " . (($status == $ind) ? "selected" : "") .
                " value=\"" . $ind . "\">" . $value . "</option>";
    }

    $form = $form . "
            </select>
            <input type=\"hidden\" name=\"id\" value=\"" . $id . "\"/>
            <input type=\"hidden\" name=\"mode\" value=\"" . $mode . "\"/>
            <input type=\"hidden\" name=\"path\" value=\"" . $path . "\"/>
        </form>";

    $statis = Yourdelivery_Model_Billing_Abstract::getStatusse();
    $stateHistory = $billing->getStateHistory();
    
    if (count($stateHistory)>0) {
        $form = $form . "<table>";

        foreach ($stateHistory as $sh) {
            $form .= "<tr><td colspan='2'>" . __b("Geändert von ") . $sh['name'] . " auf:</td></tr>";
            $form .= "<tr>";
            $form .= "<td>" . $statis[$sh['status']] . "</td>";
            $form .= "<td>" . date('d.m.Y H:i:s', strtotime($sh['created'])) . "<br/></td>";
            $form .= "</tr>";
            $form .= "<tr><td colspan='2'></td></tr>";
        }
        
        $form = $form . "</table>";
    }
    
    
    
    return $form;
}

/**
 * @author alex
 * @since 16.11.2010
 * drop down list for billing assets status
 */
function billingassetStatusDropdown($id, $path) {
    $billingAsset = new Yourdelivery_Model_BillingAsset($id);
    if ($billingAsset == null) {
        return "";
    }

    $status = $billingAsset->getStatus();
    $form = "
        <form method=\"post\" action=\"/administration_billingasset/changestatus\" onChange=\"this.submit();\">
            <select name=\"status\" id=\"yd-bill-status-" . $id . "\">
                <option " . (($status == 0) ? "selected" : "") .
            " value=\"0\">Unbezahlt</option>
                <option " . (($status == 1) ? "selected" : "") .
            " value=\"1\">Bezahlt</option>
            </select>
            <input type=\"hidden\" name=\"id\" value=\"" . $id . "\"/>
            <input type=\"hidden\" name=\"path\" value=\"" . $path . "\"/>
        </form>";

    return $form;
}

/*
 * telephone umber of customer and restaurant for this order
 */

function telephonesForOrder($orderId) {
    $order = new Yourdelivery_Model_Order($orderId);
    $result = "<small>";
    $result .= "<span class=\"tooltip\" title=\"Kunde\">" . $order->getLocation()->getTel() . "</span><br/>";

    $restaurant = null;
    try {
        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($order->getRestaurantId());
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return null;
    }

    $result .= "<span class=\"tooltip\" title=\"Dienstleister\">" . $restaurant->getTel() . "</span>";
    $result .= "</small>";

    return $result;
}

/**
 * show restaurant rating as 5-star image
 * @author alex
 * @since 18.11.2010
 */
function ratingsToImg($rating, $restaurantId = null) {

    $return = "";

    switch ($rating) {
        case 0.5:
            $return = "<span class='yd-rated-01'></span>";
            break;

        case 1:
            $return = "<span class='yd-rated-02'></span>";
            break;

        case 1.5:
            $return = "<span class='yd-rated-03'></span>";
            break;

        case 2:
            $return = "<span class='yd-rated-04'></span>";
            break;

        case 2.5:
            $return = "<span class='yd-rated-05'></span>";
            break;

        case 3:
            $return = "<span class='yd-rated-06'></span>";
            break;

        case 3.5:
            $return = "<span class='yd-rated-07'></span>";
            break;

        case 4:
            $return = "<span class='yd-rated-08'></span>";
            break;

        case 4.5:
            $return = "<span class='yd-rated-09'></span>";
            break;

        case 5:
            $return = "<span class='yd-rated-10'></span>";
            break;
    }

    if ($restaurantId) {
        $return = "<a href='/administration_service_ratings/index/RestaurantIdratings/" . $restaurantId . "'>" . $return . "</a>";
    }
    return $return;
}

/**
 * show restaurant status rating as image and assign id to the element
 * @author alex
 * @since 18.11.2010
 */
function ratingStatusToImg($ratingId, $status) {
    if ($status == -1) {
        return '
            <div id="yd-rating-container-' . $ratingId . '">
                <a href="#x" id="yd-rating-undelete-' . $ratingId . '" class="yd-rating-undelete"><img src="/media/images/yd-backend/online_status_deleted.png"/ alt="'.__b('Löschen rückgängig machen').'"></a>
            </div>';
    }
    return '
        <div id="yd-rating-container-' . $ratingId . '">
            <a href="#x" id="yd-rating-togglestatus-' . $ratingId . '" class="yd-rating-togglestatus"><img src="/media/images/yd-backend/online_status_' . $status. '.png"/ alt="'.__b('Status ändern').'"></a>
                &nbsp;
            <a href="#x" id="yd-rating-delete-' . $ratingId . '" class="yd-rating-delete"><img src="/media/images/yd-backend/del-cat.gif"/ alt="' . __b("Löschen") . '"></a>
        </div>';
}

/**
 * show restaurant status rating as image and assign id to the element
 * @author alex
 * @since 18.11.2010
 */
function ratingTopToImg($ratingId, $status) {
    return '<a href="#x" id="yd-rating-top-togglestatus-' . $ratingId . '" class="yd-rating-top-togglestatus"><img src="/media/images/yd-backend/online_status_' . $status . '.png"/ alt=""></a>';
}

/**
 * show restaurant advise as thumb up/down image
 * @author alex
 * @since 18.11.2010
 * @param int $advise
 * @param int $ratingId
 * @return string
 */
function adviseToImg($advise, $ratingId) {

    $html = "";
    switch ($advise) {
        case 0:
            $html .= '<img src="/media/images/yd-icons/yd-ratings-thumb-down.png">';
            break;

        case 1:
            $html .= '<img src="/media/images/yd-icons/yd-ratings-thumb.png">';
            break;
    }

    return $html;
}

/**
 * @author Vincent Priem
 * @since 19.04.2012
 * @param int $ratingId
 * @param int $crmEmail
 * @return string
 */
function crmEmailLink($ratingId, $crmEmail) {

    return '<a href="/administration_request_service_rating/sorry/id/' . $ratingId . '" class="yd-rating-sorry" style="' . ($crmEmail ? 'text-decoration:line-through' : "") . '">' . __b("Sorry") . '</a>';
}

/**
 * link to popup wiht order in html format
 * @author alex
 * @since 18.11.2010
 */
function orderPopupLink($orderId) {
    return '<a href="#" title="Bestellzettel als Html angucken" onclick="popup(\'/order/bestellzettel/order/' . $orderId . '\', \'Bestellzettel\', 800, 600); return false;">' . $orderId . '</a>';
}

/**
 * check if this state is -2 and add a note
 * to this order. used in orders grid of user backend
 * @author mlaug
 * @since 25.01.2011
 * @param integer $state
 * @param string $nr
 * @return string
 */
function checkForStorno($state, $nr) {

    if ($state == -2) {
        return $nr . "<br /><small style='color:red'>" . __("Bestellung storniert") . "</small>";
    }
    return $nr;
}

// show link to the billing admonation file if it exists
function fileForBillingAdmonation($number) {
    $filename = str_replace('R', 'M', $number) . '.pdf';

    $path = APPLICATION_PATH . "/../storage/billing/" . $number . "/" . $filename;
    if (file_exists($path)) {
        return "<a href=\"" . $path . "\">pdf</a>";
    }
}

/**
 * check how much data of provided fields is filled, in %
 * @author alex
 * @since 27.04.2011
 * @param array
 * @return string
 */
function dataComplete() {
    $args = func_get_args();
    $i = 0;
    $str = "";
    foreach ($args as $arg) {
        if (strlen($arg) > 0) {
            $str .= $arg . '*';
            $i++;
        }
    }
    return round(($i / count($args) * 100), 2) . "%";
}

/**
 * get types of this meal
 * @author alex
 * @since 06.07.2011
 */
function getMealTypes($id) {

    try {
        $meal = new Yourdelivery_Model_Meals($id);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return '';
    }

    $typesList = "";

    foreach ($meal->getTypes() as $ind => $type) {
        if ($ind > 0) {
            $typesList .= "<br/>";
        }

        $typeNamePath = '';
        $pt = $type->getParent();
        $prefix = '&nbsp;';
        $hierarchy = array(0 => $type->getName());

        while (!is_null($pt)) {
            $hierarchy[] = $pt->getName();
            $pt = $pt->getParent();
        }

        foreach (array_reverse($hierarchy) as $t) {
            $typeNamePath = $typeNamePath . '<br/>' . $prefix . $t;
            $prefix = $prefix . $prefix;
        }

        $typesList .= '<div class="tooltip" title="' . $typeNamePath . ' ">' . $type->getName() . ' </div>';
    }
    return $typesList;
}

/**
 * get igredients of this meal
 * @author alex
 * @since 21.07.2011
 */
function getMealIngredients($id) {
    try {
        $meal = new Yourdelivery_Model_Meals($id);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return '';
    }

    $ingredientsList = "";

    foreach ($meal->getIngredients() as $ind => $ingredient) {
        if ($ind > 0) {
            $ingredientsList .= ", ";
        }
        $ingredientsList .= $ingredient;
    }
    return $ingredientsList;
}

/**
 * link to crm subject depending on crm subject type
 * @author alex
 * @since 27.06.2011
 * @param $type
 * @param $refId
 * @return string - link to corresponding edit page
 */
function crmReferenceLink($type, $refId) {
    switch ($type) {
        default:
            return __('unbekannt');
        case 'service':
            try {
                $service = new Yourdelivery_Model_Servicetype_Restaurant($refId);
                $link = sprintf("Dienstleister:<br/><a href='/administration_service_edit/index/id/%d'>%s</a>", $service->getId(), $service->getName());
                return $link;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return 'Error';
            }
        case 'company':
            try {
                $company = new Yourdelivery_Model_Company($refId);
                $link = sprintf("Firma:<br/><a href='/administration_company_edit/index/companyid/%d'>%s</a>", $company->getId(), $company->getName());
                return $link;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return 'Error';
            }
        case 'customer':
            try {
                $user = new Yourdelivery_Model_Customer($refId);
                $link = sprintf("Benutzer:<br/><a href='/administration_user_edit/index/userid/%d'>%s %s</a>", $user->getId(), $user->getName(), $user->getPrename());
                return $link;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return 'Error';
            }
    }
}

/**
 * crm ticket reason in readable format
 * @author alex
 * @since 06.07.2011
 */
function crmReasonToReadable($reasonId) {
    return Yourdelivery_Model_Crm_Ticket::getReasonAsText($reasonId);
}

/**
 * show crm icon depending on stastus and time remained until ticket is scheduled
 * @author alex
 * @since 13.07.2011
 */
function intCrmTicketScheduledIcon($closed, $scheduled) {
    if (strlen($scheduled) == 0)
        return '';

    if ($closed == 1) {
        return $scheduled . '  <img src="/media/images/yd-backend/crm-grey.png">';
    } else {
        $timeDiffHours = (strtotime($scheduled) - time()) / (60 * 60);

        if (($timeDiffHours < 0) && ($timeDiffHours < -12)) {
            return $scheduled . '  <img src="/media/images/yd-backend/crm-black.png">';
        } else if ($timeDiffHours < 0) {
            return $scheduled . '  <img src="/media/images/yd-backend/crm-red.png">';
        } else if ($timeDiffHours < 24) {
            return $scheduled . '  <img src="/media/images/yd-backend/crm-orange.png">';
        } else {
            return $scheduled . '  <img src="/media/images/yd-backend/crm-yellow.png">';
        }
    }
}

/**
 * crm closed/open ticket status
 * @author alex
 * @since 13.07.2011
 * @return string
 */
function crmOpenClosed($isClosed) {
    switch ($isClosed) {
        default:
            return __b('unbekannt');
        case 0:
            return __b('offen');
        case 1:
            return __b('geschlossen');
    }
}

/**
 * Show edit links fürs bearbeiten der Rabattcodes
 * @author alex
 * @since 09.08.2011
 */
function discountcodeedit($id, $used, $dailydeal) {

    if ( !is_null($id) &&
            ( ($used == 0) && (strcmp($dailydeal, 'fake') !=0)  && (strcmp($dailydeal, 'daktiviert') !=0))
        ) {
        return "<a href=\"javascript:void(0)\" class=\"yd-resend-discount-code\" id=\"yd-resend-discount-code-" . $id . "\">".__b('verschicken')."/a>";
    }
}

/**
 * List fo all restaurants, associated with this printer
 * @author vpriem
 * @since 17.11.2011
 * @param int $printerId
 * @return string
 */
function restaurantsForPrinter($printerId) {
    $printerId = (integer) $printerId;
    if (!$printerId) {
        return "";
    }

    try {
        $printer =Yourdelivery_Model_Printer_Abstract::factory ($printerId);
    }
    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return "";
    }

    $html = array();
    $restaurants = $printer->getRestaurants();
    foreach ($restaurants as $restaurant) {
        $html[] = '<a href="/administration_service_edit/index/id/' . $restaurant->getId() . '">' . $restaurant->getName() . '</a>';
        }
    return implode("<br />", $html);
}

/**
 * List fo all restaurants, associated with this printer, including openings for current day
 *
 * @author Marek Hejduk <m.hejduk@pyszne.pl>
 * @since 28.05.2012
 *
 * @param int $printerId
 * @return string
 */
function restaurantsForPrinterWithOpenings($printerId) {
    $printerId = (integer) $printerId;
    if (!$printerId) {
        return "";
    }

    try {
        $printer = Yourdelivery_Model_Printer_Abstract::factory ($printerId);
    }
    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return "";
    }

    $html = array();
    $day = date('w');
    $restaurants = $printer->getRestaurants();
    foreach ($restaurants as $restaurant) {
        $openings = array();
        foreach ($restaurant->getOpeningsForDay($day) as $opening) {
            $openings[] = sprintf('%s - %s', substr($opening->from, 0, -3), substr($opening->until, 0, -3));
        }
        $html[] = '<a href="/administration_service_edit/index/id/' . $restaurant->getId() . '">' . $restaurant->getName() . '</a>' .
            ((empty($openings))? '': (' <small>[' . implode(', ', $openings) . ']</small>'));
    }
    return implode("<br />", $html);
}

/**
 * restaurant backend : get meals from this category
 * @author alex
 * @since 09.11.2011
 */
function getMealsForCategory($id) {
    try {
        $category = new Yourdelivery_Model_Meal_Category($id);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return;
    }

    foreach ($category->getMeals() as $meal) {
        if ( ($meal['deleted']==0) && ($meal['status']==1) ) {            
            $mealsList .= $meal['name'] . "<br/>";
        }
    }
    return $mealsList;
}

/**
 * Admin backend. Get type of discount action as full text
 * @author Alex Vait <vait@lieferando.de>
 * @since 17.01.2012
 */
function getDiscountType($typeId) {
    $allTypes = Yourdelivery_Model_Rabatt::getDiscountTypes();
    $type = $allTypes[$typeId];
    return '<div class="tooltip" title="' . $type['description'] . '">' . $type['name'] . '</div>';
}

/**
 * Admin backend. Get options of this discount action - link to the codes, lightbox with all codes or verification codes
 * @author Alex Vait <vait@lieferando.de>
 * @since 17.01.2012
 */
function getDiscountOptions($discountId, $typeId) {
    $html = '<a href="/administration_discount/edit/id/' . $discountId . '">' . __b('Editieren') . '</a>';

    switch ($typeId) {
        default:
            break;
        case 0:
             $html .=
            sprintf('<a href="javascript:void(0)" class="yd-show-discount-codes" id="yd-show-discount-codes-%s">' . __b('Gutscheincodes anzeigen') . '</a>', $discountId);
            if(Yourdelivery_Model_DbTable_RabattCodes::getCodesCount($discountId)<=10000){
               $html .= sprintf('<a href="/administration_discount/downloadcodes/id/%s" class="yd-download-discount-codes" id="yd-download-discount-codes-%s">' . __b('Download') . '</a>', $discountId, $discountId);
            }
            break;
        case 1:
            break;
        case 2:
             $html .=
             sprintf('<a href="javascript:void(0)" class="yd-show-verification-codes" id="yd-show-verification-codes-%s">' . __b('Registrierungscodes anzeigen') . '</a>', $discountId);
             if(Yourdelivery_Model_DbTable_RabattCodesVerification::getCodesCount($discountId)<=10000){
                 $html .= sprintf('<a href="/administration_discount/downloadcodes/id/%s" class="yd-download-verification-codes" id="yd-download-verification-codes-%s">' . __b('Download') . '</a>', $discountId, $discountId);
             }
            break;
        case 3:
            try {
                $discount = new Yourdelivery_Model_Rabatt($discountId);
                $codes = Yourdelivery_Model_DbTable_RabattCodesVerification::findByRabattId($discount->getId());
                $fakeCode = $codes[0];
                $html .= __b('Code: <b>%s</b>', $fakeCode['registrationCode']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
            break;
        case 4:
             $html .=
            sprintf('<a href="javascript:void(0)" class="yd-show-discount-codes" id="yd-show-discount-codes-%s">' . __b('Gutscheincodes anzeigen') . '</a>', $discountId);
            if(Yourdelivery_Model_DbTable_RabattCodes::getCodesCount($discountId)<=10000){
               $html .= sprintf('<a href="/administration_discount/downloadcodes/id/%s" class="yd-download-discount-codes" id="yd-download-discount-codes-%s">' . __b('Download') . '</a>', $discountId, $discountId);
            }
            break;
        case 5:
             $html .=
            sprintf('<a href="javascript:void(0)" class="yd-show-discount-codes" id="yd-show-discount-codes-%s">' . __b('Gutscheincodes anzeigen') . '</a>', $discountId);
            if(Yourdelivery_Model_DbTable_RabattCodes::getCodesCount($discountId)<=10000){
               $html .= sprintf('<a href="/administration_discount/downloadcodes/id/%s" class="yd-download-discount-codes" id="yd-download-discount-codes-%s">' . __b('Download') . '</a>', $discountId, $discountId);
            }
            break;
        case 6:
        case 7:
            try {
                $discount = new Yourdelivery_Model_Rabatt($discountId);
                $code = Yourdelivery_Model_DbTable_RabattCodes::findByRabattId($discount->getId());

                $html .= __b('Code: <b>%s</b>', $code['code']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
            break;                
    }
    
    $html .= '<a href="/administration_discount/checkdiscount?rabattId=' . $discountId . '" target="_blank">' . __b('Alle Bestellungen anzeigen') . '</a>';
    
    return $html;
}

/**
 * if this discount action of type 1, don't show the referer
 * @author alex
 * @since 02.02.2012
 */
function hideRefererTypeOne($discountType, $referer) {
    if (($discountType == Yourdelivery_Model_Rabatt::TYPE_REGULAR) ||
            ($discountType == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ONCE_PER_THIS_TYPE) ||
            ($discountType == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ACTION)) {
        return "";
    }
    return $referer;
}

/**
 * Show if gprs printer is online or offline
 * @author Alex Vait <vait@lieferando.de>
 * @since 28.03.2012
 */
function printerIsOnline($isOnline) {
    if ($isOnline) {
        return '<font color="#008000">' . __b('Online') . '</font>';
    }

    return '<font color="#FF0000"><b>' . __b('Offline') . '<b></font>';
}

/**
 * Show if gprs printer is online or offline
 * @author Alex Vait <vait@lieferando.de>
 * @since 28.03.2012
 */
function printerSignal($signal) {
    return round(($signal / 31) * 100) . ' %';
}

/**
 * Translate the notification kind of restaurant
 * @author Alex Vait <vait@lieferando.de>
 * @since 28.03.2012
 */
function translateNotificationKind($kind) {
    $kinds = Yourdelivery_Model_Servicetype_Abstract::getNotificationKinds();
    return $kinds[$kind];
}
