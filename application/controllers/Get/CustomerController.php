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
 *          a customer can get fidelity points for several actions all over the system
 *      </li>
 *      <li>
 *          for example a customer gets fidelity points for successfully processing an order,
 *          rating an order or uploading a profile image
 *      </li>
 *      <li>
 *          every response of API will return the structure
 *          <code>
 *              <fidelity>
 *                  <points>INTEGER</points>    (points the customer got for that action)
 *                  <message>STRING</message>   (translated message for customer)
 *              </fidelity>
 *          </code>
 *      </li>
 *      <li>
 *          if the amount of points is greater than 0 there will be a message for customer
 *      </li>
 *      <li>
 *          if amount of points is 0 customer didn't get any points for this action
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
 *          get - get information about customer including locations
 *      </li>
 *      <li>
 *          post - register or login a customer
 *      </li>
 *      <li>
 *          put - change data or reset password of customer
 *      </li>
 *  </ul>
 *
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 07.09.2010
 *
 * @modified Felix Haferkorn <haferkorn@lieferando.de>, 08.01.2012
 */
class Get_CustomerController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get information about customer including locations</li>
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
     *          <ACCESS>    (STRING)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <id>INTEGER</id>                                (customerId)
     *          <name>STRING</name>
     *          <prename>STRING</prename>
     *          <nickname>STRING</nickname>
     *          <email>STRING</email>
     *          <tel>STRING</tel>
     *          <picture>URL</picture>                              (URL of profile picture of customer)
     *          <gender>STRING</gender>                             ("m" = male / "w" = female / "n" = unspecified)
     *          <birthday>STRING</birthday>                         (format: YYYY-MM-DD)
     *          <fidelitypoints>INTEGER</fidelitypoints>            (total count of fidelity points of customer)
     *          <openactionpoints>INTEGER</openactionpoints>        (maximum amount of fidelity points customer can get for doing several actions)
     *          <locations>
     *              <location>
     *                  <id>INTEGER</id>            (locationId)
     *                  <street>STRING</street>
     *                  <hausnr>INTEGER</hausnr>    (street / house number)
     *                  <company>STRING</company>   (company name)
     *                  <etage>STRING</etage>       (floor)
     *                  <comment>STRING</comment>
     *                  <plz>STRING</plz>           (ZIP)
     *                  <cityId>INTEGER</cityId>
     *                  <city>STRING</city>         (city name)
     *              </location>
     *              ...
     *          </locations>
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
     *              curl "http://www.lieferando.local/get_customer/dc9aeffb7ef67068d1d19fb3d246060a"
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <customer>
     *              <id>1231</id>
     *              <name>Haferkorn</name>
     *              <prename>Felix</prename>
     *              <nickname>Felix</nickname>
     *              <email>haferkorn@yourdelivery.de</email>
     *              <tel>015140031777</tel>
     *              <picture></picture>
     *              <gender>m</gender>
     *              <birthday>0000-00-00</birthday>
     *              <fidelitypoints>0</fidelitypoints>
     *              <openactionpoints>23</openactionpoints>
     *              <locations>
     *                  <location>
     *                      <id>12470</id>
     *                      <street>Hammer Straße</street>
     *                      <hausnr>32</hausnr>
     *                      <company/>
     *                      <etage>1</etage>
     *                      <comment/>
     *                      <plz>16515</plz>
     *                      <cityId>959</cityId>
     *                      <city>Oranienburg</city>
     *                  </location>
     *                  ...
     *              </locations>
     *          </customer>
     *          <success>true</success>
     *          <message></message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>20</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - found customer</li>
     *      <li>404 - customer not found</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     */
    public function getAction() {

        try {
            $this->logger->info(sprintf('API - CUSTOMER - GET: Looking for customer by access %s', $this->getRequest()->getParam('id')));
            $customer = new Yourdelivery_Model_Customer(null, null, $this->getRequest()->getParam('id'));

            // add informations about customer
            $cElem = $this->doc->createElement('customer');
            $cElem->appendChild(create_node($this->doc, 'id', $customer->getId()));
            $cElem->appendChild(create_node($this->doc, 'name', $customer->getName()));
            $cElem->appendChild(create_node($this->doc, 'prename', $customer->getPrename()));
            $cElem->appendChild(create_node($this->doc, 'nickname', $customer->getNickname()));
            $cElem->appendChild(create_node($this->doc, 'email', $customer->getEmail()));
            $cElem->appendChild(create_node($this->doc, 'tel', $customer->getTel()));
            $cElem->appendChild(create_node($this->doc, 'picture', $customer->getProfileImage()));
            $cElem->appendChild(create_node($this->doc, 'gender', $customer->getSex()));
            $cElem->appendChild(create_node($this->doc, 'birthday', $customer->getBirthday()));

            // fidelity information
            $cElem->appendChild(create_node($this->doc, 'fidelitypoints', $customer->getFidelity() ? (integer) $customer->getFidelityPoints()->getPoints() : 0));
            $cElem->appendChild(create_node($this->doc, 'openactionpoints', $customer->getFidelity() ? (integer) $customer->getFidelity()->getOpenActionPoints() : 0));


            // append locations available
            $lElems = $this->doc->createElement('locations');
            foreach ($customer->getLocations() as $loc) {
                $lElem = $this->doc->createElement('location');
                $lElem->appendChild(create_node($this->doc, 'id', $loc->getId()));
                $lElem->appendChild(create_node($this->doc, 'prename', $customer->getPrename()));
                $lElem->appendChild(create_node($this->doc, 'name', $customer->getName()));
                $lElem->appendChild(create_node($this->doc, 'email', $customer->getEmail()));
                $lElem->appendChild(create_node($this->doc, 'street', $loc->getStreet()));
                $lElem->appendChild(create_node($this->doc, 'hausnr', $loc->getHausnr()));
                $lElem->appendChild(create_node($this->doc, 'company', $loc->getCompanyName()));
                $lElem->appendChild(create_node($this->doc, 'etage', $loc->getEtage()));
                $lElem->appendChild(create_node($this->doc, 'comment', $loc->getComment()));
                $lElem->appendChild(create_node($this->doc, 'plz', $loc->getPlz()));
                $lElem->appendChild(create_node($this->doc, 'cityId', $loc->getCityId()));
                $lElem->appendChild(create_node($this->doc, 'city', $loc->getOrt()->getOrt()));
                $lElem->appendChild(create_node($this->doc, 'primary', (integer) $loc->isPrimary()));
                $lElems->appendChild($lElem);
                unset($lElem);
            }

            $cElem->appendChild($lElems);
            $this->xml->appendChild($cElem);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn(sprintf('API - CUSTOMER - GET: could not find any customer by given access %s', $this->getRequest()->getParam('id')));
            $this->success = "false";
            $this->message = 'no access';
            return $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          register or login customer
     *      </li>
     *      <li>
     *          Post action to check if login information are correct. As a response,
     *          you get a location to the customer resource and an access key, which
     *          must be provided in some user related api calls, like editing locations
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters - Login:</b>
     *
     *  <code>
     *      type POST
     *      {
     *          email = STRING
     *          password = STRING
     *      }
     *  </code>
     *
     * <b>2.2. Parameters - Register</b>
     *
     *  <code>
     *      type JSON
     *      {
     *          "prename":"STRING",
     *          "name":"STRING",
     *          "email":"STRING",
     *          "password":"STRING",
     *          "tel":"STRING",
     *          "agb":"1"
     *          "nickname":"STRING", *
     *          "birthday":"YYYY-MM-DD", *
     *          "gender":"SINGLE CHAR (m / w)", *
     *      }
     *  </code>
     *  * = optional params
     *
     * -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <access href="STRING">STRING</access>
     *          <success>BOOLEAN</success>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Login - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -d email=haferkorn@lieferando.de -d password=testen -X POST http://www.lieferando.local/get_customer
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Login - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <access href="/get_customer/f28a353195eafca74edcc9d9e7270ead">f28a353195eafca74edcc9d9e7270ead</access>
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
     * <b>4.3. Example - Register - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -d register=1 -d parameters='{"name":"Haferkorn","prename":"Felix","email":"haferkorn@lieferando.com","password":"testen","tel":"015140031777","agb":"1"}' -X POST http://www.lieferando.local/get_customer
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.4. Example - Register - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <access href="/get_customer/a993e0cd363ad593b9d8132821cf8ad2">a993e0cd363ad593b9d8132821cf8ad2</access>
     *          <success>true</success>
     *          <message></message>
     *          <fidelity>
     *              <points>20</points>
     *              <message>Für Deine Registrierung erhältst Du 20 Treuepunkte</message>
     *          </fidelity>
     *          <memory>23</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <b>5.1. HTTP Response Codes - Login:</b>
     *
     *  <ul>
     *      <li>201 - authentication success</li>
     *      <li>403 - authentication failed</li>
     *  </ul>
     *
     *  <b>5.2. HTTP Response Codes - Register:</b>
     *
     *  <ul>
     *      <li>201 - successfully registration</li>
     *      <li>403 - invalid data</li>
     *      <li>404 - registration could not be completed</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 01.12.2011
     */
    public function postAction() {
        $request = $this->getRequest();

        if ($request->getParam('register', false)) {
            $post = $request->getPost();

            $this->logger->debug(sprintf('API - CUSTOMER - POST: posted params: %s', print_r($post, true)));

            $params = (array) json_decode($post['parameters'], true);
            $form = new Yourdelivery_Form_Api_UserRegister();

            if (!$form->isValid($params)) {
                $this->returnFormErrors($form->getMessages());
                return $this->getResponse()->setHttpResponseCode(403);
            }

            // correct some maybe-wrong values
            $params['sex'] = !empty($params['gender']) ? $params['gender'] == 'f' ? 'w' : $params['gender']  : 'n';

            $id = (integer) Yourdelivery_Model_Customer::add($params);

            if ($id <= 0) {
                $this->message = __('Beim anlegen des Benutzer ist ein Fehler aufgetreten');
                $this->logger->warn('API - CUSTOMER - POST: customer could not be created');
                return $this->getResponse()->setHttpResponseCode(403);
            }

            try {

                $customer = new Yourdelivery_Model_Customer($id);
                // set newsletter true per default
                $customer->setNewsletter(true);

                $email = new Yourdelivery_Sender_Email_Template('register');
                $email->setSubject(__('Registrierung auf %s', $this->config->domain->base));
                $email->addTo($customer->getEmail(), $customer->getFullname());
                $email->assign('cust', $customer);
                $email->assign('password', $params['password']);
                $email->send();

                $this->logger->info(sprintf('API - CUSTOMER - POST - REGISTER: successfully created new customer #%d %s (%s)', $customer->getId(), $customer->getFullname(), $customer->getEmail()));
                $this->getResponse()->setHttpResponseCode(201);

                // append fidelity information
                $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
                if ((boolean) $fidelityConfig->fidelity->enabled) {
                    $countPoints = $fidelityConfig->fidelity->points->register;
                    $this->fidelity_message = __('Für Deine Registrierung erhältst Du %d Treuepunkte', $countPoints);
                    $this->fidelity_points = $countPoints;
                }

                $this->xml->appendChild(create_node($this->doc, 'access', $customer->getSalt(), 'href', '/get_customer/' . $customer->getSalt()));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->message = __('Die Registrierung konnte nicht abgeschlossen werden.');
                return $this->getResponse()->setHttpResponseCode(404);
            }
        } else {
            $user = $request->getParam('email', null);
            $pass = $request->getParam('password', null);

            if ($user === null || $pass === null) {
                $this->message = __('Bitte gib E-Mail-Adresse und Passwort ein');
                $this->logger->warn('API - CUSTOMER - POST: customer authentication, no credentials provided');
                return $this->getResponse()->setHttpResponseCode(403);
            }

            //insert login values into auth adapter
            $this->auth->setIdentity($user)->setCredential($pass);
            //get result ...
            try {
                $result = $this->auth->authenticate();
                //... and check it
                switch ($result->getCode()) {

                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND: {
                            $this->logger->warn(sprintf('API - CUSTOMER - POST: could not validate customer - email %s not found', $user));
                            $this->message = __('Deine Zugangsdaten sind nicht korrekt.');
                            return $this->getResponse()->setHttpResponseCode(403);
                        }

                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID: {
                            $this->logger->warn(sprintf('API - CUSTOMER - POST: could not validate customer %s - password not correct', $user));
                            $this->message = __('Deine Zugangsdaten sind nicht korrekt.');
                            return $this->getResponse()->setHttpResponseCode(403);
                        }

                    case Zend_Auth_Result::SUCCESS: {
                            $this->logger->info(sprintf('API - CUSTOMER - POST: successfully authenticated customer %s', $result->getIdentity()));
                            $customer = new Yourdelivery_Model_Customer(null, $result->getIdentity());
                            $this->getResponse()->setHttpResponseCode(201);
                            $this->xml->appendChild(create_node($this->doc, 'access', $customer->getSalt(), 'href', '/get_customer/' . $customer->getSalt()));
                            break;
                        }
                }
            } catch (Exception $e) {
                $this->logger->err('API - CUSTOMER - POST: error while trying to authenticate customer - Exception: %s', $e->getMessage());
                $this->message = 'Could not validate user';
                return $this->getResponse()->setHttpResponseCode(403);
            }
        }
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          update data or reset password for customer
     *      </li>
     *      <li>
     *          you are not allowed to change the email-address to any existing email except the own one
     *      </li>
     *      <li>
     *          successfully reseting password will generate a random password for customer and send it to its email
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters - Reset password:</b>
     *
     *  <code>
     *      type JSON
     *      {
     *          "resetpassword":"STRING"    (emailaddress)
     *      }
     *  </code>
     *
     * <b>2.2. Parameters - Update data</b>
     *
     *  <code>
     *      type JSON
     *      parameters =
     *      {
     *          "access":"STRING",
     *          "prename":"STRING",
     *          "name":"STRING",
     *          "email":"STRING",
     *          "tel":"STRING"
     *          "password":"STRING", *
     *          "nickname":"STRING", *
     *          "birthday":"YYYY-MM-DD", *
     *          "gender":"SINGLE CHAR (m / w)", *
     *      }
     *  </code>
     *  * = optional params
     *
     * -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <success>BOOLEAN</success>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Reset password - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *               curl -d parameters='{"resetpassword":"haferkorn@lieferando.de"}' -X PUT http://www.lieferando.local/get_customer
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Reset password - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <success>true</success>
     *          <message>Ihr Passwort wurde geändert und an Ihre eMail-Adresse gesandt.</message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>22</memory>
     *      </response>
     *  </code>
     *
     *
     * <b>4.3. Example - Update data - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -d parameters='{"access":"dc9aeffb7ef67068d1d19fb3d246060a","name":"Haferkorn-neu","prename":"Felix-neu","email":"haferkorn@yourdelivery.de","password":"testen","tel":"015140031777"}' -X PUT http://www.lieferando.local/get_customer
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.4. Example - Update data - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <success>true</success>
     *          <message></message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>23</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>406 - invalid json provided</li>
     *      <li>403 - no valid data</li>
     *      <li>404 - password was not reseted</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 01.12.2011
     */
    public function putAction() {
        $post = $this->_getPut();
        if (!isset($post['parameters'])) {
            $parameters = $this->getRequest()->getParam('parameters', null);
            if ($parameters === null) {
                $this->logger->err('API - CUSTOMER - PUT: did not get any parameters');
                return $this->getResponse()->setHttpResponseCode(406);
            }
            $post['parameters'] = $parameters; //overwrite from get parameter, if put is not successful
        }
        $this->logger->debug(sprintf('API - CUSTOMER - PUT: posted params: %s', print_r($post['parameters'], true)));

        $json = json_decode($post['parameters']);
        if (is_object($json)) {
            if (!isset($json->resetpassword)) {
                try {

                    $customer = $this->_getCustomer($json);

                    $oldEmail = $customer->getEmail();

                    $params = (array) json_decode($post['parameters']);

                    $form = new Yourdelivery_Form_Api_UserSettings($customer->getId());

                    if (!$form->isValid($params)) {
                        $this->returnFormErrors($form->getMessages());
                        return $this->getResponse()->setHttpResponseCode(403);
                    }

                    // if ne email differs from existing, we have to migrate existing fidelity transactions
                    if (strtolower($json->email) != strtolower($oldEmail)) {
                        // migrate fidelity points / transactions
                        $customer->getFidelity()->migrateToEmail($json->email);
                    }
                    
                    $customer->setName($json->name);
                    $customer->setPrename($json->prename);
                    $customer->setEmail($json->email);
                    $customer->setTel($json->tel);
                    $customer->setNickname($json->nickname);
                    (isset($json->birthday) && strlen($json->birthday) > 0) ? $customer->setBirthday(date('Y-m-d', strtotime($json->birthday))) : null;
                    (isset($json->gender) && strlen($json->gender) > 0) ? $json->gender == 'f' ? $customer->setSex('w') : $customer->setSex($json->gender)  : null;
                    (isset($json->password) && strlen($json->password) > 0) ? $customer->setPassword(md5($json->password)) : null;
                    $customer->save();
                    
                    $this->logger->info(sprintf('API - CUSTOMER - PUT: successfully updated data for customer #%d %s', $customer->getId(), $customer->getFullname()));
                    $this->message = __('Deine Daten wurden erfolgreich gespeichert.');
                    return;
                } catch (Exception $e) {
                    $this->logger->err(sprintf('API - CUSTOMER - PUT:  could not update data because of Exception %s', $e->getMessage()));
                    $this->message = __('Deine Daten konnten nicht gesichert werden.');
                    return $this->getResponse()->setHttpResponseCode(403);
                }
                return;
            } else {

                $request = $this->getRequest();
                $post = $request->getPost();

                $status = null;
                $form = new Yourdelivery_Form_Request_NewPass();
                if ($form->isValid(array("email" => $json->resetpassword))) {

                    // reset password
                    $anonymous = new Yourdelivery_Model_Customer_Anonym();
                    $status = $anonymous->forgottenPass($json->resetpassword);
                    if ($status == 0) {
                        $this->message = __('Dein Passwort wurde geändert und an Deine E-Mail-Adresse gesendet.');
                        return;
                    }
                } else {
                    $this->logger->warn(sprintf('API - CUSTOMER - PUT:  could not reset password, because form is invalid (message %s)', implode(', ', $form->getErrorMessages())));
                    $this->message = __('Diese E-Mail-Adresse ist nicht gültig. Bitte gib eine korrekte ein.');
                    return $this->getResponse()->setHttpResponseCode(404);
                }
                $this->logger->warn(sprintf('API - CUSTOMER - PUT:  could not reset password, forgottenPass returned status %s', $status));
                $this->message = __('Diese E-Mail-Adresse ist nicht gültig. Bitte gib eine korrekte ein.');
                return $this->getResponse()->setHttpResponseCode(404);
            }
        }

        $this->logger->err('API - CUSTOMER - PUT: could not encode json');
        $this->message = 'no valid json provided';
        return $this->getResponse()->setHttpResponseCode(406);
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
     * this moethod is not used and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}

?>
