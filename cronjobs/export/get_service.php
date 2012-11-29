<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));
ini_set('memory_limit', '2048M');

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 08.12.2011
 */
clog('info', 'create export "get_service"');

$config = Zend_Registry::get('configuration');
$domain = $config->domain->base;

/**
 *
 * @param DOMDocument $doc
 * @param string $name
 * @param mixed $value
 * @return DOMElement
 */
function create_node($doc, $name, $value, $attributeName = null, $attributeValue = null) {
    $elem = $doc->createElement($name);

    if (!is_null($attributeName) && !is_null($attributeValue)) {
        $elem->setAttribute($attributeName, $attributeValue);
    }

    $elem->appendChild($doc->createTextNode($value));
    return $elem;
}

/**
 *
 * @param type $doc
 * @param array $service
 * @param type $verboseDeliverAreas
 * @param type $verboseOpenings
 * @return type
 */
function createServiceChild($doc, array $service) {

    $config = Zend_Registry::get('configuration');

    try {

        $serviceObj = new Yourdelivery_Model_Servicetype_Restaurant($service['serviceId']);
        $additionalInfo = null;
        // use unix line breaks !!
        $view = new Default_View_Helper_Openings_Format();
        $openings = $serviceObj->getOpening()->getIntervals(strtotime('last sunday'), strtotime('next saturday'));
        $additionalInfo = $view->formatOpeningsMerged($openings, 'linebreak') . "\n";

        // accepted payments for service
        $service['payments'] = __('akzeptierte Bezahlarten:') . "\n";
        if ($serviceObj->isPaymentbar()) {
            $service['payments'] .= '* ' . __('Barzahlung') . "\n";
        }

        if (!$serviceObj->isOnlycash()) {
            $service['payments'] .= '* ' . __('Paypal & Gutschein') . "\n";
        }
        $additionalInfo .= $service['payments'] . "\n\n";

        $table = new Yourdelivery_Model_DbTable_Restaurant();
        $table->setId($service['serviceId']);

        //create service node with some data
        $sElem = $doc->createElement('service');
        $sElem->appendChild(create_node($doc, 'id', $service['serviceId']));
        $sElem->appendChild(create_node($doc, 'qypeId', $service['qypeId']));
        $sElem->appendChild(create_node($doc, 'name', $service['name']));
        $sElem->appendChild(create_node($doc, 'info', $additionalInfo . strip_tags($service['description'])));
        $sElem->appendChild(create_node($doc, 'picture', sprintf('http://%s/%s/service/%d/%s-250-0.jpg', $config->domain->timthumb, $config->domain->base, $service['serviceId'], urlencode($service['name']))));
        $sElem->appendChild(create_node($doc, 'plz', $service['plz']));
        $sElem->appendChild(create_node($doc, 'city', $service['cityName']));
        $sElem->appendChild(create_node($doc, 'telephon', $service['tel']));
        $sElem->appendChild(create_node($doc, 'fax', $service['fax']));
        $sElem->appendChild(create_node($doc, 'link', $service['restUrl']));
        $sElem->appendChild(create_node($doc, 'onlycash', $service['onlycash']));
        $sElem->appendChild(create_node($doc, 'allowcash', $service['paymentbar']));
        $sElem->appendChild(create_node($doc, 'street', $service['street'] . " " . $service['hausnr']));
        $sElem->appendChild(create_node($doc, 'category', $service['categoryName']));
        $sElem->appendChild(create_node($doc, 'premium', $service['premium'] ? "true" : "false"));

        $sElem->appendChild(create_node($doc, 'open', $serviceObj->getOpening()->isOpen(time()) ? "true" : "false"));

        //append openings
        $oElems = $doc->createElement('openings');
        foreach ($openings as $opening) {
            foreach ($opening as $key => $o) {
                if (strpos($key, 'next') !== false) {
                    continue;
                }
                $oElem = $doc->createElement('day');
                $oElem->setAttribute('weekday', $o['day']);
                $oElem->appendChild(create_node($doc, 'from', date('H:i', $o['timestamp_from'])));
                $oElem->appendChild(create_node($doc, 'until', date('H:i', $o['timestamp_until'])));
                $oElems->appendChild($oElem);
                unset($oElem);
            }
        }

        $sElem->appendChild($oElems);

        $plzElems = $doc->createElement('deliversTo');
        if ($cityId == 0) {
            $deliversTo = $table->getRanges();
            foreach ($deliversTo as $range) {
                $plz = $doc->createElement('deliverArea');
                $plz->appendChild(create_node($doc, 'cityId', $range['cityId']));
                $plz->appendChild(create_node($doc, 'parent', $range['parentCityId']));
                $plz->appendChild(create_node($doc, 'plz', $range['plz']));
                $plz->appendChild(create_node($doc, 'city', $range['cityname']));
                $plz->appendChild(create_node($doc, 'deliverCost', (integer) $range['delcost'], 'dimension', 'cent'));
                $plz->appendChild(create_node($doc, 'minCost', (integer) $range['mincost'], 'dimension', 'cent'));
                $plz->appendChild(create_node($doc, 'deliverTime', (integer) $range['deliverTime'], 'dimension', 'seconds'));
                $plzElems->appendChild($plz);
            }
        } else {
            $plz = $doc->createElement('deliverArea');
            $plz->appendChild(create_node($doc, 'cityId', $cityId));
            $plz->appendChild(create_node($doc, 'parent', (integer) $service['parentCityId']));
            $plz->appendChild(create_node($doc, 'plz', $service['plz']));
            $plz->appendChild(create_node($doc, 'city', $service['cityName']));
            $plz->appendChild(create_node($doc, 'deliverCost', (integer) $service['delcost'], 'dimension', 'cent'));
            $plz->appendChild(create_node($doc, 'minCost', (integer) $service['mincost'], 'dimension', 'cent'));
            $plz->appendChild(create_node($doc, 'deliverTime', (integer) $service['deliverTime'], 'dimension', 'seconds'));
            $plzElems->appendChild($plz);
        }
        $sElem->appendChild($plzElems);
        //append ratings
        $rElems = $doc->createElement('ratings');

        $rElems->appendChild(create_node($doc, 'advise', $service['ratingAdvisePercentPositive']));
        $total = $service['ratingQuality'] + $service['ratingDelivery'];
        $rElems->appendChild(create_node($doc, 'quality', (integer) $service['ratingQuality']));
        $rElems->appendChild(create_node($doc, 'delivery', (integer) $service['ratingDelivery']));
        $rElems->appendChild(create_node($doc, 'total', (integer) $total));
        $rElems->appendChild(create_node($doc, 'votes', (integer) count($serviceObj->getRating()->getList(null, true))));
        $rElems->appendChild(create_node($doc, 'title', ''));
        $rElems->appendChild(create_node($doc, 'comment', ''));
        $rElems->appendChild(create_node($doc, 'author', ''));
        $rElems->appendChild(create_node($doc, 'created', ''));

        $sElem->appendChild($rElems);
        //append tags
        $tagElems = $doc->createElement('tags');
        $tags = $table->getTags();
        foreach ($tags as $tag) {
            $tagElems->appendChild(create_node($doc, 'tag', $tag['tag']));
        }

        //append tags
        $sElem->appendChild($tagElems);

        return $sElem;
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        $this->logger->err(sprintf('API - SERVICE - createServiceChild: %s', $e->getMessage()));
        return null;
    }
}

//create doc element
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;
$root_element = $doc->createElement("response");
$doc->appendChild($root_element);
$versionElement = $doc->createElement("version");
$versionElement->appendChild($doc->createTextNode('1.1'));
$root_element->appendChild($versionElement);


$sElems = $doc->createElement('services');
$services = Yourdelivery_Model_Servicetype_Restaurant::all();
foreach ($services as $service) {
    try {
        $serviceElement = createServiceChild($doc, $service);
        if ($serviceElement instanceof DOMElement) {
            $sElems->appendChild($serviceElement);
        }
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        
    }
}
$root_element->appendChild($sElems);
$tmpfile = APPLICATION_PATH . '/temp/' . time() . '.xml';
$doc->save($tmpfile);
// TODO: create an helper for that shit

$conn = ftp_connect('IP');
if ($conn) {
    if (ftp_login($conn, 'USER', 'PASS')) {
        ftp_pasv($conn, true);

        if (!@ftp_chdir($conn, "export")) {
            ftp_mkdir($conn, "export");
            ftp_chdir($conn, "export");
        }

        if (!@ftp_chdir($conn, "get_service")) {
            ftp_mkdir($conn, "get_service");
            ftp_chdir($conn, "get_service");
        }

        if (!@ftp_chdir($conn, $domain)) {
            ftp_mkdir($conn, $domain);
            ftp_chdir($conn, $domain);
        }

        $put = ftp_put($conn, date('Y-m-d') . '.xml', $tmpfile, FTP_BINARY);
        if (!$put) {
            clog('err', 'failed to upload results for export "get_service"');
        }
    } else {
        clog('err', 'cannot login to the ftp server for export "get_service"');
    }
    ftp_close($conn);
} else {
    clog('err', 'cannot connect to the ftp server for export "get_service"');
}
