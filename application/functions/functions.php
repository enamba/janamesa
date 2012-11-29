<?php

        const FRANCHISE_TYPE_NORMAL = 1;
        const FRANCHISE_TYPE_NOCONTRACT = 2;
        const FRANCHISE_TYPE_PREMIUM = 3;
        const FRANCHISE_TYPE_BUTLER = 4;
        const FRANCHISE_TYPE_BLOOMBURYS = 5;
        const FRANCHISE_TYPE_EATSTAR = 6;

function onlyDev() {
    if (APPLICATION_ENV != "development") {
        die('In der Entwicklung');
    }
}

// €€,cc => cccc
function priceToInt($price) {
    $p = explode(',', $price);
    if (!isset($p[1])) {
        return ($p[0] * 100);
    }
    return ($p[0] * 100 + $p[1] < 0) ? 0 : $p[0] * 100 + $p[1];
}

function makeRelative($file) {
    $rel = null;

    if (strstr($file, 'media')) {
        $parts = explode('media', $file);
        $rel = '/media' . $parts[1];
    }

    if (strstr($file, 'storage')) {
        $parts = explode('storage', $file);
        $rel = '/storage' . $parts[1];
    }

    return $rel;
}

// €€,cc => cccc with zero padding
function priceToInt2($price) {
    if (strcmp($price, "0") == 0) {
        return 0;
    }

    if ((strrpos($price, ',') !== false)) {
        $declen = strlen(substr($price, strrpos($price, ',') + 1));
        //less than two digits after decimal point
        if ($declen < 2) {
            $price = str_pad($price, strlen($price) + (2 - $declen), '0');
        }
    }

    $p = explode(',', $price);

    if (isset($p[1])) {
        // avoid entering to much numbers after period
        $p[1] = substr($p[1], 0, 2);
    }

    if (!isset($p[1])) {
        return ($p[0] * 100);
    }

    if ($price >= 0) {
        return $p[0] * 100 + $p[1];
    } else {
        return $p[0] * 100 - $p[1];
    }
}

// €€,cc <= cccc
function intToPrice($price, $precision = 2, $seperator = ",") {

    //first check for zero value
    $check = floatval($price);
    if ($check < 0) {
        return '0,00';
    }

    //calulcate based on precision
    $price = floatval($price);
    $prec = pow(10, $precision) / 100;
    $euro = intval(floor($price / 100));
    $val = round(floatval(fmod($price, 100) * $prec));
    $cent = sprintf("%0$precision.0f", $val);

    //no cents
    if ($cent == 0) {
        $cent = "00";
    }

    //if precision is 2 and we reach 100 on rounding up, we decide to raise
    //an euro and set cent to 00
    if ($cent == pow(10, $precision)) {
        $euro++;
        $cent = "00";
    }

    return $euro . $seperator . $cent;
}

// €€,cc <= cccc
function intToPriceWithNegative($price, $precision = 2, $seperator = ",") {
    //first check for negative value
    $check = floatval($price);

    $negative = false;
    if ($price < 0) {
        $negative = true;
    }

    //calulcate based on precision
    $price = floatval($price);
    $prec = pow(10, $precision) / 100;
    if ($check < 0) {
        $euro = abs(intval(ceil($price / 100)));
    } else {
        $euro = abs(intval(floor($price / 100)));
    }

    $val = abs(round(floatval(fmod($price, 100) * $prec)));
    $cent = sprintf("%0$precision.0f", $val);

    //no cents
    if ($cent == 0) {
        $cent = "00";
    }

    if ($cent < 0) {
        $cent = -1 * $cent;
    }

    //if precision is 2 and we reach 100 on rounding up, we decide to raise
    //an euro and set cent to 00
    if ($cent == pow(10, $precision)) {
        $euro++;
        $cent = "00";
    }

    return ($negative ? "-" : "") . $euro . $seperator . $cent;
}

//replace xml chars with corresponding codes
function parseToXML($htmlStr) {
    $htmlStr = decode($htmlStr);
    $xmlStr = str_replace('<', '&lt;', $htmlStr);
    $xmlStr = str_replace('>', '&gt;', $xmlStr);
    $xmlStr = str_replace('"', '&quot;', $xmlStr);
    $xmlStr = str_replace("'", '&#39;', $xmlStr);
    $xmlStr = str_replace("&", '&amp;', $xmlStr);
    return $xmlStr;
}

function decode($value) {
    return $value;
}

function optionsForCompanies($id) {
    if ($id === null) {
        return null;
    }
    $comp = null;
    try {
        $comp = new Yourdelivery_Model_Company($id);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return null;
    }
    $admins = $comp->getAdmins();

    if ($admins->count() > 0) {
        return
                "<a href=\"/administration_company_edit/index/companyid/" . $id . "\">" . __b('Info') . "</a>
         <a href=\"/administration/companylogin/id/" . $id . "\" target=\"_blank\">" . __b('Login') . "</a>
         <a href=\"/administration_company/delete/id/" . $id . "\" onclick=\"javascript:return confirm('" . __b('Vorsicht!! Soll diese Firma wirklich gel&ouml;scht werden?') . "')\">" . __b('Löschen') . "</a>";
    } else {
        return
                "<i>" . __b('kein Admin') . "</i>
         <a href=\"/administration_company_edit/index/companyid/" . $id . "\">" . __b('Info') . "</a>
         <a href=\"/administration_company/delete/id/" . $id . "\" onclick=\"javascript:return confirm('" . __b('Vorsicht!! Soll diese Firma wirklich gel&ouml;scht werden?') . "')\">" . __b('Löschen') . "</a>";
    }
}

// readable time format
function niceTime($time) {
    $now = time();
    $then = $time;
    $diff = $now - $then;
    if ($diff > 0) {
        if ($diff < 3600) {
            return "Seit " . floor($diff / 60) . " Minuten";
        } elseif ($diff < (3600 * 3)) {
            return "Seit " . floor($diff / 3600) . " Stunden und " . floor(($diff % 3600) / 60) . " Minuten";
        } elseif (date("d", $now) == date("d", $then) && $diff < (3600 * 24)) {
            return "Seit " . date("H:i", $then);
        } else {
            return "Seit dem " . date("d.m.Y - H:i", $then);
        }
    } else {
        $diff = -$diff;
        if ($diff < 3600) {
            return "In " . floor($diff / 60) . " Minuten";
        } elseif ($diff < (3600 * 3)) {
            return "In " . floor($diff / 3600) . " Stunden und " . floor(($diff % 3600) / 60) . " Minuten";
        } elseif (date("d", $now) == date("d", $then) && $diff < (3600 * 24)) {
            return "Um " . date("H:i", $then);
        } else {
            return "Am " . date("d.m.Y - H:i", $then);
        }
    }
}

//sql-date as day.month.year hour:minutes
function dateFull($time) {
    if (is_null($time) || (strlen(trim($time)) == 0)) {
        return __b("unbekannt");
    }
    return substr($time, 8, 2) . "." . substr($time, 5, 2) . "." . substr($time, 0, 4) . " " . substr($time, 11, 2) . ":" . substr($time, 14, 2) . ":" . substr($time, 17, 2);
}

//date as day.month.year
function dateYMD($time) {
    if (is_null($time) || (strlen(trim($time)) == 0)) {
        return __b("unbekannt");
    }
    return substr($time, 8, 2) . "." . substr($time, 5, 2) . "." . substr($time, 0, 4);
}

//date as hour:minutes
function dateHi($time) {
    if ($time == 0) {
        return __b("unbekannt");
    }
    return substr($time, 11, 2) . ":" . substr($time, 14, 2);
}

//timestampe as day.month.year hour:minutes
function timestampFull($time) {
    if (is_null($time) || (strlen(trim($time)) == 0)) {
        return __b("unbekannt");
    }
    return date("d.m.Y H:i:s", $time);
}

//date as day.month.year
function timestampYMD($time) {
    if (is_null($time) || (strlen(trim($time)) == 0)) {
        return __b("unbekannt");
    }
    return date("d.m.Y", $time);
}

//get daytime rounded to 15 minutes
function timestampRounded($time, $separator = ":") {
    $minutes = intval(date('i', $time));
    $min = $minutes - ($minutes % 15);

    return date("H", $time) . $separator . ($min == 0 ? '00' : $min);
}

//date as hour:minutes
function timestampHi($time) {
    if ($time == 0) {
        return __b("unbekannt");
    }
    return date("H:i", $time);
}

//sql date as day.month.year
function sqlTimeToDMY($time) {
    if ($time == 0) {
        return __b('unbekannt');
    }
    return substr($time, 8, 2) . "." . substr($time, 5, 2) . "." . substr($time, 2, 2);
}

//sql date as hour:minutes
function sqlTimeToHi($time) {
    if ($time == 0) {
        return __b('unbekannt');
    }
    return substr($time, 11, 5);
}

// calculate distance between two lon/lat coordinates
function distance($lat1, $lon1, $lat2, $lon2) {
    $quad1 = pow(($lat1 - $lat2) * 111.1338401, 2);
    $quad2 = pow(cos($lat1 - $lat2) * ($lon1 - $lon2) * 110.1338401, 2);
    return round(sqrt($quad1 + $quad2) * 1000);
}

/**
 * check if function exists
 * since php 5.3 this method exists
 */
if (!function_exists('lcfirst')) {

    function lcfirst($str) {
        $newString = array();
        $words = explode(" ", $str);

        foreach ($words as $word) {
            for ($i = 0; $i < strlen($word); $i++) {
                if (preg_match("'\w'", $word[$i])) {
                    $word[$i] = strtolower($word[$i]);
                    break;
                }
            }
            $newString[] = $word;
        }
        return implode(" ", $newString);
    }

}

// state in readable format
function intToStatusOrders($state, $mode = null) {
    return Default_Helpers_Human_Readable_Backend::intToOrderStatus($state, $mode);
}

// discount state in readable format
function intToStatusDiscount($state) {
    switch ($state) {
        default: return __b('unbekannt');
        case 0: return __b('Deaktiviert');
        case 1: return __b('Aktiviert');
    }
}

// company state in readable format with colors
function companyStatusToReaddable($state) {
    switch ($state) {
        default: return '<font color="#333333">' . __b('unbekannt') . '</font>';
        case 1: return '<font color="#339933">' . __b('aktiviert') . '</font>';
        case 0: return '<font color="#993333">' . __b('deaktiviert') . '</font>';
    }
}

// billing state in readable format
function billstatusToReadable($state) {
    switch ($state) {
        default: return __b('unbekannt');
        case 0: return __b('Nicht versand');
        case 1: return __b('Unbezahlt');
        case 2: return __b('Bezahlt');
        case 3: return __b('Teilbezahlt');
        case 4: return __b('Storno');
    }
}

// service type in readable format
function typeToReadable($state) {
    switch ($state) {
        default: return __('k.A');
        case 'rest': return __('Restaurant');
        case 'cater': return __('Catering');
        case 'great': return __('Großhandel');
        case 'fruit': return __('Obsthändler');
    }
}

/**
 * military time in readable format
 * @author alex
 * @since 02.09.2010
 */
function militaryTimeToReadable($time) {
    return substr($time, 0, 2) . ':' . substr($time, 2, 2);
}

/**
 * link for rabatt code lightbox
 * @author alex
 * @since 28.09.2010
 */
function rabattcodeLink($codeId, $orderId) {
    if (strlen($codeId) == 0) {
        return '-';
    }

    try {
        $code = new Yourdelivery_Model_Rabatt_Code(null, $codeId);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return null;
    }

    return '<div class="yd-grid-options">
            <p><a href="javascript:void(0)" class="yd-edit-rabattcode" id="yd-edit-rabattcode-' . $code->getId() . '-' . $orderId . '">' . $code->getParent()->getName() . ' (' . $code->getCode() . ')</a></p>
         </div>';
}

// get service types for this meal category
function getServiceTypes($id) {
    $category = new Yourdelivery_Model_Meal_Category($id);

    $typesList = "";

    foreach ($category->getServiceTypes() as $ind => $type) {
        if ($ind > 0) {
            $typesList .= ", ";
        }
        $typesList .= $type['name'];
    }
    return $typesList;
}

// get all persons, responsible for this restaurant
function getAdmins($id) {
    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($id);
    $adminsList = "";
    $ind = 0;

    foreach ($restaurant->getAdmins() as $admin) {
        if ($ind > 0) {
            $adminsList .= "<br/>";
        }
        $adminsList .= "<a href=\"/administration_user/edit/id/" . $admin->getId() . "\">" . $admin->getName() . " " . $admin->getPrename() . "</a>";
        $ind++;
    }
    return $adminsList;
}

// get categories this option belongs to
function getOptionsCategories($id) {
    $option = new Yourdelivery_Model_Meal_Option($id);

    $categoryList = "";

    foreach ($option->getCategories() as $ind => $cat) {
        if ($ind > 0) {
            $categoryList .= ", ";
        }
        $categoryList .= $cat['name'];
    }
    return $categoryList;
}

/**
 * @author Alex Vait <vait@lieferando.de>, Vincent Priem <priem@lieferando.de>
 * @since 31.07.2012
 * @param int $id
 * @return string 
 */
function getOptionsGroups($id) {

    $html = array();
    $option = new Yourdelivery_Model_Meal_Option($id);
    $groups = $option->getOptionsGroups();
    foreach ($groups as $group) {
        $html[] = '<a href="/restaurant_options/editgroup/id/' . $group['id'] . '">' . $group['name'] . '  (#' . $group['id'] . ') ' . ($group['internalName'] ? "(" . $group['internalName'] . ")" : "") . '</a>';
    }
    return implode(", ", $html);
}

// get extras of this extras group
function getExtrasForGroup($id) {
    $extrasGroup = new Yourdelivery_Model_Meal_ExtrasGroups($id);
    $extrasList = "";

    foreach ($extrasGroup->getExtrasSorted() as $ind => $extra) {
        if ($ind > 0) {
            $extrasList .= "<br/>";
        }

        $extrasList .= "<a href=\"/restaurant_extras/edit/id/" . $extra['id'] . "\" target=\"_blank\">" . $extra['name'] . "</a>";
    }
    return $extrasList;
}

// get options of this option group
function getOptionsForGroup($id) {
    $optRow = new Yourdelivery_Model_Meal_OptionRow($id);
    $optionsList = "";

    foreach ($optRow->getOptionsSorted() as $ind => $option) {
        if ($ind > 0) {
            $optionsList .= "<br/>";
        }

        $optionsList .= "<a href=\"/restaurant_options/edit/id/" . $option['id'] . "\" target=\"_blank\">" . $option['name'] . "</a>";
    }
    return $optionsList;
}

/**
 * get meals associated with this option group
 *  @author alex
 * @since 11.08.2010
 */
function getMealsForOptionsGroup($id) {
    $optRow = new Yourdelivery_Model_Meal_OptionRow($id);
    $mealsList = "";

    foreach ($optRow->getAssociatedMeals() as $ind => $meal) {
        if ($ind > 0) {
            $mealsList .= "<br/>";
        }

        $mealsList .= $meal['name'] . "  " .
                "(<a href=\"/restaurant_categories/edit/id/" . $meal['categoryId'] . "\" target=\"_blank\">" . $meal['categoryName'] . "</a>)";
    }
    return $mealsList;
}

// get firms and restaurnats, associated with this contact
function getAssociations($contactId) {
    $associations = __b("Firmen:<br/>");

    try {
        $contact = new Yourdelivery_Model_Contact($contactId);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return __b('*** Fehler: kann den Kontakt nicht erstellen');
    }

    $companys_db = $contact->getCompanys();
    foreach ($companys_db as $c) {
        try {
            $company = new Yourdelivery_Model_Company($c['id']);
            $associations .= "<a href=\"/administration_company_edit/index/companyid/" . $company->getId() . "\">" . $company->getName() . "</a><br/>";
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
    }

    $associations .= __b("Dienstleister:") . "<br/>";
    $restaurants_db = $contact->getServices();
    foreach ($restaurants_db as $r) {
        try {
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($r['id']);
            $associations .= "<a href=\"/administration_service_edit/index/id/" . $restaurant->getId() . "\">" . $restaurant->getName() . "</a><br/>";
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
    }
    return $associations;
}

// order in readable format with colors
function translateStatus($state) {
    switch ($state) {
        case 1:
            return '<span style="color:red;">' . __('unbezahlt') . '</span>';
        default:
            return '<span style="color:green;">' . __('bezahlt') . '</span>';
    }
}

// discount repeat state in readable format
function rrepeatToReadable($state, $anzahl) {
    switch ($state) {
        default: return __b('unbekannt');
        case 0: return __b('Einmalig');
        case 1: return __b('Wiederholt');
        case 2: return __b('Anzahl (%s)', $anzahl);
    }
}

// state in readable format
function repeatToReadable($state) {
    switch ($state) {
        default: return __('unbekannt');
        case 0: return __('Läuft');
        case 1: return __('Pausiert');
        case 2: return __('Gelöscht');
    }
}

// boolean in yes/no readable format
function intToYesNo($state) {
    switch ($state) {
        default: return __b('unbekannt');
        case 0: return '<font color="#ff6666">' . __b('Nein') . '</font>';
        case 1: return '<font color="#009900">' . __b('Ja') . '</font>';
    }
}

// boolean in yes/no icons
function intToYesNoIcon($state) {
    switch ($state) {
        case 0: return '<img class="yd-state-center" src="/media/images/yd-backend/yd-state-no.png">';
        case 1: return '<img class="yd-state-center" src="/media/images/yd-backend/yd-state-yes.png">';
        default: return '?';
    }
}

// boolean in yes/no/empty icons
function intToYesNoNullIcon($state) {
    switch ($state) {
        case 2: return '<img class="yd-state-center" src="/media/images/yd-backend/yd-state-no.png">';
        case 1: return '<img class="yd-state-center" src="/media/images/yd-backend/yd-state-yes.png">';
        case 0: return '';
        default: return '';
    }
}

// boolean in yes/no readable format, but reverse
function intToYesNoReverse($state) {
    switch ($state) {
        default: return __('unbekannt');
        case 1: return '<font color="#ff6666">' . __b('Nein') . '</font>';
        case 0: return '<font color="#009900">' . __b('Ja') . '</font>';
    }
}

// discount amount in readable format
function rabattToReadable($rabatt, $measure) {
    switch ($measure) {
        default: return __b('unbekannt');
        case '0': return $rabatt . ' %';
        case '1': return intToPrice($rabatt) . __b(' €');
    }
}

// discount type in readable format
function rabattKindToReadable($kind) {
    switch ($kind) {
        default: return __b('unbekannt');
        case '0': return '%';
        case '1': return __b('€');
    }
}

// deleted state in readable format
function deletedToReadable($deleted) {
    switch ($deleted) {
        default: return __b('unbekannt');
        case '0': return '<font color="#559955">' . __b('aktiv') . '</font>';
        case '1': return '<font color="#cc3333">' . __b('gelöscht') . '</font>';
    }
}

/**
 * deleted user state in readable format
 * @author Alex Vait <vait@lieferando.de>
 * @since 24.05.2012
 */
function deletedUserToReadable($deleted) {
    switch ($deleted) {
        case '0': return '<font color="#559955">' . __b('aktiv') . '</font>';
        default: return '<font color="#cc3333">' . __b('gelöscht') . '</font>';
    }
}

// online/offline state in readable format
function statusToReadable($active) {
    switch ($active) {
        default: return __b('unbekannt');
        case '1': return '<font color="#339933">' . __b('online') . '</font>';
        case '0': return '<font color="#993333">' . __b('offline') . '</font>';
    }
}

// servicetype offline reason in readable format
function offlineStatusToReadable($status) {
    $stati = Yourdelivery_Model_Servicetype_Abstract::getStati();

    if (!in_array(intval($status), array_keys($stati))) {
        return __b('unbekannt');
    }

    return $stati[$status];
}

// salesperson type in readable format
function callcenterToReadable($type) {
    switch ($type) {
        default: return __b('unbekannt');
        case '0': return __b('Aussendienst');
        case '1': return __b('Call Center');
    }
}

// is this salesperson registered as admin
function registeredAsAdmin($reg) {
    if ($reg == 0) {
        return __b('ja');
    } else {
        return '<b><font color="#ff3333">' . __b('nein') . '</font></b>';
    }
}

/**
 * payment type in readable format
 * 
 * @param string $payment
 * @param int $id
 * 
 * @return string
 */
function paymentToReadable($payment, $id = null) {
    return Default_Helpers_Grid_Order::payment($payment, $id);
}

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 05.10.2011
 * @param boolean $isPaid
 */
function isPaidToReadable($isPaid) {
    if ($isPaid) {
        return __('bezahlt');
    }
    return __('unbezahlt');
}

// restaurant mode type in readable format
function modeToReadable($mode) {
    return Default_Helpers_Human_Readable_Default::ordermode($mode);
}

// orderer type in readable format
function kindToReadable($kind) {
    return Default_Helpers_Human_Readable_Default::orderkind($kind);
}

// link to the tracking code
function getTrackingCodeLink($trackingCodeId) {
    try {
        $code = new Yourdelivery_Model_Tracking_Code($trackingCodeId);
        return $code->getLink();
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return 'fehler';
    }
}

// to make zero data readable
function dateToReadable($date) {
    if ($date == '0.0.0') {
        return __b('offen');
    }
    return $date;
}

function onofflineToReadable($state) {
    switch ($state) {
        default: return __('unbekannt');
        case 0: return __('online');
        case 1: return __('offline');
    }
}

// checkboxes in locations table, restaurant backend
function locationsCheckbox($id) {
    return '<input class="yd-locations-checkbox" type="checkbox" name="yd-location[' . $id . ']" value="1"/>';
}

// checkboxes for all other grid tables
function idCheckbox($id, $value = null) {

    if ($value) {
        return '<input class="yd-checkbox" id="yd-id-checkbox-' . $id . '" type="checkbox" name="yd-id-checkbox[' . $id . ']" value="1" checked="checked" />';
    }
    return '<input class="yd-checkbox" id="yd-id-checkbox-' . $id . '" type="checkbox" name="yd-id-checkbox[' . $id . ']" value="1" />';
}


// show link to pop-up window with meal-extras associations or 'none' if no association exists
function extrasMealRelations($state, $id) {
    switch ($state) {
        default: return __b('unbekannt');
        case 0: return '<font color="#ff6666">' . __b('Keine') . '</font>';
        case 1: return '<a href="#" class="yd-restaurant-show-extra-meals" id="yd-extra-' . $id . '">' . __b('Vorhanden') . '</a>';
    }
}

/**
 * Show links for orders - storno, resending, fake, etc.
 * @author alex
 * @since 25.08.2010 or so
 */
function optionsForOrders($id) {
    return Default_Helpers_Grid_Order::options($id);
}

/**
 * Show links for orders in restaurant backend
 * @author alex
 * @since 05.01.2011
 */
function optionsForOrdersRestaurant($id, $mode = 'Restaurant') {
    $html = '<div class="yd-grid-options">
        <p>
            <a href="#" title="Bestellzettel als Html angucken" onclick="return popup(\'/order/bestellzettel/order/' . $id . '\', \'Bestellzettel\', 800, 600);">Html</a>
        </p>';

    // extra options for "great" orders
    if (strcmp($mode, 'Großhandel') == 0) {
        try {
            $order = new Yourdelivery_Model_Order($id);
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($order->getRestaurantId());

            if (($order->getState() == 0)) {
                $html .= '<p><a href="#" class="yd-edit-order-options" id="yd-edit-order-options-' . $id . '">Lieferzeit bestätigen</a></p>';
            } else if (($order->getState() > 0) && $restaurant->getAcceptsPfand() == 1) {
                $html .= '<p><a href="#" class="yd-edit-order-options" id="yd-edit-order-options-' . $id . '">Pfand eintragen</a></p>';
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
    }

    $html .= '</div>';

    return $html;
}

// online/offline state in restaurant backend in readable format
function onlineStatus($status) {
    if ($status == 1) {
        return "<div id=\"yd-online-status-" . $status . "\" class=\"onlineState\">" . __b('online') . "</div>";
    } else {
        return "<div id=\"yd-online-status-" . $status . "\" class=\"onlineState\">" . __b('offline') . "</div>";
    }
}

/**
 * links to admin group edit pages, if the group is not admin
 * @author Alex Vait <vait@lieferando.de>
 * @since 28.08.2012
 * @return Yourdelivery_Model_DbTable_Admin_Access_UserGroupNn
 */

function adminGroupLinks($adminId) {
    try {
        $admin = new Yourdelivery_Model_Admin($adminId);
    } 
    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return "";
    }

    $links = "";
    
    foreach ($admin->getGroups() as $group) {        
        $links .= '<a href="/administration_adminrights/editgroup/id/' . $group->getId() . '">' . $group->getName() . '</a><br/>';
    }
    
    return $links;
}

/**
 * @deprecated
 */
function getRegisteredCustomerLink($name, $email, $customerId = "", $iPhoneUuid = "", $orderId = null) {
    return Default_Helpers_Grid_Customer::customerInfo($name, $email, $customerId, $iPhoneUuid, $orderId);
}

// daytime in readable format
function intToDaytime($time) {
    if (strlen($time) != 4) {
        return "unbekannt";
    }
    return substr($time, 0, 2) . ":" . substr($time, 2, 2);
}

// not surprising
function secToMinutes($sec) {
    return $sec / 60;
}

/**
 * Translation
 * Lookup a message in the current domain
 * @author vpriem
 * @param string $msg
 * @return string
 */
if (!function_exists("gettext")) {

    function gettext($msg) {
        return $msg;
    }

}

if (!function_exists("dgettext")) {

    function dgettext($domain, $msg) {
        return $msg;
    }

}

/**
 * frontend translation tag
 * @param string $msg
 * @return string
 */
function __($msg) {
    $msg = gettext($msg);
    $params = func_get_args();
    if (count($params) > 1) {
        return vsprintf($msg, array_slice($params, 1));
    }
    return $msg;
}

/**
 * backend translation tag
 * @param string $msg
 * @return string
 */
function __b($msg) {
    $msg = dgettext("yd-backend", $msg);
    $params = func_get_args();
    if (count($params) > 1) {
        return vsprintf($msg, array_slice($params, 1));
    }
    return $msg;
}

/**
 * partner-backend translation tag
 * @param string $msg
 * @return string
 */
function __p($msg) {
    $msg = dgettext("yd-partner", $msg);
    $params = func_get_args();
    if (count($params) > 1) {
        return vsprintf($msg, array_slice($params, 1));
    }
    return $msg;
}

/**
 * Translation
 * Plural version of gettext
 * @author vpriem
 * @since 09.12.2010
 * @param string $msg1
 * @param string $msg2
 * @param int $n
 * @return string
 */
if (!function_exists("ngettext")) {

    function ngettext($msg1, $msg2, $n) {
        return $n > 1 ? $msg2 : $msg1;
    }

}

/**
 * frontend translation tag plural
 * @param string $msg1
 * @param string $msg2
 * @param integer $n
 * @return string
 */
function _n($msg1, $msg2, $n) {
    $msg = ngettext($msg1, $msg2, $n);
    $params = func_get_args();
    if (count($params) > 3) {
        return vsprintf($msg, array_slice($params, 3));
    }
    return $msg;
}

/**
 * backend translation tag plural
 * @param string $msg1
 * @param string $msg2
 * @param integer $n
 * @return string
 */
function _bn($msg) {
    $msg = dngettext("yd-backend", $msg1, $msg2, $n);
    $params = func_get_args();
    if (count($params) > 3) {
        return vsprintf($msg, array_slice($params, 3));
    }
    return $msg;
}

/**
 * partner backend translation tag plural
 * @param string $msg1
 * @param string $msg2
 * @param integer $n
 * @return string
 */
function _pn($msg) {
    $msg = dngettext("yd-partner", $msg1, $msg2, $n);
    $params = func_get_args();
    if (count($params) > 3) {
        return vsprintf($msg, array_slice($params, 3));
    }
    return $msg;
}

/**
 * check if a string has been serialized
 * @author mlaug
 * @param mixed $data
 * @return boolean
 */
function is_serialized($data) {
    // if it isn't a string, it isn't serialized
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    if (!preg_match('/^([adObis]):/', $data, $badions)) {
        return false;
    }
    switch ($badions[1]) {
        case 'a' :
        case 'O' :
        case 's' :
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                return true;
            }
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                return true;
            }
            break;
    }
    return false;
}

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 22.08.2010
 * @param SplObjectStorage $budgets
 * @param int $addressId
 * @return string
 */
function budgetHasAddress($budgets, $addressId) {
    $budgetNames = '<ul>';
    foreach ($budgets as $budget) {
        if ($budget->hasAddress($addressId)) {
            $budgetNames .= '<li>' . $budget->getName() . '</li>';
        }
    }
    $budgetNames .= '</ul>';
    return $budgetNames;
}

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 01.09.2010
 * @param int $budgetId
 * @return string
 */
function budgetUseElma($budgetId) {
    $link = null;

    try {
        $budget = new Yourdelivery_Model_Budget($budgetId);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return $link;
    }

    if ($budget->makeUseOfYdCard()) {
        $link = '<a href="/company/ydcardbudget/id/' . $budget->getYdCard()->getId() . '" alt="' . $budget->getName() . ' (ELMA) bearbeiten">' . $budget->getName() . ' (ELMA)</a>';
    } else {
        $link = '<a href="/company/budget/id/' . $budgetId . '" alt="' . $budget->getName() . ' bearbeiten">' . $budget->getName() . '</a>';
    }
    return $link;
}

/**
 * show options for company billings
 * @author alex
 * since 13.10.2010
 */
function optionsForBillingCompany($id) {
    try {
        $billing = new Yourdelivery_Model_Billing($id);
        $company = new Yourdelivery_Model_Company($billing->getRefId());
        $contact = new Yourdelivery_Model_Contact($company->getBillingContactId());
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return null;
    }

    if (strcmp($company->getBillDeliver(), "email") == 0) {
        $billDeliverEmail = 'checked="checked"';
    } else if (strcmp($company->getBillDeliver(), "fax") == 0) {
        $billDeliverFax = 'checked="checked"';
    } else if (strcmp($company->getBillDeliver(), "post") == 0) {
        $billDeliverPost = 'checked="checked"';
    }

    $html = '<form method="post" action="/administration_request_billing/send" class="yd-send-bill send-billing" id="yd-send-bill-form-' . $billing->getId() . '">
        <input type="hidden" name="id" value="' . $billing->getId() . '" />
        <table>
            <tr>
                <td>
                    <input type="text" id="yd-viafax-data-' . $billing->getId() . '" name="faxnumber" value="' . $contact->getFax() . '"/>
                </td>
                <td>
                    <input type="checkbox" id="yd-viafax-checkbox-' . $billing->getId() . '" name="viafax" ' . $billDeliverFax . ' value="1" /> ' . __b('Fax') . '
                </td>
                <td rowspan="2" align="center">
                    <table>
                        <tr>
                            <td>
                                <input type="checkbox" disabled="disabled" checked="checked" /> pdf
                            </td>
                            <td widht="30%">
                                <input type="checkbox" name="sendcsv" value="1" /> csv
                            </td>
                            <tr>
                                <td colspan="2">
                                    <input type="submit" id="yd-send-bill-button-' . $billing->getId() . '" class="yd-send-bill-button" value="' . __b('Senden') . '" />
                                </td>
                            </tr>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" id="yd-viamail-data-' . $billing->getId() . '" name="emailaddr" value="' . $contact->getEmail() . '" />
                </td>
                <td>
                    <input type="checkbox" id="yd-viamail-checkbox-' . $billing->getId() . '" name="viaemail" ' . $billDeliverEmail . ' value="1" /> ' . __b('eMail') . ' <br /><br />
                    <input type="checkbox" id="yd-email-sign-' . $billing->getId() . '" name="sign" value="1" /> ' . __b('signieren') . '
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="viapost" ' . $billDeliverPost . ' value="1" /> ' . __b('Post') . '
                </td>
                <td>' . __b('Die Firma möchte Rechnungen') . '<br/>' . __b('per Post erhalten') . '</td>
            </tr>
            <tr>
                <td colspan="3">
                    <a href="/administration_billing/resetbill/crefo/1/mode/company/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Neu generieren') . '</a>&nbsp;|&nbsp;
                    <a href="/administration_billing/resetbill/crefo/0/mode/company/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Neu generieren (ohne Crefo)') . '</a>&nbsp;|&nbsp;
                    <a href="/administration_billing/edit/id/' . $billing->getId() . '">' . __b('Bearbeiten') . '</a>&nbsp;|&nbsp;
                    <a href="/administration_billing/deletebill/mode/company/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Löschen') . '</a>
                </td>
            </tr>
        </table>
    </form>';

    return $html;
}

/**
 * show options for restaurant billings
 * @author alex
 * since 13.10.2010
 */
function optionsForBillingRestaurant($id) {
    try {
        $billing = new Yourdelivery_Model_Billing($id);
        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($billing->getRefId());
        $contact = new Yourdelivery_Model_Contact($restaurant->getBillingContactId());
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return null;
    }

    $billDeliver = explode(",", $restaurant->getBillDeliver());
    if (in_array("email", $billDeliver)) {
        $billDeliverEmail = 'checked="checked"';
    }
    if (in_array("fax", $billDeliver)) {
        $billDeliverFax = 'checked="checked"';
    }
    if (in_array("post", $billDeliver)) {
        $billDeliverPost = 'checked="checked"';
    }

    $reset = '';
    if ($billing->getStatus() < 1) {
        $reset = '<a href="/administration_billing/resetbill/mode/rest/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Neu generieren') . '</a>&nbsp;|&nbsp;';
    }

    if ($billing->getStatus() < 2 && $billing->getAmount() > 0) {
        $balance = '<a href="/administration_billing/balancebill/mode/rest/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Verrechnen') . '</a>&nbsp;|&nbsp;';
    }

    $html = '<form method="post" action="/administration_request_billing/send" class="yd-send-bill send-billing" id="yd-send-bill-form-' . $id . '">
        <input type="hidden" name="id" value="' . $billing->getId() . '" />
        <table>
            <tr>
                <td>
                    <input type="text" id="yd-viafax-data-' . $billing->getId() . '" name="faxnumber" value="' . ($contact->getFax() ? $contact->getFax() : $restaurant->getFax()) . '"/>
                </td>
                <td>
                    <input type="checkbox" id="yd-viafax-checkbox-' . $billing->getId() . '" name="viafax" ' . $billDeliverFax . ' value="1" />' . __b('Fax') . '
                </td>
                <td rowspan="2" align="center">
                    <table>
                        <tr>
                            <td>
                                <input type="checkbox" disabled="disabled" checked="checked" /> ' . __b('pdf') . '
                            </td>
                            <td widht="30%">
                                <input type="checkbox" name="sendcsv" value="1" /> ' . __b('csv') . '
                            </td>
                            <tr>
                                <td colspan="2">
                                    <input type="submit" id="yd-send-bill-button-' . $billing->getId() . '" class="yd-send-bill-button" value="' . __b('Senden') . '" />
                                </td>
                            </tr>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" id="yd-viamail-data-' . $billing->getId() . '" name="emailaddr" value="' . ($contact->getEmail() ? $contact->getEmail() : $restaurant->getEmail()) . '" />
                </td>
                <td>
                    <input type="checkbox" id="yd-viamail-checkbox-' . $billing->getId() . '" name="viaemail" ' . $billDeliverEmail . ' value="1" /> ' . __b('eMail') . '
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="viapost" ' . $billDeliverPost . ' value="1" /> ' . __b('Post') . '
                </td>
                <td>' . __b('Der Dienstleister möchte Rechnungen ') . '<br/>' . __b('per Post erhalten') . '</td>
            </tr>
            <tr>
                <td colspan="3">
                    ' . $reset . $balance . '
                    <a href="/administration_billing/edit/id/' . $billing->getId() . '">' . __b('Bearbeiten') . '</a>&nbsp;|&nbsp;
                    <a href="/administration_billing/deletebill/mode/rest/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Löschen') . '</a>
                </td>
            </tr>
        </table>
    </form>';

    return $html;
}

/**
 * show options for courier billings
 * @author alex
 * since 07.02.2011
 */
function optionsForBillingCourier($id) {
    try {
        $billing = new Yourdelivery_Model_Billing($id);
        $courier = new Yourdelivery_Model_Courier($billing->getRefId());
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return null;
    }

    if (strcmp($courier->getBillDeliver(), "email") == 0) {
        $billDeliverEmail = 'checked="checked"';
    } else if (strcmp($courier->getBillDeliver(), "fax") == 0) {
        $billDeliverFax = 'checked="checked"';
    } else if (strcmp($courier->getBillDeliver(), "post") == 0) {
        $billDeliverPost = 'checked="checked"';
    }

    $html = '<form method="post" action="/administration_request_billing/send" class="yd-send-bill send-billing" id="yd-send-bill-form-' . $billing->getId() . '">
        <input type="hidden" name="id" value="' . $billing->getId() . '" />
        <table>
            <tr>
                <td>
                    <input type="text" id="yd-viafax-data-' . $billing->getId() . '" name="faxnumber" value="' . $courier->getFax() . '"/>
                </td>
                <td>
                    <input type="checkbox" id="yd-viafax-checkbox-' . $billing->getId() . '" name="viafax" ' . $billDeliverFax . ' value="1" /> ' . __b('Fax') . '
                </td>
                <td rowspan="2" align="center">
                    <table>
                        <tr>
                            <td>
                                <input type="checkbox" disabled="disabled" checked="checked" /> ' . __b('pdf') . '
                            </td>
                            <td widht="30%">
                                <input type="checkbox" name="sendcsv" value="1" /> ' . __b('csv') . '
                            </td>
                            <tr>
                                <td colspan="2">
                                    <input type="submit" id="yd-send-bill-button-' . $billing->getId() . '" class="yd-send-bill-button" value="' . __b('Senden') . '" />
                                </td>
                            </tr>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" id="yd-viamail-data-' . $billing->getId() . '" name="emailaddr" value="' . $courier->getEmail() . '" />
                </td>
                <td>
                    <input type="checkbox" id="yd-viamail-checkbox-' . $billing->getId() . '" name="viaemail" ' . $billDeliverEmail . ' value="1" /> ' . __b('eMail') . '
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="viapost" ' . $billDeliverPost . ' value="1" /> ' . __b('Post') . '
                </td>
                <td>' . __b('Der Kurierdienst möchte Rechnungen') . ' <br/>' . __b('per Post erhalten') . '</td>
            </tr>
            <tr>
                <td colspan="3">
                    <a href="/administration_billing/resetbill/mode/company/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Neu generieren') . '</a>&nbsp;|&nbsp;
                    <a href="/administration_billing/edit/id/' . $billing->getId() . '">' . __b('Bearbeiten') . '</a>&nbsp;|&nbsp;
                    <a href="#" class="yd-mahnung" id="yd-mahnung-' . $billing->getId() . '">' . __b('Mahung erstellen') . '</a>&nbsp;|&nbsp;
                    <a href="/administration_billing/deletebill/mode/company/id/' . $billing->getId() . '" class="yd-are-you-sure">' . __b('Löschen') . '</a>
                </td>
            </tr>
        </table>
    </form>';

    return $html;
}

function getCanteenOrderNames($canteenOrderId) {
    $names = array();

    if (!is_null($canteenOrderId)) {
        try {
            $canteenOrder = new Yourdelivery_Model_Canteen_Order($canteenOrderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
        $consOrders = $canteenOrder->getConsolidateOrders();

        $names[] = $consOrders->count();

        foreach ($consOrders as $order) {
            $names[] = $order->getCustomer()->getFullname() . ' (Bestellung #' . $order->getId() . ')';
            var_dump($order->getCustomer());
        }
    }
    return implode(', ', $names);
}

function optionsForCanteenOrders($id) {
    $order = null;
    try {
        $order = new Yourdelivery_Model_Order($id);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        return null;
    }

    return "<div class='yd-grid-options'>
        <p>
            <a title=\"Bestellzettel als Html angucken\" href=\"#\" onClick=\"window.open('/order/bestellzettel/order/" . $id . "', 'Bestellzettel', 'scrollbars=1,width=650,left=50,top=50')\">Html</a>
            &nbsp;|&nbsp;" . (file_exists(APPLICATION_PATH . "/../storage/orders/canteen/" . $order->getCanteen()->getId() . "/" . date('Y-m-d', $order->getDeliverTimestamp()) . "/orderfax-canteen.pdf") ? "<a title=\"Kantinen Pdf ansehen\" href=\"/storage/orders/canteen/" . $order->getCanteen()->getId() . "/" . date('Y-m-d', $order->getDeliverTimestamp()) . "/orderfax-canteen.pdf\">Pdf</a></p>" : "") . "
        <p><a class=\"cursor yd-edit-order-options\" id=\"yd-edit-order-options-" . $id . "\">Bearbeiten</a></p>
     </div>";
}

/**
 * List all companies having association wiht this canteen
 *
 */
function companiesOfCanteen($canteenId) {
    $canteen = new Yourdelivery_Model_Servicetype_Canteen($canteenId);

    $result = "";
    foreach ($canteen->getCompanies() as $company) {
        $result .= $company->getName() . "<br/>";
    }
    return $result;
}

/**
 * Show only a picture for the specified meal
 * @author alex
 * @since 14.12.2010
 */
function showMealPicture($mealId, $restaurantId) {

    if (is_null($mealId)) {
        return "";
    }

    $picture = new Default_File_Picture();

    $file = '/storage/restaurants/' . $restaurantId . "/meals/" . $mealId . "/default.jpg";

    if (!file_exists(APPLICATION_PATH . '/..' . $file)) {
        return "";
    }
    return "<img src='" . $file . "' width='115' height='75'>";
}

/**
 * Link to download billing as pdf, csv and zip
 * @since 04.01.2010
 * @author alex
 */
function billingDownloadLinks($number, $id) {
    $html = $number;

    // pdf
    $file = APPLICATION_PATH . '/../storage/billing/' . $number . ".pdf";
    if (file_exists($file)) {
        $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/pdf">PDF Rechnung herunterladen</a>';
    }

    // csv
    $file = APPLICATION_PATH . '/../storage/billing/' . $number . ".csv";
    if (file_exists($file)) {
        $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '/csv">CSV Rechnung herunterladen</a>';
    }

    // voucher
    $file = APPLICATION_PATH . '/../storage/billing/' . str_replace("R-", "G-", $number) . ".pdf";
    if (file_exists($file)) {
        $html .= '<br /><a href="/download/voucher/' . Default_Helpers_Crypt::hash($id) . '">PDF Gutschrift herunterladen</a>';
    }

    // asset
    $file = APPLICATION_PATH . '/../storage/billing/' . str_replace("R-", "A-", $number) . ".pdf";
    if (file_exists($file)) {
        $html .= '<br /><a href="/download/asset/' . Default_Helpers_Crypt::hash($id) . '">PDF Rechnungsposten herunterladen</a>';
    }

    // zip
    $file = APPLICATION_PATH . '/../storage/billing/' . $number . ".pdf";
    if (file_exists($file)) {
        $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($id) . '">ZIP herunterladen</a>';
    }

    try {
        $billing = new Yourdelivery_Model_Billing($id);
        $costcenters = $billing->getCostcenters();
        if (count($costcenters) > 0) {
            $html .= '<br/><br/>';
            $html .= '<small><a href="#x" class="yd-bill-show-costcenters" id="yd-bill-costcenters-title-' . $id . '">Kostenstellen &darr;</a></small>';
            $html .= '<div id="yd-bill-costcenters-links-' . $id . '" style="display:none;"><br/>';
            foreach ($costcenters as $cc) {
                if (!is_null($cc['costcenterId'])) {
                    // link to costcenter pdf
                    if ($billing->getSubPdf($cc['costcenterId'])) {
                        $html .= '<a href="/download/subbill/' . Default_Helpers_Crypt::hash($id) . '/' . $cc['costcenterId'] . '">Kostenstelle ' . $cc['costcenterId'] . '. Betrag : ' . intToPrice($cc['amount']) . '</a><br/>';
                    } else {
                        $html .= 'Kostenstelle ' . $cc['costcenterId'] . '. Betrag : ' . intToPrice($cc['amount']) . '<br/>';
                    }
                }
            }
            $html .= '</div>';
        }
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        
    }
    return $html;
}

/**
 * Link to billings of this billing asset
 * @since 10.01.2011
 * @author alex
 */
function billingsForBillingAssets($billRest, $billCompany, $billCourier) {
    $billsLink = '';

    if (intval($billRest) != 0 && intval($billRest) != 99999) {
        try {
            $cbill = new Yourdelivery_Model_Billing($billRest);
            // search for pdf file
            $file = APPLICATION_PATH . '/../storage/billing/' . $cbill->getNumber() . ".pdf";
            if (file_exists($file)) {
                $billsLink .= __b('Dienstleisterrechnung:') . '<a href="/download/bill/' . Default_Helpers_Crypt::hash($cbill->getId()) . '/pdf">' . $cbill->getNumber() . ' (pdf)</a>';
            } else {
                $billsLink .= __b('Dienstleisterrechnung: ') . $cbill->getNumber();
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
    }

    if (intval($billCompany) != 0 && intval($billCompany) != 99999) {
        try {
            $cbill = new Yourdelivery_Model_Billing($billCompany);
            // search for pdf file
            $file = APPLICATION_PATH . '/../storage/billing/' . $cbill->getNumber() . ".pdf";
            if (file_exists($file)) {
                $billsLink .= '<br/>' . __b('Firmenrechnung:') . ' <a href="/download/bill/' . Default_Helpers_Crypt::hash($cbill->getId()) . '/pdf">' . $cbill->getNumber() . ' (pdf)</a>';
            } else {
                $billsLink .= '<br/>' . __b('Firmenrechnung:') . ' ' . $cbill->getNumber();
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
    }

    if (intval($billCourier) != 0 && intval($billCourier) != 99999) {
        try {
            $cbill = new Yourdelivery_Model_Billing($billCourier);
            // search for pdf file
            $file = APPLICATION_PATH . '/../storage/billing/' . $cbill->getNumber() . ".pdf";
            if (file_exists($file)) {
                $billsLink .= '<br/>' . __b('Kurierrechnung:') . ' <a href="/download/bill/' . Default_Helpers_Crypt::hash($cbill->getId()) . '/pdf">' . $cbill->getNumber() . ' (pdf)</a>';
            } else {
                $billsLink .= '<br/>' . __b('Kurierrechnung:') . ' ' . $cbill->getNumber();
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
    }
    return $billsLink;
}

/**
 * Show value of the billing and, if available, of the voucher
 * @since 11.01.2011
 * @author alex
 */
function billTotalAndVoucher($total, $voucher) {
    $html = __b('Rechnungsbetrag:') . "&nbsp;" . intToPrice($total);

    if (intval($voucher) > 0) {
        $html .= "<br/>" . __b('Gutschrift:') . "&nbsp;" . intToPrice($voucher);
    }
    return $html;
}

/**
 * Show phone if restaurant must be notified
 * @since 17.01.2011
 * @author alex
 */
function notifyPayedForOrders($id, $notifyPayed, $payment, $franchiseTypeId, $sendBy) {

    $return = "";

    if ($franchiseTypeId == FRANCHISE_TYPE_PREMIUM) {
        $return .= '<img src="/media/images/yd-backend/yd-phone.png" title="' . __b('Der Dienstleister soll über die Bestellung telefonisch informiert werden.') . '" class="tooltip">';
    } elseif ((intval($notifyPayed) > 0) && (strcmp($payment, "Barzahlung") != 0)) {
        $return .= '<img src="/media/images/yd-backend/yd-phone.png" title="' . __b('Die Rechnung wurde online bezahlt. Der Dienstleister soll darüber telefonisch informiert werden.') . '" class="tooltip">';
    }

    if ($franchiseTypeId == FRANCHISE_TYPE_NOCONTRACT) {
        $return .= '<img src="/media/images/yd-backend/yd-no-contract.png" title="' . __b('Der Dienstleister hat noch keinen Vertrag. Er sollte über die Bestellung telefonisch informiert werden.') . '" class="tooltip">';
    }

    $return .= '<img src="/media/images/yd-backend/sendby-' . ($sendBy ? $sendBy : "pending") . '.png" title="' . __b($sendBy == 'phone' ? "Diese Bestellung muss per Telefon übermittelt werden/ist per Telefon übermittelt worden" : ($sendBy ? "Diese Bestellung wurde per '" . $sendBy . "' übermittelt" : "Diese Bestellung wurde noch nicht übermittelt")) . '" class="tooltip">';
    return $return;
}

/**
 * parse the comment string from the order status log and parse
 * all numbers into a link to the order
 * changed to highlight Fax Support
 * @todo: may need some additional checks, that this works correct
 * @author mlaug,daniel
 * @since 21.01.2011
 * @param string $comment
 * @return string
 */
function parseOrderLog($comment) {

    return preg_replace('/(Fax wurde vom Support an Dienstleister gesendet\.)/', '<span style="color:red; font-weight:bold;">$1</span>', gettext($comment));
}

/**
 * sort ranges by plz-city alphabetically
 * @author alex
 * @since 19.10.2011
 */
function compare_ranges($r1, $r2) {
    return strnatcmp($r1['cityname'], $r2['cityname']);
}

/**
 * Get Name which corresponds to given id
 * @author Allen Frank <frank@lieferando.de>
 * @since 21-02-2012
 * @param type $franchiseTypeId
 * @return Name of the Franchise
 */
function getFranchise($franchiseTypeId) {
    return Yourdelivery_Model_Servicetype_Franchise::getById($franchiseTypeId, false, true);
}

function getFranchiseImage($restaurantId, $franchiseTypeId, $dlName) {
    return Default_Helpers_Grid_Service::decorateService($restaurantId, $franchiseTypeId, $dlName);
}

function emailinfo($email, $orderId = null) {
    return Default_Helpers_Grid_Customer::emailinfo($email, $orderId);
}

function discountinfo($rabattCodeId, $orderId, $isDiscount) {
    return Default_Helpers_Grid_Discount::discountInfo($rabattCodeId, $orderId, $isDiscount);
}

function telinfo($telCustomer, $telService, $orderId) {
    return Default_Helpers_Grid_Service::decorateTel($telCustomer, $telService, $orderId);
}

function companyinfo($companyName, $companyId, $orderId) {
    return Default_Helpers_Grid_Company::companyinfo($companyName, $companyId, $orderId);
}

function ipinfo($ip, $uuid, $orderId) {
    return Default_Helpers_Grid_Web::ipinfo($ip, $uuid, $orderId);
}

function addressinfo($addresse, $city, $orderId) {
    return Default_Helpers_Grid_Order::address($addresse, $city, $orderId);
}

function gridBlacklistOptions($valueId) {
    return Default_Helpers_Grid_Blacklist::options($valueId);
}

function gridBlacklistPaypalOptions($valueId, $blacklistId) {
    return Default_Helpers_Grid_Blacklist::paypalOptions($valueId, $blacklistId);
}

function gridBlacklistOrderLink($orderId) {
    return Default_Helpers_Grid_Blacklist::orderLink($orderId);
}

function gridBlacklistMatchings($matching) {
    return Default_Helpers_Grid_Blacklist::matchingTypes($matching);
}

/**
 * get the printer status history
 * @author Alex Vait
 * @since 30.08.2012
 * @param int $printerId
 * @return string
 */
function getPrinterStatesHistory($printerId, $actualState) {
    return Default_Helpers_Grid_Printer::getPrinterStatesHistory($printerId, $actualState);
}


/**
 * get all attributes of the meal as string
 * @author Alex Vait
 * @since 27.06.2012
 * @param int $id
 * @return string
 */
function getMealAttributes($id) {
    try {
        $meal = new Yourdelivery_Model_Meals($id);
        return $meal->getAtributesAsString();
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        
    }
    return "";
}

/**
 * get all attributes of the meal as string
 * @author Vincent Priem
 * @since 29.06.2012
 * @param int $id
 * @return string
 */
function getMealTypesHierarchy($id) {
    try {
        $meal = new Yourdelivery_Model_Meals($id);
        return $meal->getTypesHierarchyAsString();
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        
    }
    return "";
}
