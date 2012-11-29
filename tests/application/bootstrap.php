<?php

error_reporting(E_ERROR);

//enable effective garbage collection to create codecoverage
gc_enable();

//define default timezone
date_default_timezone_set('Europe/Berlin');

//define locales
setlocale(LC_ALL, "de_DE.UTF-8");

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

defined('APPLICATION_PATH_TESTING')
        || define('APPLICATION_PATH_TESTING', realpath(dirname(__FILE__) . '/../../tests/application'));

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?
                        getenv('APPLICATION_ENV') : 'testing'));

defined('IS_PRODUCTION')
        || define('IS_PRODUCTION', APPLICATION_ENV == 'production');

defined('MAX_LOOPS') || define('MAX_LOOPS', 50);

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../library'),
            realpath(APPLICATION_PATH . '/../../library'),
            realpath(APPLICATION_PATH . '/../../models'),
            get_include_path()
        )));

/** Zend_Application */
//require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'Zend/Application.php';
require_once 'Zend/Test/PHPUnit/ControllerTestCase.php';
require_once 'Zend/Config.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Session/Namespace.php';
require_once APPLICATION_PATH . '/functions/functions.php';
require_once APPLICATION_PATH . '/functions/grid.function.php';
require_once APPLICATION_PATH_TESTING . '/../CustomizedAsserts.php';

/**
 * @author mlaug
 */
function isIndex($checkForParameters = true) {
    $uri = @parse_url($_SERVER['REQUEST_URI']);
    if (is_array($uri) && $uri['path'] == '/') {
        if (strstr(HOSTNAME, "elpedido") ||
                strstr(HOSTNAME, ".yourdelivery") ||
                strstr(HOSTNAME, ".lieferando") ||
                strstr(HOSTNAME, ".appetitos") ||
                strstr(HOSTNAME, ".smakuje") ||
                strstr(HOSTNAME, ".pyszne") ||
                strstr(HOSTNAME, ".eat-star") ||
                strstr(HOSTNAME, ".janamesa") ||
                strstr(HOSTNAME, ".taxiresto")) {

            if ($checkForParameters && count($_GET) == 0 && count($_POST) == 0) {
                return true;
            } elseif ($checkForParameters === false) {
                return true;
            }
        }
    }
    return false;
}

/**
 * @author mlaug
 * @since 03.05.2011
 * @return boolean
 */
function isBaseUrl() {
    $config = Zend_Registry::get('configuration');
    $domain = $config->domain->base;
    if ($config->domain->www_redirect->enabled == 1) {
        return strpos(HOSTNAME, $domain) || strpos(HOSTNAME, '.yourdelivery');
    } else {
        return strstr(HOSTNAME, $domain) || strstr(HOSTNAME, 'yourdelivery');
    }
}

/**
 * Base class for our tests with some helper functions
 * @package test
 * @author mlaug
 */
abstract class Yourdelivery_Test extends Yourdelivery_Customized_Asserts {

    /**
     * @var Zend_Application
     */
    protected $application;
    protected $config;
    protected $_logger = null;

    /**
     * @author mlaug
     */
    public function setUp() {
        define('HOSTNAME', 'jenkins.yourdelivery.local');
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    /**
     * call some logic after each TestCase
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.03.2012
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * @author mlaug
     */
    public function appBootstrap() {
        $this->application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->application->bootstrap();
        $this->config = Zend_Registry::get('configuration');
        $this->_logger = Zend_Registry::get('logger');

        // Define hostname
        defined('HOSTNAME')
                || define('HOSTNAME', $this->config->domain->base);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.06.2012
     */
    public function dataProviderCacheNoCache() {
        return array(
            array(1),
            array(0));
    }

    /**
     * set config to use or not use memcache
     *
     * @param boolean | integer $enabled
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.06.2012
     */
    public function setUsingCache($enabled) {
        $config = Zend_Registry::get('configuration');
        $config->cache->use = (integer) $enabled;
    }

    /**
     * create a discount for any testcase you want (valid for 1 day)
     *
     * @param boolean $rrepeat
     * @param integer $kind
     * @param integer $rabatt
     * @param boolean $onlyPrivate
     * @param boolean $onlyCompany
     * @param boolean $onlyCustomer
     * @param boolean $onlyPremium
     * @param timestamp $start
     * @param timestamp $end
     * @param boolean $noCash
     * @param boolean $onlyIphone
     *
     * @return Yourdelivery_Model_Rabatt_Code
     */
    public function createDiscount(
            $rrepeat = false, 
            $kind = 0, 
            $rabatt = 10, 
            $onlyPrivate = false, 
            $onlyCompany = false, 
            $onlyCustomer = false, 
            $onlyPremium = false, 
            $onlyCanteen = false, 
            $start = null, 
            $end = null, 
            $noCash = true, 
            $onlyIphone = false, 
            $minAmount = 10, 
            $type = 0) {
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'TestingDiscount-' . time(),
                    'rrepeat' => $rrepeat,
                    'kind' => $kind,
                    'rabatt' => $rabatt,
                    'status' => true,
                    'start' => !is_null($start) ? $start : date('Y-m-d H:i:s', strtotime('-1minute')),
                    'end' => !is_null($end) ? $end : date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'onlyPremium' => $onlyPremium,
                    'onlyCustomer' => $onlyCustomer,
                    'onlyCompany' => $onlyCompany,
                    'onlyPrivate' => $onlyPrivate,
                    'noCash' => $noCash,
                    'onlyIphone' => $onlyIphone,
                    'minAmount' => $minAmount,
                    'type' => $type
                )
        );
        $discount->save();
        $codes = new Yourdelivery_Model_DbTable_RabattCodes();
        $code = $discount->getId() . Default_Helper::generateRandomString();
        $codes->insert(
                array(
                    'code' => $code,
                    'rabattId' =>
                    $discount->getId()
                )
        );
        return new Yourdelivery_Model_Rabatt_Code($code);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 2011
     * @modified fhaferkorn, 05.10.2011
     *
     * @param integer $amount
     * @return Yourdelivery_Model_Rabatt_Code
     */
    public function createFidelityDiscount($amount = 800) {
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'Testcase (Eingelöste Treuepunkte) ' . date('d.m.Y. H:i'),
                    'info' => '(Eingelöste Treuepunkte)',
                    'rrepeat' => false,
                    'kind' => 1,
                    'rabatt' => $amount,
                    'status' => true,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'onlyPrivate' => true
                )
        );
        // mark as fidelity
        $discount->setFidelity(true);
        $discount->save();
        return new Yourdelivery_Model_Rabatt_Code($discount->generateCode());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.11.2010
     * @param $email
     * @param $pass
     */
    public function login($email, $pass) {
        $session = new Zend_Session_Namespace('Default');
        $this->dispatch('/');
        $this->resetResponse();
        $this->getRequest()->setPost(array(
            'user' => $email,
            'pass' => $pass,
            'login' => 1
        ));
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/user/login');
        $customer = new Yourdelivery_Model_Customer($session->customerId);
        $this->assertTrue($customer->isLoggedIn());
        $this->assertEquals($email, $customer->getEmail());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.11.2010
     */
    public function logout() {
        $this->dispatch('/user/logout');
        $this->assertRedirectTo('/');
    }

    /**
     * place an order and return the order object
     *
     * @param array $args set your arguments here like array('payment' => 'bar', 'mode' => 'rest', ...)
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.04.2011
     *
     * @return ingeter orderId
     *
     *
     * OLD PARAMS
     *
     * $payment = 'bar',
     * $discount = true,
     * Yourdelivery_Model_Customer $customer = null,
     * Yourdelivery_Model_Location $location = null,
     * $totalAbove = null,
     * $kind = 'priv',
     * $mode = 'rest',
     * $faxService = null,
     * $serviceAcceptsBarPayment = null,
     * $serviceAcceptsOnlinePayment = null,
     * $premium = null,
     * $service = null
     */
    protected function placeOrder(array $args = null) {
        $order = null;
        isset($args['mode']) ? null : $args['mode'] = 'rest';
        isset($args['kind']) ? null : $args['kind'] = 'priv';
        isset($args['payment']) ? null : $args['payment'] = 'bar';
        isset($args['deliverTime']) ? null : $args['deliverTime'] = __('sofort');

        if ($args['kind'] == 'comp') {
            $order = new Yourdelivery_Model_Order_Company();
            $order->setup($this->getRandomCustomerCompany(), $args['mode']);
            $this->assertEquals('comp', $order->getKind());
        } else {
            $order = new Yourdelivery_Model_Order_Private();
            $order->setup(new Yourdelivery_Model_Customer_Anonym(), $args['mode']);
            $this->assertEquals('priv', $order->getKind());
        }

        //set current domain
        $order->setDomain(HOSTNAME);
        isset($args['uuid']) ? $order->setUuid($args['uuid']) : null;
        
        //check if everyting is setup correctly
        $this->assertEquals($args['mode'], $order->getMode());
        $this->assertTrue(strlen($order->getSecret()) == 20);
        $this->assertTrue($order->getCustomer() instanceof Yourdelivery_Model_Customer_Abstract);

        if (isset($args['customer']) && $args['customer'] instanceof Yourdelivery_Model_Customer_Abstract) {
            $order->setCustomer($args['customer']);
        } else {
            // create unique email
            $email = 'Testperson' . time() . '-' . rand(1, 99) . '@testmail.de';
            $order->getCustomer()->setPrename('Samson');
            $order->getCustomer()->setName('Tiffy');
            $order->getCustomer()->setEmail($email);
            $order->getCustomer()->setTel(rand(12345678, 87654321));
        }

        isset($args['premium']) ? null : $args['premium'] = null;
        isset($args['notify']) ? null : $args['notify'] = 'fax';
        isset($args['faxService']) ? null : $args['faxService'] = 'retarus';
        isset($args['serviceOnlinePayment']) ? null : $args['serviceOnlinePayment'] = null;
        isset($args['serviceBarPayment']) ? null : $args['serviceBarPayment'] = null;
        $args['serviceOnlinePayment'] || isset($args['discount']) || $args['payment'] != 'bar' ? $onlinePayment = true : $onlinePayment = false;

        // add location
        if (isset($args['location']) && $args['location'] instanceof Yourdelivery_Model_Location) {
            if (!isset($args['service'])) {
                $args['service'] = $this->getRandomService(array(
                    'onlinePayment' => $onlinePayment,
                    'barPayment' => $args['serviceBarPayment'],
                    'plz' => $args['location']->getPlz(),
                    'fax' => $args['faxService'],
                    'premium' => $args['premium'],
                    'notify' => $args['notify']
                        ));
            }
        } else {
            if (!isset($args['service'])) {
                $args['service'] = $this->getRandomService(array(
                    'onlinePayment' => $onlinePayment,
                    'barPayment' => $args['serviceBarPayment'],
                    'fax' => $args['faxService'],
                    'premium' => $args['premium'],
                    'notify' => $args['notify']
                        ));
            }

            $args['location'] = new Yourdelivery_Model_Location();
            $range = $this->getRandomDeliverRange($args['service']);
            $args['location']->setCityId($range['cityId']);
            $args['location']->setStreet('Chauseestraße');
            $args['location']->setHausnr('86');
            $args['location']->setTel(rand(12345678, 87654321));
            $args['location']->setCompanyName('Samson & Tiffy GmbH');
            $args['location']->setComment('ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ');
            $args['location']->setEtage(rand(1, 999));
        }

        //set deliver time
        $order->setDeliverTime($args['deliverTime'], 0);

        //append current location
        $order->setLocation($args['location']);

        $order->setService($args['service']);

        //check for ranges
        $this->assertTrue($order->addressInRange($args['location']->getCity()->getId()));

        if (!isset($args['meals'])) {
            //add meals
            $opt_ext = array(
                'extras' => array(),
                'options' => array(),
                'special' => 'testcase'
            );

            $options = null;
            // first meal
            $meal1 = $this->getRandomMealFromService($args['service']);
            $size1 = $this->getRandomMealSize($meal1);
            $meal1->setCurrentSize($size1->getId());
            $costMeal1 = $meal1->getAllCosts();
            $options = $meal1->getOptions();

            $this->assertEquals(count($order->addMeal($meal1, $opt_ext, 3)), 2);

            // second meal
            $meal2 = $this->getRandomMealFromService($args['service']);
            $size2 = $this->getRandomMealSize($meal2);
            $meal2->setCurrentSize($size2->getId());
            $costMeal2 = $meal2->getAllCosts();
            $this->assertEquals(count($order->addMeal($meal2, $opt_ext, 2)), 2);

            $bucketTotal = ($costMeal1 * 3) + ($costMeal2 * 2);

            $this->assertTrue($order->getBucketTotal() > 0);
            $this->assertEquals($order->getBucketTotal(), $bucketTotal);

            $i = 0;

            if (!isset($args['totalAbove'])) {
                // add meals until reaching mincost
                while ($i++ <= MAX_LOOPS && $args['service']->getMinCost($args['location']->getCityId()) > $order->getBucketTotal(null, true)) {
                    $meal3 = $this->getRandomMealFromService($args['service']);
                    $size3 = $this->getRandomMealSize($meal3);
                    $meal3->setCurrentSize($size3->getId());
                    $costMeal3 = $meal3->getAllCosts();
                    $this->assertEquals(count($order->addMeal($meal3, $opt_ext, 3)), 2);
                    $bucketTotal += ( $costMeal3 * 3);
                }
            } else {
                // add meals until reaching mincost and given totalAbove
                while ($i++ <= MAX_LOOPS && $args['service']->getMinCost($args['location']->getCityId()) > $order->getBucketTotal(null, true) || $order->getBucketTotal(null, true) <= $args['totalAbove']) {
                    $meal3 = $this->getRandomMealFromService($args['service']);
                    $size3 = $this->getRandomMealSize($meal3);
                    $meal3->setCurrentSize($size3->getId());
                    $costMeal3 = $meal3->getAllCosts();
                    $this->assertEquals(count($order->addMeal($meal3, $opt_ext, 3)), 2);
                    $bucketTotal += ( $costMeal3 * 3);
                }
            }
        }else{
            foreach ($args['meals'] as $meal) {
                $this->assertInstanceof('Yourdelivery_Model_Meals', $meal['meal']);
                $this->assertEquals(count($order->addMeal($meal['meal'], $meal['opt_ext'], $meal['count'])), 2);
            }
        }

        // check abstotal
        $deliverCost = $order->getService()->getDeliverCost();

        if ($args['kind'] == 'comp' && $args['mode'] == 'rest') {
            // calculate budget of customerCompany
            $customer = $order->getCustomer();
            $ownBudget = $order->getCustomer()->getCurrentBudget();
            $leftOvers = $deliverCost + $bucketTotal - $ownBudget;
            if ($leftOvers < 0) {
                $leftOvers = 0;
            }
            $this->assertEquals($leftOvers, $order->getAbsTotal(false));
        } else {
            if(!isset($args['meals'])){
                $this->assertEquals($order->getAbsTotal(false), $deliverCost + $bucketTotal);
            }
        }

        if (isset($args['discount'])) {
            if ($args['discount'] === true && !($args['discount'] instanceof Yourdelivery_Model_Rabatt_Code)) {
                $discount = $this->createDiscount();
                $order->setDiscount($discount);
                $this->assertTrue($order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);
                $this->assertNotEquals($order->getAbsTotal(), $order->getAbsTotal(true, false));
            } elseif ($args['discount'] > 0 && !($args['discount'] instanceof Yourdelivery_Model_Rabatt_Code)) {
                $discount = new Yourdelivery_Model_Rabatt_Code(null, $args['discount']);
                $order->setDiscount($discount);
                $this->assertTrue($order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);
                $this->assertNotEquals($order->getAbsTotal(), $order->getAbsTotal(true, false));
            } elseif ($args['discount'] instanceof Yourdelivery_Model_Rabatt_Code) {
                $order->setDiscount($args['discount']);
                $this->assertTrue($order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);
                $this->assertNotEquals($order->getAbsTotal(), $order->getAbsTotal(true, false));
            }
        }

        $possiblePayments = array('bar', 'ebanking', 'credit', 'paypal');
        while (!Yourdelivery_Helpers_Payment::allowPayment($order, $args['payment'])) {
            $desiredPayment = $args['payment']; 
            $args['payment'] = array_pop($possiblePayments);
            if ($args['payment'] === null) {
                throw new Exception(sprintf('Could not find a possbile payment method for service #%s with payment %s', $order->getService()->getId(), $desiredPayment));
            } 
       }
        $order->setPayment($args['payment']);

        $this->assertTrue($order->finish(), Default_Helpers_Log::getLastLog());

        //finalize order to invalidate discount
        if (!isset($args['finalize']) || $args['finalize'] == true) {
            isset($args['checkForFraud']) ? null : $args['checkForFraud'] = true;
            $order->finalizeOrderAfterPayment($args['payment'], false, false, false, $args['checkForFraud']);
        }
        return $order->getId();
    }

    /**
     * test if xml file for this fax exists
     * @author alex
     * @since 02.02.2011
     */
    public function isRetarusFaxOK($orderId) {
        $filepattern = $orderId . '-ordersheet';
        $datePart = date('d-m-Y', time());
        $handler = @opendir(APPLICATION_PATH . '/../storage/orders/' . $datePart);

        $found = false;

        //loop over all files in the directory
        while ($file = readdir($handler)) {
            if (strpos($file, $filepattern) === 0) {
                closedir($handler);
                return true;
            }
        }
        closedir($handler);
        return false;
    }

    /**
     * test if database entry for this fax-transaction exists
     * @author alex
     * @since 02.02.2011
     */
    public function isInterfaxFaxOK($orderId) {
        $it_table = new Yourdelivery_Model_DbTable_Interfax_Transactions();
        $row = $it_table->getByOrder($orderId);

        return $row['transactionId'] > 0;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.02.2011
     * @return Yourdelivery_Model_Customer
     */
    public function createCustomer() {
        $customer = new Yourdelivery_Model_Customer();
        $customer->setData(array(
            'name' => Default_Helper::generateRandomString(),
            'prename' => 'Tester',
            'email' => Default_Helper::generateRandomString(19) . "@testmail" . Default_Helper::generateRandomString(5) . ".de",
            'password' => md5('samsontiffy'),
            'sex' => 'm',
            'tel' => Default_Helper::generateRandomString(10)
        ));
        $this->assertTrue($customer->save() !== false);
        return $customer;
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     *
     * @return Yourdelivery_Model_Company
     */
    public function createCompany() {
        $cityId = $this->getRandomCityId();
        $city = new Yourdelivery_Model_City($cityId);

        $data = array(
            'name' => 'Test GmbH',
            'industry' => 'testindustry',
            'street' => 'Schillerstraße',
            'hausnr' => '13',
            'cityId' => $cityId,
            'plz' => $city->getPLz()
        );

        $company = new Yourdelivery_Model_Company();
        $company->setData($data);
        $company->save();

        $this->assertTrue($company instanceof Yourdelivery_Model_Company);
        return $company;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.08.2011
     *
     * @param Yourdelivery_Model_Company $company | null
     * @return Yourdelivery_Model_Customer_Company $employee
     */
    public function getRandomCustomerCompany($company = null, $withCurrentBudget = null) {
        if ($company instanceof Yourdelivery_Model_Company) {
            $companyAdditon = ' AND cc.companyId = ' . $company->getId() . ' ';
        } else {
            $companyAdditon = ' ';
        }

        if (is_null($withCurrentBudget)) {
            $budgetTimeAdditon = ' ';
        } elseif ($withCurrentBudget) {
            // todo: take care of orders, customer made today
            $budgetTimeAdditon = sprintf(' AND cbt.amount > 0 AND "%s" BETWEEN cbt.from AND cbt.until', date('H:m:i'));
        } elseif (!$withCurrentBudget) {
            $budgetTimeAdditon = sprintf(' AND cbt.amount <= 0 AND "%s" NOT BETWEEN cbt.from AND cbt.until', date('H:m:i'));
        }

        $db = Zend_Registry::get('dbAdapter');
        $sql = 'SELECT c.id, cc.companyId FROM customers c
                    JOIN customer_company cc ON cc.customerId = c.id
                    JOIN companys co ON co.id = cc.companyId
                    JOIN company_budgets cb ON cb.id = cc.budgetId
                    JOIN company_budgets_times cbt ON cbt.budgetId = cb.id
                WHERE
                    c.deleted = 0
                    AND co.status = 1
                    ' . $companyAdditon . '
                    ' . $budgetTimeAdditon . '
                ORDER BY RAND() LIMIT 1';
        $row = $db->fetchRow($sql);

        if (!$row) {
            throw new Exception(sprintf('Could not find random customer for company'));
        }

        $customerCompany = new Yourdelivery_Model_Customer_Company($row['id'], $row['companyId']);
        $this->assertInstanceof('Yourdelivery_Model_Customer_Company', $customerCompany);

        return $customerCompany;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.03.2011
     * @return Yourdelivery_Model_Location
     */
    public function createLocation($plz = null, $cityId = null, $customerId = null) {
        $location = new Yourdelivery_Model_Location();
        if (!is_null($plz)) {
            $city = Yourdelivery_Model_DbTable_City::findByPlz($plz);
            $location->setCityId($city[0]['id']);
        }

        if (!is_null($cityId)) {
            $city = new Yourdelivery_Model_City($cityId);
            $location->setCityId($city->getId());
        }

        if (is_null($plz) && is_null($cityId)) {
            $location->setCityId($this->getRandomCityId());
        }

        if (!is_null($customerId)) {
            $location->setCustomer(new Yourdelivery_Model_Customer($customerId));
        }

        $location->setStreet('Chauseestraße');
        $location->setHausnr('86');
        $location->setTel('017620142163');
        $location->save();
        $this->assertTrue($location->isPersistent());
        return $location;
    }

    /**
     * get random company model from db
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.04.2011
     * @return Yourdelivery_Model_Company
     */
    public function getRandomCompany($excludeDeleted = true, $online = true, $contact = false, $except = null) {
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('companys', array('id'))->order('RAND()')->limit(1);

        if ($excludeDeleted) {
            $select->where('deleted=0');
        }
        if ($online) {
            $select->where('status=1');
        }
        if ($contact) {
            $select->where('contactId > 0');
        }

        if (!is_null($except)) {
            $select->where('id not in (?)', $except);
        }

        $row = $db->fetchRow($select);
        $company = new Yourdelivery_Model_Company($row['id']);

        $this->assertTrue($company instanceof Yourdelivery_Model_Company);
        $this->assertTrue($company->isPersistent());

        return $company;
    }

    /**
     * get random customer model from db
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.04.2011
     *
     * @param boolean $withCompany
     * @param boolean $withLocation
     * @param boolean $withDiscount
     * @param boolean $withUserRights
     * @param integer $withAtLeastXOrders
     *
     * @return Yourdelivery_Model_Customer | Yourdlivery_Model_Customer_Company
     */
    public function getRandomCustomer($withCompany = null, $withLocation = null, $withDiscount = false, $withUserRights = null, $withAtLeastXOrders = null) {
        // make sure, that there is at least one customer
        // $this->createCustomer();
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(array('c' => 'customers'), array('customerId' => 'c.id'))->where('c.deleted = 0')->where('c.tel IS NOT NULL')->order('RAND()')->limit(1);

        if ($withLocation === true) {
            $select->join(array('l' => 'locations'), 'l.customerId = c.id', array())->where('l.deleted = 0');
        }

        if ($withLocation === false) {
            $select->joinLeft(array('l' => 'locations'), 'l.customerId = c.id', array())->where('l.id IS NULL');
        }

        if ($withCompany === true) {
            $select->from(array(), array('companyId' => 'cc.companyId'))
                    ->join(array('cc' => 'customer_company'), 'cc.customerId = c.id', array())
                    ->join(array('comp' => 'companys'), 'comp.id = cc.companyId', array())
                    ->join(array('budg' => 'company_budgets'), 'budg.id = cc.budgetId', array())
                    ->where('comp.deleted = 0');
        }

        if ($withCompany === false) {
            $select->joinLeft(array('cc' => 'customer_company'), 'cc.customerId = c.id', array())->where('cc.id IS NULL');
        }

        if ($withDiscount === true) {
            /**
             * TAKE CARE:
             * if there is no customer with random discount in database,
             * you will get empty result
             * MAYBE you should create one first
             */
            $select->join(array('rc' => 'rabatt_codes'), 'rc.id = c.permanentDiscountId', array())
                    ->join(array('r' => 'rabatt'), 'r.id = rc.rabattId')
                    ->where('r.status = 1')
                    ->where('? BETWEEN r.start and r.end', date('Y-m-d H:i:s'));
        }

        if ($withDiscount === false) {
            $select->where('c.permanentDiscountId IS NULL');
        }

        if ($withAtLeastXOrders > 0) {
            $select->join(array('oc' => 'orders_customer'), 'oc.email = c.email', array())
                    ->having('count(oc.id) >= ?', $withAtLeastXOrders)
                    ->group('c.id');
        }

        // option for user_rights
        if (!is_null($withUserRights)) {
            $select->joinLeft(array('ur' => 'user_rights'), 'ur.customerId = c.id');
            if (!$withUserRights) {
                $select->where('ur.id IS NULL');
            } else {
                $select->where('ur.id IS NOT NULL');
            }
        }

        $row = $db->fetchRow($select);

        if ($withCompany === true) {
            $customer = new Yourdelivery_Model_Customer_Company($row['customerId'], $row['companyId']);
        } else {
            $customer = new Yourdelivery_Model_Customer($row['customerId']);
        }

        $this->assertTrue($customer instanceof Yourdelivery_Model_Customer_Abstract);
        $this->assertTrue($customer->isPersistent());
        return $customer;
    }

    /**
     * get a random service
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.04.2011
     * @modify afrank@15-03-12
     * @param array $args set your arguments here like array('online' => true, 'type' => 1, ...)
     * @return Yourdelivery_Model_Servicetype_Abstract
     *
     * OLD PARAMS:
     *
     * $online = true,
     * $type = null,
     * $onlinePayment = null,
     * $barPayment = null,
     * $plz = null,
     * $deleted = false,
     * $fax = null,
     * $premium = false,
     * $excludeCourier = false,
     * $avanti = false,
     * $notify = null
     *
     *
     */
    public function getRandomService(array $args = null) {
        isset($args['online']) ? null : $args['online'] = true;
        isset($args['type']) ? null : $args['type'] = 1;
        isset($args['onlinePayment']) ? ($args['onlinePayment'] != true ? $args['barPayment'] = true : null) : $args['onlinePayment'] = null;
        isset($args['barPayment']) ? null : $args['barPayment'] = null;
        isset($args['plz']) ? null : $args['plz'] = null;
        isset($args['deleted']) ? null : $args['deleted'] = false;
        isset($args['fax']) ? null : $args['fax'] = null;
        isset($args['premium']) ? null : $args['premium'] = false;
        isset($args['excludeCourier']) ? null : $args['excludeCourier'] = false;
        isset($args['withCourier']) ? null : $args['withCourier'] = false;
        isset($args['avanti']) ? null : $args['avanti'] = false;
        isset($args['hasOrders']) ? null : $args['hasOrders'] = false;
        isset($args['hasOrdersThisMonth']) ? $args['hasOrders'] = true : $args['hasOrdersThisMonth'] = false;

        $args['deleted'] == true ? $args['online'] = false : null;
        $db = Zend_Registry::get('dbAdapter');
        $deadLockPreventer = 0;

        do {
            $query = $db->select()
                    ->from(array("rp" => "restaurant_plz"), array('r.id'))
                    ->join(array('rs' => 'restaurant_servicetype'), "rs.restaurantId = rp.restaurantId", array())
                    ->join(array('r' => 'restaurants'), "r.id = rp.restaurantId", array())
                    ->join(array('rf' => 'restaurant_franchisetype'), "r.franchiseTypeId = rf.id", array())
                    ->where('r.id not in (select restaurantId from restaurant_company)')
                    ->where('rp.status = 1')
                    ->order('RAND()')
                    ->limit(1);

            if ($args['excludeCourier']) {
                $query->joinLeft(array('cr' => 'courier_restaurant'), "r.id = cr.restaurantId", array());
                $query->where('cr.courierId IS NULL');
            }

            if ($args['withCourier']) {
                $query->join(array('cr' => 'courier_restaurant'), "r.id = cr.restaurantId", array());
            }

            if (isset($args['type'])) {
                $query->where('rs.servicetypeId = ?', $args['type']);
            }

            if (isset($args['online'])) {
                if ($args['online']) {
                    $query->where('isOnline = 1');
                } else {
                    $query->where('isOnline = 0');
                }
            }

            if (isset($args['deleted'])) {
                if ($args['deleted']) {
                    $query->where('deleted > 0');
                } else {
                    $query->where('deleted = 0');
                }
            }

            if (isset($args['plz'])) {
                $query->where('rp.plz = ?', $args['plz']);
            }

            if (isset($args['onlinePayment'])) {
                if ($args['onlinePayment']) {
                    $query->where('r.onlycash = 0');
                } else {
                    $query->where('r.onlycash = 1');
                }
            }

            if (isset($args['barPayment'])) {
                if ($args['barPayment']) {
                    $query->where('r.paymentbar = 1');
                } else {
                    $query->where('r.paymentbar = 0');
                }
            }

            if (isset($args['fax']) && in_array($args['fax'], array('retarus', 'interfax'))) {
                $query->where('r.faxService = ?', $args['fax']);
            }

            if (isset($args['notify'])) {
                $query->where('r.notify = ?', $args['notify']);
            }

            if ($args['premium']) {
                $query->where('rf.name = ?', 'Premium');
            }

            if ($args['avanti']) {
                $query->where('rf.name = ?', 'Pizza AVANTI');
            }

            if (isset($args['deliverCityId'])) {
                $query->where('rp.cityId = ?', $args['deliverCityId']);
                $query->where('rp.delcost > 0');
            }

            if (isset($args['restaurantCityId'])) {
                $query->where('r.cityId = ?', $args['restaurantCityId']);
            }

            if (isset($args['withAdmin'])) {
                $query->join(array('ur' => 'user_rights'), 'ur.kind = "r" AND ur.refId = r.id');
            }

            if ($args['hasOrders']) {
                $query->join(array('o' => 'orders'), "r.id = o.restaurantId", array());
                if ($args['hasOrdersThisMonth']) {
                    $query->where('MONTH(o.time) = MONTH(NOW())');
                    switch ($args['type']) {
                        default:
                        case 1:
                            $query->where('o.mode = ?', 'rest');
                            break;
                        case 2:
                            $query->where('o.mode = ?', 'cater');
                            break;
                        case 3:
                            $query->where('o.mode = ?', 'great');
                            break;
                    }
                }
            }

            $query->group('r.id');

            # echo $query->__toString();die;

            $restaurantId = $db->fetchOne($query);

            $queryMeals = $db->select()
                    ->from(array("m" => "meals"), array('m.id'))
                    ->join(array('mc' => 'meal_categories'), "m.categoryId= mc.id", array())
                    ->where("m.restaurantId = ?", $restaurantId);

            $mealsCount = count($db->fetchAll($queryMeals));

            $deadLockPreventer++;
        } while (($mealsCount == 0) && ($deadLockPreventer < 30));

        if ($deadLockPreventer == 30) {
            $this->assertEquals("", "Konnte nach dreisig Versuchen kein Restaurant mit Speisen finden :(");
        }

        $service = null;
        switch ($args['type']) {
            default:
            case 1:
                $service = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
                break;
            case 2:
                $service = new Yourdelivery_Model_Servicetype_Cater($restaurantId);
                break;
            case 3:
                $service = new Yourdelivery_Model_Servicetype_Great($restaurantId);
                break;
        }

        //if there is no correlation in restaurant_printer_topup, create one
        if (in_array($args['notify'], array('sms', 'smsemail')) && is_null($service->getSmsPrinter())) {
            $printerId = $db->fetchRow('select id from printer_topup where online > 0 order by rand() limit 1');
            $printer = new Yourdelivery_Model_DbTable_Restaurant_Printer();
            $printer->insert(array(
                'restaurantId' => $restaurantId,
                'printerId' => (integer) $printerId['id']
            ));
        }
        return $service;
    }

    /**
     * get a random picture category
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.12.2011
     * @return Yourdelivery_Model_Category_Picture
     */
    public function getRandomPictureCategory() {
        $db = Zend_Registry::get('dbAdapter');
        $picCategoryId = $db->fetchOne('SELECT `id` FROM `category_picture` ORDER BY RAND() LIMIT 1');
        return new Yourdelivery_Model_Category_Picture($picCategoryId);
    }

    /**
     * get random deliver range of service
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.04.2011
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @return array (restaurant_plz)
     */
    public function getRandomDeliverRange(Yourdelivery_Model_Servicetype_Abstract $service) {
        $ranges = $service->getRanges(1000, $service->isPremium());
        shuffle($ranges);
        return $ranges[0];
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.04.2011
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @param boolean $with_options
     * @return Yourdelivery_Model_Meals
     */
    public function getRandomMealFromService(Yourdelivery_Model_Servicetype_Abstract $service, $options = null) {
        $allMeals = $service->getMeals();
        if (count($allMeals) <= 0) {
            throw new Exception(sprintf('No meal found for Service #%s %s', $service->getId(), $service->getName()));
        }

        shuffle($allMeals);

        foreach ($allMeals as $m) {

            $meal = new Yourdelivery_Model_Meals($m['id']);
            if (!$meal->isPersistent()) {
                throw new Exception(sprintf('WTF - Meal not persistant - Service #%s %s - ShuffleMeal #%s', $service->getId(), $service->getName(), $m['id']));
            }

            if ($meal->isExcludeFromMinCost() || $meal->getCategory()->isExcludeFromMinCost()) {
                continue;
            }

            $size = $this->getRandomMealSize($meal);

            try {
                $meal->setCurrentSize($size->getId());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }

            if (is_null($options)) {
                return $meal;
            }

            if (!$options) {
                if (!$meal->hasOptions()) {
                    return $meal;
                }
            }

            if ($options === true) {
                if ($meal->hasOptions()) {
                    return $meal;
                }
            }
        }
        throw new Exception(sprintf('No random meal with params (options: %s) found for service #%s %s', is_null($options) ? 'null' : $options ? 'true' : 'false', $service->getId(), $service->getName()));
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 21.04.2011
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @param boolean $with_extras
     * @return Yourdelivery_Model_Meals
     */
    public function getRandomMealFromServiceWithExtras(Yourdelivery_Model_Servicetype_Abstract $service, $extras = true) {

        $allMeals = $service->getMeals();
        $i = 0;
        if (count($allMeals) <= 0) {
            throw new Exception(sprintf('No meal found for Service #%s %s', $service->getId(), $service->getName()));
        }

        shuffle($allMeals);
        $last_item = end($allMeals);

        foreach ($allMeals as $m) {
            $i++;
            $meal = new Yourdelivery_Model_Meals($m['id']);
            if (!$meal->isPersistent()) {
                throw new Exception(sprintf('WTF - Meal not persistant - Service #%s %s - ShuffleMeal #%s', $service->getId(), $service->getName(), $m['id']));
            }

            if ($meal->isExcludeFromMinCost() || $meal->getCategory()->isExcludeFromMinCost()) {
                continue;
            }

            $size = $this->getRandomMealSize($meal);

            try {
                $meal->setCurrentSize($size->getId());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }

            if (is_null($extras)) {
                return $meal;
            }

            if ($extras === true) {
                if ($meal->hasExtras()) {
                    return $meal;
                }
            }

            if ($m == $last_item or $i >= 25) {
//                $this->_logger->debug('Could not find any extras, so create one');
//                $this->_logger->debug('meg:'.$service->getId());
                $mealsExtraGroup = new Yourdelivery_Model_DbTable_Meal_ExtrasGroups();
                $mealExtraGroupId = $mealsExtraGroup->insert(
                        array(
                            'internalName' => 'internalTestName',
                            'name' => 'testGroupName',
                            'restaurantId' => $service->getId()
                        )
                );

//                $this->_logger->debug('me:'.$mealExtraGroupId);
                $mealExtra = new Yourdelivery_Model_DbTable_Meal_Extras();
                $mealExtraId = $mealExtra->insert(
                        array(
                            'name' => 'testExtraName',
                            'mwst' => 7,
                            'groupId' => $mealExtraGroupId
                        )
                );
//                $this->_logger->debug('mer:'.$mealExtraId.'|'.$meal->getId().'|'.$meal->getCurrentSize());
                $mealExtraRelation = new Yourdelivery_Model_DbTable_Meal_ExtrasRelations();
                $mealExtraRelation->insert(
                        array(
                            'extraId' => $mealExtraId,
                            'mealId' => $meal->getId(),
                            'sizeId' => $meal->getCurrentSize()
                        )
                );

                $meal->clearData();
                $mealReloaded = new Yourdelivery_Model_Meals($meal->getId());
                $mealReloaded->setCurrentSize($meal->getCurrentSize());
                return $mealReloaded;
            }
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.04.2011
     *
     * @param Yourdelivery_Model_Meals $meal
     *
     * @return Yourdelivery_Model_Meal_Size
     */
    public function getRandomMealSize(Yourdelivery_Model_Meals $meal) {
        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from(array('m' => 'meals'))
                ->join(array('msnn' => 'meal_sizes_nn'), 'msnn.mealId = m.id')
                ->join(array('ms' => 'meal_sizes'), 'ms.id = msnn.sizeId')
                ->where('m.id = ?', $meal->getId())
                ->where('msnn.sizeId > 0')
                ->order('RAND()')
                ->limit(1);
        $row = $db->fetchRow($select);
        return new Yourdelivery_Model_Meal_Sizes($row['sizeId']);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2011
     *
     * @param Yourdelivery_Model_Meals $meal
     *
     * @return Yourdelivery_Model_Meal_Option
     */
    public function getRandomMealOption(Yourdelivery_Model_Meals $meal) {
        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from(array('m' => 'meals'))
                ->join(array('msnn' => 'meal_sizes_nn'), 'msnn.mealId = m.id')
                ->join(array('ms' => 'meal_sizes'), 'ms.id = msnn.sizeId')
                ->where('m.id = ?', $meal->getId())
                ->where('msnn.sizeId > 0')
                ->order('RAND()')
                ->limit(1);
        $row = $db->fetchRow($select);
        return new Yourdelivery_Model_Meal_Sizes($row['sizeId']);
    }

    /**
     * get a valid cityId from this database and return
     * and check first if there is a valid service
     * @author mlaug, fhaferkorn
     * @since 11.04.2011
     * @return int
     */
    public function getRandomCityId($with_restaurants = true) {
        $db = Zend_Registry::get('dbAdapter');
        $thausandPlzs = $db->fetchAll('select c.id from city c inner join restaurant_plz rp on rp.cityId=c.id inner join restaurants r on r.id=rp.restaurantId where rp.status=1 and r.isOnline=1 LIMIT 1000');
        shuffle($thausandPlzs);
        return (integer) $thausandPlzs[0]['id'];
    }

    /**
     * get a valid plz from this database and return
     * @author mlaug
     * @since 11.04.2011
     * @param boolean $with_restaurants
     * @return array
     */
    public function getRandomPlz($with_restaurants = true) {
        $db = Zend_Registry::get('dbAdapter');
        $thausandPlzs = $db->fetchAll('select * from city c inner join restaurant_plz rp on rp.cityId=c.id inner join restaurants r on r.id=rp.restaurantId where rp.status=1 and r.isOnline=1 LIMIT 1000');
        shuffle($thausandPlzs);
        return array(
            'plz' => $thausandPlzs[0]['plz'],
            'cityId' => $thausandPlzs[0]['cityId']
        );
    }

    /**
     * @author mpantar
     * @since 13-04-11
     * @return Yourdelivery_Model_Administratior
     */
    public function createRandomAdministrationUser() {
        $db = Zend_Registry::get('dbAdapter');

        $sql = "SELECT id from admin_access_users WHERE groupId = 1 ORDER BY RAND() LIMIT 1";
        $row = $db->fetchRow($sql);
        if ($row) {
            return new Yourdelivery_Model_Admin($row['id']);
        }

        $user = Default_Helper::generateRandomString(19) . "@testmail" . Default_Helper::generateRandomString(5) . ".de";
        $admin = new Yourdelivery_Model_Admin();
        $admin->setData(array(
            'name' => 'Admin tester',
            'email' => $user,
            'password' => md5('admin'),
            'groupId' => '1'
        ));
        $admin->save();
        return $admin;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.08.2011
     * @param string $name
     * @return array
     */
    public function getContactByName($name) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "contacts"))
                ->where("c.name = ?", $name);
        return $db->fetchRow($query);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.08.2011
     * @param string $name
     * @return array
     */
    public function getCompanyByName($name) {
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.name = ?", $name);
        return $db->fetchRow($query);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.08.2011
     * @param int $id
     * @return array
     */
    public function getCompanyById($id) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("c" => "companys"))
                ->where("c.id = ?", $id);
        return $db->fetchRow($query);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.08.2011
     * @return Yourdelivery_Model_Contact
     */
    public function getRandomContact($has_email = true) {
        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from(array("c" => "contacts"));

        if ($has_email) {
            $select->where("c.email != 0");
        } else {
            $select->where("c.email = 0");
        }

        $stmt = $db->query($select);
        $all = $stmt->fetchAll();
        shuffle($all);
        return new Yourdelivery_Model_Contact($all[0]['id']);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>, fhaferkorn
     * @since 08.09.2011
     * @return Yourdelivery_Model_Courier
     */
    public function getRandomCourier() {
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow('SELECT `id` FROM `courier` ORDER BY RAND() LIMIT 1');

        $courier = new Yourdelivery_Model_Courier($row['id']);
        $this->assertTrue($courier instanceof Yourdelivery_Model_Courier);
        $this->assertTrue($courier->isPersistent());

        return $courier;
    }

    /**
     *
     * Random Rabatt Codes für Customer
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return array
     */
    public function getCustomerRabattCodes() {
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from(
                        array('rc' => 'rabatt_codes'), array(
                    'rid' => 'rc.id',
                    'code' => 'rc.code'
                ))
                ->joinLeft(array('r' => 'rabatt'), "rc.rabattId=r.id")
                ->where('r.onlyCustomer=1')
                ->where('rc.used=0')
                ->where('r.end>=now()');

        $fields = $db->fetchAll($select);

        shuffle($fields);

        if ((strtotime($fields[0]['end']) <= time()) || is_null($fields)) {
            $discount = $this->createDiscount(0, 0, 10, false, false, true);
            $fields[0]['rid'] = $discount->getRabattId();
            $fields[0]['code'] = $discount->getCode();
            $fields[0]['id'] = $discount->getId();
        }
        return $fields;
    }

    public function dispatchPost($url, $post) {
        if (empty($url) || empty($post)) {
            throw new Exception("Empty Parameters in dispatchPost!");
        }

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch($url);
    }

    public function getCityIdByCourierId($cid) {
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from(array('c' => 'courier_plz'), array('c.cityId')
                )->where('c.courierId = ?', $cid);
        $result = $db->fetchAll($select);
        $plzs = array();
        foreach ($result as $id) {
            $plzs[] = $id['cityId'];
        }
        return $plzs;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.09.2011
     * @param Yourdelivery_Model_Customer $customer | null
     * @return Yourdelivery_Model_Location
     */
    public function getRandomLocation($customerId = null, $exceptCity = null) {
        $db = $this->_getDbAdapter();

        $select = $db->select()->from(array('l' => 'locations'), array('locationId' => 'l.id'))->where('deleted = 0')->order('RAND()')->limit(1);

        if (!is_null($customerId)) {
            $select->where('customerId = ?', $customerId);
        }

        if (!is_null($exceptCity)) {
            $select->join(array('c' => 'city'), 'c.id = l.cityId')->where('c.city not like "%?%"', $exceptCity);
        }

        $row = $db->fetchRow($select);

        if (!$row) {
            if (!is_null($customerId)) {
                return $this->createLocation(null, null, $customerId);
            }
            return $this->createLocation(null, null);
        }
        return new Yourdelivery_Model_Location($row['locationId']);
    }

    /**
     * Get a valid bill from this database and return
     * @author vpriem
     * @since 07.06.2011
     * @return int
     */
    protected function _getRandomBillId($mode = 'rest', $template = null, $verbose = null, $showProject = null, $showCostcenter = null, $showEmployee = null, $projectSub = null, $costcenterSub = null) {
        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()
                ->from(array('b' => "billing"), array("id"))
                ->where("b.refId NOT IN (1260, 1271, 1270, 1295, 1443)")
                ->where("b.mode = ?", $mode)
                ->order(new Zend_Db_Expr("RAND()"))
                ->limit(50);

        if ($mode == 'company') {
            $select->where('amount < 100000');
        }

        if ($template !== null
                || $verbose !== null
                || $showProject !== null
                || $showCostcenter !== null
                || $showEmployee !== null
                || $projectSub !== null
                || $costcenterSub !== null) {
            $select->join(array('bc' => "billing_customized"), "b.refId = bc.refId", array());
        }

        if ($template !== null) {
            $select->where("bc.template = ?", $template);
        }

        if ($verbose !== null) {
            $select->where("bc.verbose = ?", $verbose);
        }

        if ($showProject !== null) {
            $select->where("bc.showProject = ?", $showProject);
        }

        if ($showCostcenter !== null) {
            $select->where("bc.showCostcenter = ?", $showCostcenter);
        }

        if ($showEmployee !== null) {
            $select->where("bc.showEmployee = ?", $showEmployee);
        }

        if ($projectSub !== null) {
            $select->where("bc.projectSub = ?", $projectSub);
        }

        if ($costcenterSub !== null) {
            $select->where("bc.costcenterSub = ?", $costcenterSub);
        }
        return $db->fetchOne($select);
    }

    /**
     * Get a valid inventory from this database and return
     * @author vpriem
     * @since 15.06.2011
     * @return Yourdelivery_Model_Inventory
     */
    protected function _getRandomInventory() {
        $db = Zend_Registry::get('dbAdapter');
        $id = $db->fetchOne(
                "SELECT i.id
            FROM `inventory` i
            ORDER BY RAND()
            LIMIT 1"
        );
        return new Yourdelivery_Model_Inventory(1);
    }

    /**
     * Get random printer
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.05.2012
     * @return Yourdelivery_Model_Printer_Abstract
     */
    protected function _getRandomPrinter() {
        $db = Zend_Registry::get('dbAdapter');
        $id = $db->fetchOne(
                "SELECT pt.id
            FROM `printer_topup` pt
            ORDER BY RAND()
            LIMIT 1"
        );
        return Yourdelivery_Model_Printer_Abstract::factory($id);
    }

    protected function createCity($parentCityId = null) {
        $city = new Yourdelivery_Model_City();
        $city->setData(array(
            'plz' => '99999',
            'city' => 'testcaseCity-' . Default_Helper::generateRandomString(10),
            'state' => 'testcaseState-' . Default_Helper::generateRandomString(10),
            'stateId' => rand(1, 20),
            'parentCityId' => (integer) $parentCityId
        ));
        return $city->save();
    }

    /**
     * Random PayerID from Black/Whitelist Table
     * @param string $white
     * @return string
     */
    public function getRandomPayerId($white = "whitelist") {
        $db = Zend_Registry::get('dbAdapter');

        $results = $db->query($db->select()
                                ->from('blacklist_values')
                                ->where('behaviour = ?', $white)
                                ->where('type = "payerId"')
                )->fetchAll();

        shuffle($results);
        return $results[0]['value'];
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.12.2011
     *
     * @param Yourdelivery_Model_Servicetype_Abstract $restaurant
     * @param integer $limit
     * @param boolean $withCourier
     *
     * @return array
     */
    public function getRandomRange(Yourdelivery_Model_Servicetype_Abstract $restaurant, $limit = 10000, $withCourier = false) {
        $ranges = $restaurant->getRanges($limit, $withCourier);
        shuffle($ranges);
        return $ranges[0];
    }

    /**
     * @author Feli Haferkorn
     * @since 25.01.2012
     *
     * @return Yourdelivery_Model_Rabatt
     */
    public function createNewCustomerDiscount(array $params = null) {
        $discount = new Yourdelivery_Model_Rabatt();
        $id = $discount->setData(array(
                    'name' => 'Test New Customer Discount ' . time(),
                    'rrepeat' => 0,
                    'kind' => 1,
                    'rabatt' => 1000,
                    'status' => 1,
                    'referer' => 'referer-' . time() . '-' . rand(1, 500),
                    'type' => isset($params['type']) ? $params['type'] : 2,
                    'start' => isset($params['start']) ? $params['start'] : date('Y-m-d H:i:s', strtotime('-1minute')),
                    'end' => isset($params['end']) ? $params['end'] : date('Y-m-d H:i:s', time() + 60 * 60 * 24),
                    'onlyPremium' => false,
                    'onlyCustomer' => false,
                    'onlyCompany' => false,
                    'onlyPrivate' => false,
                    'noCash' => true
                ))->save();
        $discount = new Yourdelivery_Model_Rabatt($id);
        $discount->setData(array('hash' => $discount->makeHash()));
        $discount->save();

        $this->assertGreaterThan(0, $id);
        $discount->generateCode(null, 15, null, true);

        return $discount;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 01.02.2012
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter() {
        return Zend_Registry::get('dbAdapter');
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     *
     * @return Yourdelivery_Model_Customer_Company $employee
     */
    public function createCustomerCompanyRelation() {
        $customer = $this->createCustomer();
        $company = $this->createCompany();

        $budget = new Yourdelivery_Model_Budget();
        $budget->setData(array('companyId' => $company->getId(), 'name' => 'testBudget' . time()));
        $budget->save();

        $cc = new Yourdelivery_Model_Customer_Company();
        $cc->setData(
                array('customerId' => $customer->getId(), 'companyId' => $company->getId(), 'budgetId' => $budget->getId(), 'status' => 1)
        );
        return Yourdelivery_Model_DbTable_Customer_Company::add(array('email' => $customer->getEmail(), 'budget' => $budget->getId()), $company->getId());
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.02.2012
     *
     * @return Yourdelivery_Model_Servicetype_Rating
     */
    public function createRating($order, array $args = null) {
        $rating = new Yourdelivery_Model_Servicetype_Rating();
        $rating->setData(
                array(
                    'orderId' => $order->getId(),
                    'quality' => isset($args['quality']) ? $args['quality'] : rand(1, 5),
                    'delivery' => isset($args['delivery']) ? $args['delivery'] : rand(1, 5),
                    'customerId' => $order->getCustomerId(),
                    'restaurantId' => $order->getRestaurantId(),
                    'advise' => isset($args['advise']) ? $args['advise'] : rand(1, 0),
                    'status' => isset($args['status']) ? $args['status'] : 0,
                    'author' => 'Test Author - ' . Default_Helper::generateRandomString(10),
                    'comment' => 'comment - ' . Default_Helper::generateRandomString(10)
                )
        );
        $rating->save();
        return $rating;
    }

    /**
     * open a service for this order, if not open and recreated object
     * to avoid cashing problems
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param Yourdelivery_Model_Order_Abstract $order
     */
    protected function openService($service, $time) {

        // clear all special openings first
        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
        $table->delete(sprintf('restaurantId = %d', $service->getId()));

        // clear holiday opening
        $openingsTable = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        $openingsTable->delete(sprintf('restaurantId = %d and day = 10', $service->getId()));

        // add special opening
        $service->getOpening()->addSpecialOpening(array(
            'specialDate' => date('Y-m-d', $time),
            'closed' => '0',
            'from' => "00:00:00",
            'until' => "24:00:00"
        ));

        $this->assertTrue($service->getOpening()->isOpen($time), sprintf('service #%d is not open at %s, although it should be, because of special opening', $service->getId(), date('Y-m-d H:i:s', $time)));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.02.2012
     *
     * @todo make it faster
     */
    public function getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount() {
        $service = $this->getRandomService(array('onlinePayment' => true, 'barPayment' => true));
        $range = $this->getRandomRange($service);
        $minAmount = $range['mincost'];

        $db = Zend_Registry::get('dbAdapter');

        do {
            $row = $db->fetchRow('
                SELECT m.id AS `mealId`, msnn.sizeId AS `sizeId`
                FROM meals m
                JOIN meal_sizes_nn msnn ON msnn.mealId = m.id
                JOIN meal_categories mc ON (mc.restaurantId = ' . $service->getId() . ' AND mc.id = m.categoryId)
                WHERE m.restaurantId = ' . $service->getId() . '
                    AND m.excludeFromMinCost = 0
                    AND mc.excludeFromMinCost = 0
                    AND (msnn.cost * 3) >= ' . $minAmount . '
                    ORDER BY RAND() LIMIT 1
                    ');
            $deadLockPreventer++;
        } while (!$row && ($deadLockPreventer < 30));

        $row['cityId'] = $range['cityId'];

        $row['sizeId'] = $row['sizeId'];
        $row['plz'] = $range['plz'];
        $row['restaurantId'] = $service->getId();
        $row['rangeId'] = $range['id'];

        $meal = new Yourdelivery_Model_Meals($row['mealId']);
        $this->assertInstanceof('Yourdelivery_Model_Meals', $meal);
        if (!$meal->isPersistent()) {
            return $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        }
        $this->assertIsPersistent($meal);
        return array($service, $meal, $row);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.02.2012
     *
     * @todo make it faster
     */
    public function getRandomServiceWithDeliverRangeAndMealBelowMinamount() {
        $service = $this->getRandomService(array('onlinePayment' => true, 'barPayment' => true));
        $range = $this->getRandomRange($service);
        $minAmount = $range['mincost'];

        if ($minAmount < 1000) {
            return $this->getRandomServiceWithDeliverRangeAndMealBelowMinamount();
        }

        $db = Zend_Registry::get('dbAdapter');

        do {
            $row = $db->fetchRow('
                SELECT m.id AS `mealId`, msnn.sizeId AS `sizeId`
                FROM meals m
                JOIN meal_sizes_nn msnn ON msnn.mealId = m.id
                JOIN meal_categories mc ON (mc.restaurantId = ' . $service->getId() . ' AND mc.id = m.categoryId)
                WHERE m.restaurantId = ' . $service->getId() . '
                    AND m.excludeFromMinCost = 0
                    AND mc.excludeFromMinCost = 0
                    AND msnn.cost < ' . $minAmount . '
                    ORDER BY RAND() LIMIT 1
                    ');
            $deadLockPreventer++;
        } while (!$row && ($deadLockPreventer < 30));

        $row['cityId'] = $range['cityId'];

        $row['sizeId'] = $row['sizeId'];
        $row['plz'] = $range['plz'];
        $row['restaurantId'] = $service->getId();
        $row['rangeId'] = $range['id'];

        $meal = new Yourdelivery_Model_Meals($row['mealId']);
        $this->assertInstanceof('Yourdelivery_Model_Meals', $meal);
        if (!$meal->isPersistent()) {
            return $this->getRandomServiceWithDeliverRangeAndMealBelowMinamount();
        }
        $this->assertIsPersistent($meal);
        return array($service, $meal, $row);
    }

    /**
     * get random satellite model
     *
     * @return \Yourdelivery_Model_Satellite
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.04.2012
     */
    public function getRandomSatellite() {
        $dbTable = new Yourdelivery_Model_DbTable_Satellite();
        $allSatellites = $dbTable->fetchAll('disabled = 0')->toArray();
        shuffle($allSatellites);

        return new Yourdelivery_Model_Satellite($allSatellites[0]['id']);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 24.04.2012
     *
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    public function createRestaurant() {
        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $name = 'Test Restaurant ' . time() . '-' . rand(12345, 541236);

        $data = array(
            'name' => $name,
            'street' => 'Teststraße',
            'hausnr' => '13',
            'cityId' => $city->getId(),
            'plz' => $city->getPLz(),
            'tel' => '11223344',
            'isOnline' => 1,
            'customerNr' => microtime(),
            'restUrl' => Default_Helpers_Web::urlify(__('lieferservice-%s-%s', $name, $city->getFullName())),
            'restUrl' => Default_Helpers_Web::urlify(__('catering-%s-%s', $name, $city->getFullName())),
            'restUrl' => Default_Helpers_Web::urlify(__('grosshandel-%s-%s', $name, $city->getFullName()))
        );

        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant();
        $restaurant->setData($data);
        $restaurant->save();

        $this->assertTrue($restaurant instanceof Yourdelivery_Model_Servicetype_Restaurant);
        return $restaurant;
    }

    /**
     * get a valid meal type from the database
     * @author Alex Vait <vait@lieferado.de>
     * @since 18.07.2012
     * @return int
     */
    public function getRandomMealType() {
        $db = Zend_Registry::get('dbAdapter');
        $typeId = $db->fetchRow('select id from meal_types order by rand() limit 1');
        return (integer) $typeId['id'];
    }

}

/**
 * Description of Abstract
 *
 * @author mlaug
 */
abstract class AbstractOrderController extends Yourdelivery_Test {

    /**
     * TODO: refactor parameters like placeOrder
     * prepare an order and give back a random $post array
     * with associated objects for service, customer and location
     * @param boolean $loggedInCustomer
     * @return array
     */
    public function _preparePost($orderId = null, Yourdelivery_Model_Customer_Abstract $customer = null, Yourdelivery_Model_Company $company = null, $mode = 'rest', $kind = 'priv', $payment = 'bar', $discount = false, $serviceAcceptsBarPayment = true, $addDiscount = null, $location = null, $faxService = null, $ServiceAcceptsBarPayment = null) {
        if ($orderId === null) {
            $service = $this->getRandomService(
                    array('onlinePayment' => true,
                        'barPayment' => $serviceAcceptsBarPayment,
                        'plz' => $location,
                        'fax' => $faxService,
                        'premium' => $args['premium'],
                        'notify' => $args['notify'],
                        'excludeCourier' => true
                    )
            );

            $location = new Yourdelivery_Model_Location();
            $range = $this->getRandomDeliverRange($service);
            $location->setCityId($range['cityId']);
            $location->setStreet('Chauseestraße');
            $location->setHausnr('86');
            $location->setTel(rand(12345678, 87654321));
            $location->setCompanyName(' $%_} TESTCASE &#{´`"^~€\\ ');
            $location->setComment('ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ');

            $orderId = $this->placeOrder(array(
                'finalize' => false,
                'payment' => $payment,
                'discount' => $addDiscount,
                'customer' => $customer,
                'location' => $location,
                'totalAbove' => 2000,
                'kind' => $kind,
                'mode' => $mode,
                'faxService' => $faxService,
                'serviceBarPayment' => $serviceAcceptsBarPayment,
                'serviceOnlinePayment' => true,
                'service' => $service));
        }

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order::STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));

        //step 1, select a location and setup ydState object
        $ydState = Yourdelivery_Cookie::factory('yd-state');
        $ydState->set('city', $order->getLocation()->getCityId());
        $ydState->set('kind', $order->getKind());
        $ydState->set('mode', $order->getMode());
        $ydState->save();

        $post = array(
            'kind' => $kind,
            'mode' => $mode,
            'payment' => $payment,
            'cityId' => $order->getLocation()->getCityId(),
            'serviceId' => $order->getService()->getId()
        );

        $card = $order->getCard(false, false);
        foreach ($card['bucket'] as $celem) {
            foreach ($celem as $hash => $elem) {
                $meal = $elem['meal'];
                $options = array();
                $extras = array();
                foreach ($meal->getCurrentOptions() as $opt) {
                    $options[] = $opt->getId();
                }
                foreach ($meal->getCurrentExtras() as $ext) {
                    $extras[] = $ext->getId();
                }
                $post['meal'][$hash] = array(
                    'id' => $meal->getId(),
                    'size' => $elem['size'],
                    'special' => $meal->getSpecial(),
                    'count' => $elem['count'],
                    'options' => $options,
                    'extras' => $extras
                );
            }
        }

        //add openings for this service
        try {
            $openings = $order->getService()->getOpenings();
            if ($location && $location->getCity()) {
                $order->getService()->setCurrentCityId($location->getCity()->getId());
            }
            $day = 0;
            foreach ($openings as $open) {
                //get next day
                for ($day = 0; $day < 7; $day++) {
                    if ($open['day'] == ((date('w') + $day) % 7)) {
                        $post['deliver-time'] = substr($open['from'], 0, -3);
                        $current_day = (date('d') + $day ) % 30;
                        $current_month = date('m');
                        $current_year = date('Y');
                        $post['deliver-time-day'] = $current_day . "." . $current_month . "." . $current_year;
                        $time = strtotime($current_year . "-" . $current_month . "-" . $current_day . " " . $post['deliver-time']);
                        if ($order->getService()->getOpening()->isOpen($time) && $time > time()) {
                            break 2;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $post['deliver-time'] = 'sofort';
        }

        if (is_object($customer)) {
            $post['email'] = $customer->getEmail();
        }
        $post['telefon'] = 4738374743734;
        $post['finish'] = true;
        $post['agb'] = 1;

        if ($order->getService()->getMinCost($order->getLocation()->getCityId()) > 30000) {
            $this->markTestSkipped('Mincost is way too high: ' . $order->getService()->getMinCost($order->getLocation()->getCityId()));
        }

        $this->assertGreaterThan($order->getService()->getMinCost($order->getLocation()->getCityId()), 30001, "Minamount: " . $order->getService()->getMinCost($order->getLocation()->getCityId()));
        return array($post, $order);
    }

}

