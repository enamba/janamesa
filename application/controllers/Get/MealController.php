<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * Meal API
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 07.09.2010
 */
class Get_MealController extends Default_Controller_RestBase {

    /**
     * do some stuff before actions
     *
     * @return nothing
     */
    public function preDispatch() {
        //$this->enableCache();
        parent::preDispatch();
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          Get all inforamtion for a certian meal.
     *      </li>
     *      <li>
     *          You need to provide meal and size to get the informations
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
     *      type GET
     *      {
     *          <MEAL-ID>   INTEGER
     *          <SIZE-ID>   INTEGER
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <meal>
     *              <id>INTEGER</id>
     *              <size>INTEGER</size>
     *              <name>STRING</name>
     *              <image>STRING</image>
     *              <hasspecials>INTEGER</hasspecials>
     *              <category>STRING</category>
     *              <description>STRING</description>
     *              <cost>INTEGER</cost>
     *              <tax>INTEGER</tax>
     *              <excludefrommincost>BOOLEAN</excludefrommincost>    (if true meal is not included in minimum order cost)
     *              <mincount>INTEGER</mincount>                        (meal has to be ordered at least ... times)
     *              <extras>
     *                  ...
     *              </extras>
     *              <options>
     *                  <minChoice>INTEGER</minChoice> (minimum selected options, 0 means minChoices == choices)
     *                  <choices>INTEGER</choices>
     *                  ...
     *              </options>
     *          </meal>
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
     *          <li>http://www.lieferando.de/get_meal/61526?size=13014 (with options)</li>
     *          <li>http://www.lieferando.de/get_meal/257101?size=40034 (with options & extras)</li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <meal>
     *          <id>749622</id>
     *          <size>115512</size>
     *          <name>Vogue Caractère Bleue</name>
     *          <image>http://image.yourdelivery.de/lieferando.de/service/13796/categories/37849/meals/333099/Tai+Nigiri-150-150.jpg</image>
     *          <hasspecials>0</hasspecials>
     *          <category>Zigaretten</category>
     *          <description>Smoking kills ...</description>
     *          <cost>500</cost>
     *          <tax>19</tax>
     *          <excludefrommincost>BOOLEAN</excludefrommincost>
     *          <mincount>INTEGER</mincount>
     *          <extras></extras>
     *          <options></options>
     *      </meal>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - meal found</li>
     *      <li>404 - meal not found</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.09.2010
     *
     * @return HTTP-RESPONSE-CODE
     */
    public function getAction() {
        try {

            $id = (integer) $this->getRequest()->getParam('id', null);

            if ($id <= 0) {
                $this->message = 'No mealId provided';
                $this->success = "false";
                $this->logger->err('API - MEAL - GET: no id has been provided');
                return $this->getResponse()->setHttpResponseCode(404);
            }

            $meal = new Yourdelivery_Model_Meals($id);

            //we need a size
            $size = $this->getRequest()->getParam('size', null);
            if (is_null($size)) {
                $this->message = 'No size provided for meal';
                $this->success = "false";
                $this->logger->err('API - MEAL - GET: no size has been provided');
                return $this->getResponse()->setHttpResponseCode(404);
            }

            //set selected size
            $meal->setCurrentSize($size);

            //create xml element and collect data
            $mElem = $this->doc->createElement('meal');
            //create service node with some data
            $mElem->appendChild(create_node($this->doc, 'id', $meal->getId()));
            $mElem->appendChild(create_node($this->doc, 'size', $size));
            $mElem->appendChild(create_node($this->doc, 'name', stripslashes($meal->getName())));
            $mElem->appendChild(create_node($this->doc, 'image', $meal->getHasExistingPicture() ? $meal->getImg() : ''));
            $mElem->appendChild(create_node($this->doc, 'hasspecials', (integer) stripslashes($meal->hasSpecials())));
            $mElem->appendChild(create_node($this->doc, 'category', $meal->getCategory()->getName()));
            $mElem->appendChild(create_node($this->doc, 'description', stripslashes($meal->getDescription())));
            $mElem->appendChild(create_node($this->doc, 'cost', $meal->getCost()));
            $mElem->appendChild(create_node($this->doc, 'tax', $meal->getMwst()));
            $mElem->appendChild(create_node($this->doc, 'excludefrommincost', stripslashes((integer) $meal->getExcludeFromMinCost() || $meal->getCategory()->getExcludeFromMinCost())));
            $mElem->appendChild(create_node($this->doc, 'mincount', (integer) $meal->getMinAmount()));

            $options = $meal->getOptionsFast();
            $extrasGroup = $meal->getExtrasFast();

            // options
            $oElems = $this->doc->createElement('options');
            foreach ($options as $group) {
                $gElem = $this->doc->createElement('option');
                $gElem->appendChild(create_node($this->doc, 'name', $group['name']));
                $gElem->appendChild(create_node($this->doc, 'description', $group['description']));
                $gElem->appendChild(create_node($this->doc, 'choices', $group['choices']));
                $gElem->appendChild(create_node($this->doc, 'minChoices', $group['minChoices']));
                $alternatives = $this->doc->createElement('alternatives');
                foreach ($group['items'] as $option) {
                    $oElem = $this->doc->createElement('alternative');
                    $oElem->appendChild(create_node($this->doc, 'id', $option['oid']));
                    $oElem->appendChild(create_node($this->doc, 'name', $option['name']));
                    $alternatives->appendChild($oElem);
                    unset($oElem);
                }
                $gElem->appendChild($alternatives);
                $oElems->appendChild($gElem);
                unset($gElem);
            }

            // extras
            $eElems = $this->doc->createElement('extras');
            foreach ($extrasGroup as $extras) {
                foreach ($extras['items'] as $extra) {
                    $eElem = $this->doc->createElement('extra');
                    $eElem->appendChild(create_node($this->doc, 'id', $extra['id']));
                    $eElem->appendChild(create_node($this->doc, 'name', trim($extra['name'])));
                    $eElem->appendChild(create_node($this->doc, 'cost', $extra['cost']));
                    $eElems->appendChild($eElem);
                }
            }

            $mElem->appendChild($eElems);
            $mElem->appendChild($oElems);
            $this->xml->appendChild($mElem);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err(sprintf('API - MEAL - GET: could not find meal %d', $id));
            $this->success = "false";
            $this->message = 'meal not found';
            return $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * the index method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the post method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function postAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}
