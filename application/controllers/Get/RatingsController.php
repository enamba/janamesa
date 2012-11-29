<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          get ratings for order for customer
 *      </li>
 *      <li>
 *          post ratings for order
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          index - disallowed - 403
 *      </li>
 *      <li>
 *          delete - disallowed - 403
 *      </li>
 *      <li>
 *          get - get ratings for specified service
 *      </li>
 *      <li>
 *          post - rate an order
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 07.09.2010
 *
 * @modified Felix Haferkorn <haferkorn@lieferando.de>, 24.01.2012
 */
class Get_RatingsController extends Default_Controller_RestBase {

    /**
     * this method is called before every function call
     *
     * @return void
     */
    public function preDispatch() {
        $this->enableCache();
        parent::preDispatch();
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get the ratings of a specified service</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      type GET
     *      {
     *          <SERVICE-ID>    (INTEGER)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <ratings>
     *              <abritary>
     *                  <count>INTEGER</count>                  (total count of online ratings)
     *                  <advise>INTEGER[0-100]</advise>         (percentage of positive advises)
     *                  <qualityStars>FLOAT[1.00-5.00]</qualityStars> (number of stars to show for quality)
     *                  <quality5>INTEGER[0-100]</quality5>     (percentage of ratings with 5 stars for quality)
     *                  <quality4>INTEGER[0-100]</quality4>     (percentage of ratings with 4 stars for quality)
     *                  <quality3>INTEGER[0-100]</quality3>     ...
     *                  <quality2>INTEGER[0-100]</quality2>
     *                  <quality1>INTEGER[0-100]</quality1>
     *                  <deliveryStars>FLOAT[1.00-5.00]</deliveryStars>   (number of stars to show for delivery)
     *                  <delivery5>INTEGER[0-100]</delivery5>   (percentage of ratings with 5 stars for delivery)
     *                  <delivery4>INTEGER[0-100]</delivery4>   (percentage of ratings with 4 stars for delivery)
     *                  <delivery3>INTEGER[0-100]</delivery3>   ...
     *                  <delivery2>INTEGER[0-100]</delivery2>
     *                  <delivery1>INTEGER[0-100]</delivery1>
     *              </abritary>
     *              <individual>                                (individual ratings from customers)
     *                  <rating>
     *                      <quality>INTEGER[1-5]</quality>     (1 - 5 stars for quality)
     *                      <delivery>INTEGER[1-5]</delivery>   (1 - 5 stars for delivery)
     *                      <advise>BOOLEAN</advise>            (advise / thumb up)
     *                      <author>STRING</author>             (author of rating)
     *                      <time>TIMESTAMP</time>              (timestamp when rating was created)
     *                      <title>STRING</title>
     *                      <comment>STRING</comment>
     *                      <profileimage>STRING</profileimage> (URL of profile image of customer)
     *                  </rating>
     *                  ...
     *              </individual>
     *          </ratings>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              http://www.lieferando.local/get_ratings/16969
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <ratings>
     *              <abritary>
     *                  <count>1</count>
     *                  <advise>100</advise>
     *                  <qualityStars>4.38</qualityStars>
     *                  <quality5>0</quality5>
     *                  <quality4>0</quality4>
     *                  <quality3>0</quality3>
     *                  <quality2>0</quality2>
     *                  <quality1>100</quality1>
     *                  <deliveryStars>4.55</deliveryStars>
     *                  <delivery5>0</delivery5>
     *                  <delivery4>0</delivery4>
     *                  <delivery3>100</delivery3>
     *                  <delivery2>0</delivery2>
     *                  <delivery1>0</delivery1>
     *              </abritary>
     *              <individual>
     *                  <rating>
     *                      <quality>1</quality>
     *                      <delivery>3</delivery>
     *                      <advise>1</advise>
     *                      <author>Heidi</author>
     *                      <time>1342005035</time>
     *                      <title>this is a test title</title>
     *                      <comment>this is a test comment</comment>
     *                      <profileimage>http://cdn.yourdelivery.de/images/yd-profile/default_user.png</profileimage>
     *                  </rating>
     *              </individual>
     *          </ratings>
     *          <success>true</success>
     *          <message></message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>18</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - found service - list ratings</li>
     *      <li>404 - service not found</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.10.2010
     */
    public function getAction() {
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($this->getRequest()->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->success = false;
            $this->message = 'Service could not be found';
            return $this->getResponse()->setHttpResponseCode(404);
        }

        //get all ratings
        $ratings = $service->getRating()->getList(10, true, true);

        $rElems = $this->doc->createElement('ratings');

        //create abritary data, sum up everything
        $abritary = $this->doc->createElement('abritary');
        $abritary->appendChild(create_node($this->doc, 'count', (integer) count($service->getRating()->getList(null, true))));

        $abritary->appendChild(create_node($this->doc, 'advise', $service->getRatingAdvisePercentPositive()));
        
        $abritary->appendChild(create_node($this->doc, 'qualityStars', $service->getRatingQuality()));
        $abritary->appendChild(create_node($this->doc, 'quality5', $service->getRating()->getPercentRating(5, 'quality', true)));
        $abritary->appendChild(create_node($this->doc, 'quality4', $service->getRating()->getPercentRating(4, 'quality', true)));
        $abritary->appendChild(create_node($this->doc, 'quality3', $service->getRating()->getPercentRating(3, 'quality', true)));
        $abritary->appendChild(create_node($this->doc, 'quality2', $service->getRating()->getPercentRating(2, 'quality', true)));
        $abritary->appendChild(create_node($this->doc, 'quality1', $service->getRating()->getPercentRating(1, 'quality', true)));

        $abritary->appendChild(create_node($this->doc, 'deliveryStars', $service->getRatingDelivery()));
        $abritary->appendChild(create_node($this->doc, 'delivery5', $service->getRating()->getPercentRating(5, 'delivery', true)));
        $abritary->appendChild(create_node($this->doc, 'delivery4', $service->getRating()->getPercentRating(4, 'delivery', true)));
        $abritary->appendChild(create_node($this->doc, 'delivery3', $service->getRating()->getPercentRating(3, 'delivery', true)));
        $abritary->appendChild(create_node($this->doc, 'delivery2', $service->getRating()->getPercentRating(2, 'delivery', true)));
        $abritary->appendChild(create_node($this->doc, 'delivery1', $service->getRating()->getPercentRating(1, 'delivery', true)));

        $individual = $this->doc->createElement('individual');
        foreach ($ratings as $rating) {
            $rElem = $this->doc->createElement('rating');
            $rElem->appendChild(create_node($this->doc, 'quality', (integer) $rating['quality']));
            $rElem->appendChild(create_node($this->doc, 'delivery', (integer) $rating['delivery']));
            $rElem->appendChild(create_node($this->doc, 'advise', (boolean) $rating['advise']));
            $rElem->appendChild(create_node($this->doc, 'author', strlen($rating['author']) > 0 ? $rating['author'] : __('Unbekannt') ));
            $rElem->appendChild(create_node($this->doc, 'time', strtotime($rating['created'])));
            $rElem->appendChild(create_node($this->doc, 'title', $rating['title']));
            $rElem->appendChild(create_node($this->doc, 'comment', $rating['comment']));
            $rElem->appendChild(create_node($this->doc, 'profileimage', (!isset($rating['image']))  ? Yourdelivery_Model_Customer::DEFAULT_IMG : Yourdelivery_Model_Customer::createProfileImageUrl($rating['image'])));
            $individual->appendChild($rElem);
            unset($rElem);
        }

        $rElems->appendChild($abritary);
        $rElems->appendChild($individual);

        $this->xml->appendChild($rElems);
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          Use this post action to rate an order. As an identifier we use the
     *          hash of an order here to validate, that the customer is allowed to rate
     *          this order. The hash can be gathered from the GET Action of the Order API
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      type JSON
     *      parameters =
     *      {
     *          hash    : STRING         (hash of order)
     *          quality : INTEGER        (number of stars for quality of order [0-5])
     *          delivery: INTEGER        (number of stars for delivery of order [0-5])
     *          advise  : BOOLEAN        (user advise for order - 1 = positive ; 0 = negative)
     *          author  : STRING *       (author for rating)
     *          title   : STRING *       (title for rating)
     *          comment : STRING *       (comment for rating)
     *      }
     *  </code>
     *  * = optional params
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <success>BOOLEAN</success>
     *          <message>STRING</message>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -X POST -d parameters='{"author":"Felix","advise":"1","title":"Foo","quality":"3","comment":"Bar","hash":"7ed004ca4875205cb050974fbfc09ead","delivery":"5"}' http://www.lieferando.local/get_ratings
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <success>true</success>
     *          <message>succesfully rated</message>
     *          <fidelity>
     *              <points>2</points>
     *              <message>Für Deine Bewertung erhältst Du 2 Treuepunkte</message>
     *          </fidelity>
     *          <memory>10</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>201 - successfully rated order</li>
     *      <li>406 - invalid json / invalid values (message in response)</li>
     *      <li>404 - order not found by hash</li>
     *      <li>409 - order is rated already or ordertime is too far in the past</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.10.2010
     *
     * @return integer HTTP-RESPONSE-CODE
     */
    public function postAction() {
        $request = $this->getRequest();
        $post = $request->getPost();

        $json = json_decode($post['parameters']);
        if (!is_object($json)) {
            $this->logger->err('API - RATINGS - POST: could not encode json');
            $this->message = __('Empfange Daten sind im falschen Format.');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $params = (array) json_decode($post['parameters'], true);
        $form = new Yourdelivery_Form_Api_Rating();

        $order = Yourdelivery_Model_Order::createFromHash($params['hash']);

        if (!$order) {
            $this->logger->warn(sprintf('API - RATINGS - POST: could not find order by hash %s', $params['hash']));
            $this->success = 'false';
            $this->message = __('Bestellung wurde nicht gefunden.');
            return $this->getResponse()->setHttpResponseCode(404);
        }
        
        if (!$order->isRateable()) {
            $this->logger->warn(sprintf('API - RATINGS - POST: order #%s is not rateable', $order->getId()));
            $this->success = 'false';
            $this->message = __('Die Bestellung kann nicht oder nicht mehr bewertet werden.');
            return $this->getResponse()->setHttpResponseCode(409);
        }
        
        /*
         * copy some values for form
         * because we want to avoid duplicate code
         * so, the API form extends the frontend form
         */
        $params['rate-1'] = $params['quality'];
        $params['rate-2'] = $params['delivery'];

        if (!$form->isValid($params)) {
            $this->returnFormErrors($form->getMessages());
            return $this->getResponse()->setHttpResponseCode(406);
        }

        //rate this order
        $order->rate($order->getCustomer()->getId(), $params['quality'], $params['delivery'], $params['comment'], $params['title'], $params['advise'], $params['author']);
        $this->logger->info(sprintf('API - RATINGS - POST: successfully rated order #%s by customer #%s %s', $order->getId(), $order->getCustomer()->getId(), $order->getCustomer()->getFullname()));
        $this->message = __('Erfolgreiche Bewertung.');
        // fidelity points
        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        if ((boolean) $fidelityConfig->fidelity->enabled) {
            $countPoints = $fidelityConfig->fidelity->points->rate_low;
            //string must be greater 50, must contain more than 5 words and more than 10 unique chars
            if (strlen($params['comment']) >= 50 && count(array_unique(str_split($params['comment']))) > 10 && str_word_count($params['comment']) > 5 ) {
                $countPoints = $fidelityConfig->fidelity->points->rate_high;
            }
            $this->fidelity_message = __('Für Deine Bewertung erhältst Du %d Treuepunkte', $countPoints);
            $this->fidelity_points = $countPoints;
        }
        
        $order->getCustomer()->clearCache();
        $order->getCustomer()->getFidelity()->clearCache();
        
        return $this->getResponse()->setHttpResponseCode(201);
    }

    /**
     * the index method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}
