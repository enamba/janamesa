<?php

/**
 * @package Yourdelivery
 * @subpackage Connect
 */
require_once(APPLICATION_PATH . '/../library/Yourdelivery/Connect/Facebook/facebook.php');

/**
 * This call takes controll over the php sdk package from facebook and extends
 * its functionalities by those needed by the yourdelivery web app
 * https://github.com/facebook/php-sdk
 * http://developers.facebook.com/docs/reference/php/
 * @author mlaug
 */
class Yourdelivery_Connect_Facebook extends Facebook {
    
    protected $_logger = null;
    protected $_lastError = null;

    public function __construct() {
        $globalConfig = Zend_Registry::get('configuration');
        
        $this->app_id = $globalConfig->facebook->id;
        $this->app_secret = $globalConfig->facebook->secret;
        $this->app_url = $globalConfig->hostname;
        $config = array(
            'appId' => $this->app_id,
            'secret' => $this->app_secret,
            'cookie' => true
        );
        parent::__construct($config);
        
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
     * set last error
     * @author Jens Naie <naie@lieferando.de>
     * @since 09.07.2012
     * @return void
     */
    public function setError($error) {
        $this->_lastError = $error;
    }

    /**
     * get last error
     * @author Jens Naie <naie@lieferando.de>
     * @since 09.07.2012
     * @return void
     */
    public function getError() {
        return $this->_lastError;
    }
    
    /**
     * get/create a yourdelivery customer from facebook acount
     * 
     * <ul>
     *  <li>The given customer account has to be associated with the facebook Id</li>
     *  <li>The given email is not to be found in our database, so we create an account and associate with the facebook Id</li>
     *  <li>The given email has been found in our database
     *      <ul>
     *          <li>we check for an association and create one if not yet set</li>
     *          <li>if an association has been found it must match the facebook id</li>
     *          <li>if not matching the facebook id, we forbid the login due to possible fraud</li>
     *      </ul>
     * </ul>
     * @author mlaug
     * @since 21.10.2011
     * @return Yourdelivery_Model_Customer
     */
    public function getYourdeliveryUser($customer = null) {
        $user = $this->getUser();
        if (!$user) {
            $this->setError(__('Wir können keine Daten von Facebook ermitteln. Erlaubst Du Cookies?'));
            return null;
        }
        try{
            $data = $this->api('/me');
        }catch(Exception $e){
            $this->_logger->err($e->getMessage());
            $this->setError(__('Wir können keine Daten von Facebook ermitteln. Erlaubst Du Cookies?'));
            return null;
        }
        if ($data['email'] && $data['last_name']) {
            $table = new Yourdelivery_Model_DbTable_Customer();
            $row = $table->fetchRow(
                $table->select()->where('facebookId = ? AND deleted = 0', $user)
            );
            if (!$row) {
                $email = $data['email'];

                try {
                    //check if we already got that email address, if so add facebookId
                    if (!isset($customer)) {
                        $customer = new Yourdelivery_Model_Customer(null, $email);
                    }
                    if (strlen($customer->getFacebookId()) > 0 && $customer->getFacebookId() != $user) {
                        //but this means, someone has already associated this customer with a facebook acount
                        //that will be handled as fraud...
                        $this->setError(__('Der Account deiner Facebook E-Mail Adresse ist bereits mit einem anderen Facebook-Account verknüpft.'));
                        return null;
                    }
                    //associate with facebook acount
                    $customer->setFacebookId($user);
                    $customer->setLastLogin(date(DATETIME_DB));
                    if ((empty($row->birthday) || $row->birthday == '0000-00-00') && !empty($data['birthday'])) {
                        $customer->setBirthday($this->_getIsoDate($data['birthday']));
                    }
                    if ((empty($row->sex) || $row->sex == 'n') && !empty($data['gender'])) {
                        $customer->setSex(substr($data['gender'], 0, 1));
                    }
                    $customer->save();

                    $this->_logger->info(sprintf('successfully updated customer #%s %s via facebook-login - facebookId %s', $customer->getId(), $data['first_name'].' '.$data['last_name'], $user));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    //email has not been found
                    $customer = new Yourdelivery_Model_Customer();
                    $customerData = array(
                        'name' => $data['last_name'],
                        'prename' => $data['first_name'],
                        'email' => $email,
                        'lang' => substr($data['locale'], 0, 2),
                        'facebookId' => $user
                    );
                    if (!empty($data['birthday'])) {
                        $customerData['birthday'] = $this->_getIsoDate($data['birthday']);
                    }
                    if (!empty($data['gender'])) {
                        $customerData['sex'] = substr($data['gender'], 0, 1);
                    }
                    $customer->setData($customerData);
                    $customer->save();
                    $this->_logger->info(sprintf('successfully created customer #%s %s via facebook-login - facebookId %s', $customer->getId(), $data['first_name'].' '.$data['last_name'], $user));
                }
                if (empty($row->profileImage) && ($image = $this->getImage())) {
                    $customer->addImage($image, true);
                }
                $fidelityMessage = '<a href="http://www.facebook.com/' . $user . '">' . $data['first_name'] . ' ' . $data['last_name'] . '</a>';
                $customer->addFidelityPoint('facebookconnect', $fidelityMessage);
                return $customer;
            } else {
                if ($customer && $row->id != $customer->getId()) {
                    //this means, someone has already associated a customer with a facebook acount
                    //that will be handled as fraud...
                    $this->setError(__('Dein Facebook Profil ist bereits mit einem anderen Lieferando-Account verknüpft.'));
                    return null;
                }
                try {
                    $customer = new Yourdelivery_Model_Customer($row->id);
                    $this->_logger->info(sprintf('customer #%s %s already a facebook user - facebookId %s', $customer->getId(), $data['first_name'].' '.$data['last_name'], $user));
                    if (empty($row->email) && !empty($email)) {
                        $customer->setEmail($email);
                    }
                    if (empty($row->name) && !empty($data['last_name'])) {
                        $customer->setName($data['last_name']);
                    }
                    if (empty($row->prename) && !empty($data['first_name'])) {
                        $customer->setPrename($data['first_name']);
                    }
                    if (empty($row->lang) && !empty($data['first_name'])) {
                        $customer->setLang(substr($data['locale'], 0, 2));
                    }
                    if ((empty($row->birthday) || $row->birthday == '0000-00-00') && !empty($data['birthday'])) {
                        $customer->setBirthday($this->_getIsoDate($data['birthday']));
                    }
                    if ((empty($row->sex) || $row->sex == 'n') && !empty($data['gender'])) {
                        $customer->setSex(substr($data['gender'], 0, 1));
                    }
                    $customer->save();
                    return $customer;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->_logger->err(sprintf('Error connecting account to facebopok: %s'), $e->getMessage());
                    $this->setError(__('Dein Facebook Profil konnte nicht verknüpft werden.'));
                    return null;
                }
            }
        } else {
            $this->_logger->err(sprintf('Facebook connect without any data from facebook'));
            $this->setError(__('Dein Facebook Profil konnte nicht verknüpft werden.'));
            return null;
        }
    }

    /**
     * check if the current access token is still valid. Invalidation can be 
     * caused by different scenarios
     * <ul>
     *  <li>There is no facebook session what so ever</li>
     *  <li>The token expires after expires time (2 hours is the default).</li>
     *  <li>The user changes her password which invalidates the access token.</li>
     *  <li>The user de-authorizes your app.</li>
     *  </li>The user logs out of Facebook.</li>
     * </ul>
     * @author mlaug
     * @since 22.10.2011
     * @return boolean
     */
    public function isSessionValid() {
        try {
            $data = $this->api('/me');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Post a Link on the users wall
     * @author Jens Naie <naie@lieferando.de>
     * @since 09.07.2012
     * @return boolean
     */
    public function postOnUsersWall($name, $link, $message, $description = null, $picture = null, $icon = null) {
        try {
            $data = array(
                        'name' => $name,
                        'link' => $link,
                        'message' => $message
                    );
            if (isSet($description)) {
                $data['description'] = $description;
            }
            if (isSet($picture)) {
                $data['picture'] = $picture;
            }
            if (isSet($icon)) {
                $data['icon'] = $icon;
            }
            $result = $this->api('/me/feed', 'POST', $data);
            $this->_logger->info(sprintf('Customer %s posted a message on facebook', $name));
            return true;
        } catch (Exception $e) {
            $this->_logger->err(sprintf('Could not write on users facebook wall: %s', $e->getMessage()));
            return false;
        }
    }
    
    /**
     * get image of the current facebook user
     * @author Jens Naie <naie@lieferando.de>
     * @since 10.07.2012
     * @param string
     * @return string
     */
    public function getImage($pullTmpFile = true) {
        $user = $this->getUser();
        $headers = get_headers('https://graph.facebook.com/' . $user . '/picture?type=large', 1);
        if(isset($headers['Location'])) {
            if ($pullTmpFile) {
                $tmpfname = tempnam(sys_get_temp_dir(), 'YD_');
                file_put_contents($tmpfname, file_get_contents($headers['Location']));
                return $tmpfname;
            } else {
                return $headers['Location'];
            }
        } else {
            return false;
        }
    }

    /**
     * get ISO date from amarican dateform
     * @author Jens Naie <naie@lieferando.de>
     * @since 10.07.2012
     * @param string
     * @return string
     */
    private function _getIsoDate($date) {
        if (preg_match('|(\d\d)/(\d\d)/(\d\d\d\d)|', $date, $regs)) {
            return $regs[3] . '-' . $regs[1] . '-' . $regs[2];
        } else {
            return null;
        }
    }
}