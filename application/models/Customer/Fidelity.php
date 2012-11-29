<?php

/**
 * @package customer
 * @subpackage fidelity
 */

/**
 * Description of Fidelity
 *
 * @package customer
 * @subpackage Location
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @modified 04.11.2011 mlaug
 */
class Yourdelivery_Model_Customer_Fidelity extends Default_Model_Base {

    /**
     * store the email adress here
     *
     * @var string
     */
    protected $_email = null;

    /**
     * stores from the config all points for each action
     *
     * @var array
     */
    protected $_pointsForAction = array();

    /**
     * the last transaction
     *
     * @var integer
     */
    protected $_last = null;

    /**
     * mark the last transaction as old or not
     *
     * @var boolean
     */
    protected $_oldTrans = false;

    /**
     * stores the current config
     *
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * stores all current transactions
     *
     * @var array
     */
    protected $_transactions = array();

    /**
     * stores all open actions
     *
     * @var array
     */
    protected $_openActions = null;

    /**
     * this is the constructor
     *
     * @param string $email eMail of customer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     */
    public function __construct($email) {
        $this->_email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        $this->_pointsForAction = $this->_config->fidelity->points->toArray();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 05.12.2011
     * @return string
     */
    public function getCacheTag($append = '') {
        return sprintf('%sfidelity%s', $append, md5($this->_email));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 05.12.2011
     */
    public function clearCache() {
        // clear cache for transactions
        if (Default_Helpers_Cache::remove($this->getCacheTag('transactions'))) {
            $this->logger->debug('CUSTOMER FIDELITY - cache cleared for ' . $this->getCacheTag('transactions'));
        } else {
            $this->logger->debug('CUSTOMER FIDELITY - could not clear cache for ' . $this->getCacheTag('transactions'));
        }

        // clear cache for open actions all
        if (Default_Helpers_Cache::remove($this->getCacheTag('open1'))) {
            $this->logger->debug('CUSTOMER FIDELITY - cache cleared for ' . $this->getCacheTag('open1'));
        } else {
            $this->logger->debug('CUSTOMER FIDELITY - could not clear cache for ' . $this->getCacheTag('open1'));
        }

        // clear cache for open actions
        if (Default_Helpers_Cache::remove($this->getCacheTag('open0'))) {
            $this->logger->debug('CUSTOMER FIDELITY - cache cleared for ' . $this->getCacheTag('open0'));
        } else {
            $this->logger->debug('CUSTOMER FIDELITY - could not clear cache for ' . $this->getCacheTag('open0'));
        }

        $this->_transactions = null;
        $this->_openActions = null;
    }

    /**
     * get points for an action
     *
     * @param string $action action for getting fidelity points
     *
     * @return integer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     */
    public function getPointsForAction($action) {
        if (!array_key_exists($action, $this->_pointsForAction)) {
            $this->logger->warn('found no points for an action ' . $action);
            return 0;
        }
        $points = $this->_pointsForAction[$action];
        $this->logger->debug(sprintf('found %s points for action %s', $points, $action));
        return (integer) $points;
    }

    /**
     * get the last transaction id
     *
     * @return integer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 11.11.2011
     */
    public function getLastTransactionId() {
        return (integer) $this->_last;
    }

    /**
     * check if the last transaction is an already created one for a unique
     * action or not
     *
     * @return boolean
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.02.2012
     */
    public function isOldTransaction() {
        return (boolean) $this->_oldTrans;
    }

    /**
     * add a new transaction for this customer. we may store different types
     * of data in the data field of the table, e.g. the link to the facebook post,
     * or the associated twitter account...
     *
     * if points are provided, those defined in the config file will be overwritten
     *
     * this method is the basis method
     *
     * This action will will remove the cache
     *
     * @param string  $action action for getting fidelity points
     * @param string  $data   some data for transaction
     * @param integer $points amount of points for transaction
     * @param integer $status optional flag for transaction state
     *
     * @return integer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     */
    public function addTransaction($action, $data, $points = 0, $status = 0) {
        if ($this->_config->fidelity->enabled != 1 || $this->_email === null) {
            return $this->getPoints();
        }

        $uniqueAction = $this->isUnique($action);
        if (in_array($action, $this->getUniqueActions()) && $uniqueAction) {
            $this->_last = $uniqueAction['id'];
            $this->_oldTrans = true;
            $this->modifyTransaction($uniqueAction['id'], 0);
            return $this->getPoints();
        }

        $points = $points == 0 ? $this->getPointsForAction($action) : $points;

        // don't add transaction if points are 0
        if ($points == 0) {
            return $this->getPoints();
        }

        $transaction = new Yourdelivery_Model_Customer_FidelityTransaction();
        $transaction->setEmail($this->_email);
        $transaction->setAction($action);
        $transaction->setStatus($status);
        $transaction->setPoints($points);
        $transaction->setTransactionData($data);
        $transactionId = $transaction->save();
        if ($transactionId) {
            $this->_oldTrans = false;
            $this->_last = $transactionId;
            $this->logger->info(sprintf('successfully added %d points to customer %s for action %s', $points, $this->_email, $action));
            $this->clearCache();
            return $this->getPoints();
        }

        $this->logger->crit(sprintf('failed to add %d points to customer %s for action %s', $points, $this->_email, $action));
        return $this->getPoints();
    }

    /**
     * check if this transactioni is unique, so that we do not add it twice
     *
     * @param string $action action for getting fidelity points
     *
     * @return array
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.11.2011
     */
    public function isUnique($action) {
        $table = new Yourdelivery_Model_DbTable_Customer_FidelityTransaction();
        return $table->findByAction($action, $this->_email, true);
    }

    /**
     * cancel a transaction.
     * This action will will remove the cache
     *
     * @param integer $transactionId id of transaction to be modified
     * @param integer $status        new state to set transaction
     *
     * @return boolean
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     */
    public function modifyTransaction($transactionId, $status) {
        if ((integer) $transactionId <= 0) {
            return $this->getPoints();
        }
        try {
            $transaction = new Yourdelivery_Model_Customer_FidelityTransaction($transactionId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->getPoints();
        }

        //cancel this transaction
        $transaction->setStatus($status);
        $transaction->save();
        $this->logger->info(sprintf('successfully modifed transaction #%s of customer %s to status %s', $transactionId, $this->_email, $status));
        $this->clearCache();
        return $this->getPoints();
    }

    /**
     * get transactions (for given action)
     *
     * @param string  $action action for getting fidelity points
     * @param integer $limit  limit for transacion result
     *
     * @return array
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 23.12.2011
     */
    public function getTransactions($action = null, $limit = null) {
        if ($this->_email === null) {
            return array();
        }
        if (!isset($this->_transactions[$action][$limit]) || $this->_transactions[$action][$limit] === null) {
            $hash = $this->getCacheTag('transactions' . $action);
            $this->_transactions[$action][$limit] = Default_Helpers_Cache::load($hash);
            if ($this->_transactions[$action][$limit]) {
                return $this->_transactions[$action][$limit];
            }
            $this->_transactions[$action][$limit] = Yourdelivery_Model_DbTable_Customer_FidelityTransaction::findAllByEmail($this->_email, $action, $limit);
            Default_Helpers_Cache::store($hash, $this->_transactions[$action][$limit]);
        }
        return $this->_transactions[$action][$limit];
    }

    /**
     * get transaction by given data
     *
     * @param string $transactionData some data for transaction
     * @param string $action          action for getting fidelity points
     *
     * @return integer transactionId
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.11.2011
     */
    public function getTransactionByTransactionDataAction($transactionData, $action) {
        if ($this->_email === null) {
            return array();
        }

        return $this->getTable()->findByTransactionDataAction($this->_email, $transactionData, $action);
    }

    /**
     * get all transaction with according data
     *
     * @return array
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.11.2011
     */
    public function getTransactionsVerbose() {
        $transactionList = $this->getTransactions();
        //provide message
        foreach ($transactionList as $key => $transaction) {
            $action = $transaction['action'];
            switch ($action) {
                case 'manual':
                    if ($transaction['transactionData'] == 'initiale migration') {
                        $transactionList[$key]['message'] = __('Wir haben auf ein neues Treuepunkte-System umgestellt. Ab sofort bekommst Du für 100 Treuepunkte ein Gratisessen. Bestehende Treuepunkte wurden entsprechend an das neue System angepasst.');
                    } else {
                        $transactionList[$key]['message'] = __('fidelity_' . $action, $transaction['transactionData']);
                    }
                    break;

                case 'accountimage':
                    $transactionList[$key]['message'] = __('fidelity_' . $action . ' %s', $transaction['points']);
                    break;

                //transaction data inherits an orderID
                case 'rate_low':
                case 'rate_high':
                    try {
                        $order = new Yourdelivery_Model_Order((integer) $transaction['transactionData'], false);
                        $transactionList[$key]['message'] = __('fidelity_' . $action . ' %s %s', $order->getService()->getName(), $transaction['points']);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                    }
                    break;

                case 'usage':
                case 'order':
                    try {
                        $order = new Yourdelivery_Model_Order((integer) $transaction['transactionData'], false);
                        $transactionList[$key]['message'] = __('fidelity_' . $action . ' %s %s %s', $order->getService()->getName(), date(__("d.m.Y H:i"), $order->getTime()), $transaction['points']);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                    }
                    break;

                case 'registeraftersale':
                case 'register':
                    $transactionList[$key]['message'] = __('fidelity_' . $action . ' %s', $transaction['points']);
                    break;
                case 'facebookconnect':
                    $transactionList[$key]['message'] = __('fidelity_' . $action . ' %s %s', $transaction['transactionData'], $transaction['points']);
                    break;
                case 'facebookpost':
                    $transactionList[$key]['message'] = __('fidelity_' . $action . ' %s %s', $transaction['transactionData'], $transaction['points']);
                    break;
            }
        }
        return $transactionList;
    }

    /**
     * get the total of points of a certian email address
     *
     * @param string $action action for getting fidelity points
     *
     * @return integer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     */
    public function getPoints($action = null) {
        $transaction = $this->getTransactions($action);
        return (integer) array_reduce($transaction, function($v, $w) {
                            $v += $w['status'] >= 0 ? $w['points'] : 0;
                            return $v;
                        }
        );
    }

    /**
     * check, if user is allowed 2 cash-in fidelity points
     *
     * @return boolean
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.11.2011
     */
    public function isCashinReached() {
        if ($this->getPoints() >= $this->getCashInNeed()) {
            return true;
        }
        return false;
    }

    /**
     * get the maxcost of a meal, which can be cashed in
     *
     * @return integer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     */
    public function getCashInLimit() {
        return (integer) $this->_config->fidelity->cashin->maxcost;
    }

    /**
     * get the minimum fidelity points to cashin as a discount
     *
     * @return integer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.11.2011
     */
    public function getCashInNeed() {
        return (integer) $this->_config->fidelity->cashin->need;
    }

    /**
     * get the maximum cost of meal, which can be treated as a discount
     *
     * @return integer
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 18.06.2012
     */
    public function getCashInMaxCost() {
        return (integer) $this->_config->fidelity->cashin->maxcost;
    }

    /**
     * get all actions which could trigger another fidelity point
     *
     * @return array
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.11.2011
     */
    public function getOpenActions($all = false) {
        if ($this->_openActions[(integer) $all] != null) {
            $this->logger->debug('FIDELITY - OPEN ACTIONS: variable was already set - don\'t recalculating');
            return $this->_openActions[(integer) $all];
        }

        if ($this->_email === null) {
            return array();
        }

        $hash = $this->getCacheTag('open' . (integer) $all);
        $this->_openActions[(integer) $all] = Default_Helpers_Cache::load($hash);
        if ($this->_openActions[(integer) $all] != null) {
            $this->logger->debug('FIDELITY - OPEN ACTIONS: Cache loaded for id:  ' . $this->getCacheTag('open' . (integer) $all));
            return $this->_openActions[(integer) $all];
        }

        //check for unrated orders
        $unrated = null;
        $customer = $this->getCustomer();
        if (!is_null($customer)) {
            $unrated = $customer->getUnratedOrders($all ? 100 : 5, 0);
        }

        $pointsToRate = $this->getPointsForAction('rate_high');

        $openActions = array();
        $all ? $count = count($unrated) : $count = 3;
        for ($i = 0; $i < $count; $i++) {
            if (count($unrated) > 0) {
                $o = array_pop($unrated);
                $openActions['orders'][] = array(
                    'info' => __('Bestellung bei %s bewerten und bis zu %d Punkte bekommen', $o['name'], $pointsToRate),
                    'id' => $o['order_id'],
                    'call2action' => sprintf('/rate/%s', $o['hashtag']),
                    'time' => $o['time'],
                    'points' => $pointsToRate
                );
            }
        }

        //check for facebook like
        /* $facebook = Yourdelivery_Model_DbTable_Customer_FidelityTransaction::findByAction('facebookfan', $this->_email);
          $pointsToLike = $this->getPointsForAction('facebookfan');
          if (!$facebook) {
          $openActions['facebook'][] = array(
          'info' => __('Werde jetzt Fan auf Facebook und bekomme %d Treuepunkte', $pointsToLike),
          'id' => null,
          'call2action' => 'I LIKE',
          'time' => time(),
          'points' => $pointsToLike
          );
          } */

        //check for profile image
        $profile = Yourdelivery_Model_DbTable_Customer_FidelityTransaction::findByAction('accountimage', $this->_email, true);
        $pointsToProfile = $this->getPointsForAction('accountimage');
        if ((!$profile || $profile['status'] < 0) && !is_null($customer) && !$customer->hasProfileImage()) {
            $openActions['ac'][] = array(
                'info' => __('Lade jetzt ein Profilbild von Dir hoch und bekomme %d Treuepunkte', $pointsToProfile),
                'id' => null,
                'call2action' => '/user/index',
                'time' => time(),
                'points' => $pointsToProfile
            );
        }

        //check for tweets and facebook status
        if (Default_Helpers_Cache::store($hash, $openActions)) {
            $this->logger->debug('FIDELITY - OPEN ACTIONS: Cache stored for id:  ' . $hash);
        } else {
            $this->logger->debug('FIDELITY - OPEN ACTIONS: could not store for id:  ' . $hash);
        }

        //return all results
        return $this->_openActions[(integer) $all] = $openActions;
    }

    /**
     * get the count of possible fidelity points
     *
     * @return integer
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.11.2011
     */
    public function getOpenActionPoints($all = false) {
        $open = $this->getOpenActions($all);
        $coins = 0;
        if (is_array($open)) {
            foreach ($open as $action) {
                foreach ($action as $a) {
                    $coins += (integer) $a['points'];
                }
            }
        }

        return $coins;
    }

    /**
     * cancel a fiedelity transaction
     *
     * @param string $action action to get fidelity points for
     *
     * @return integer new amount of fidelity points
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 23.11.2011
     */
    public function cancelTransactionByAction($action) {
        $transaction = $this->getTable()->findByAction($action, $this->_email, true);
        $this->modifyTransaction($transaction['id'], -1);
    }

    /**
     * get all unique actions, which are only allowed once for each customer
     *
     * @return array
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 27.11.2011
     */
    public function getUniqueActions() {
        return $this->_config->fidelity->unique->toArray();
    }

    /**
     * migrate fidelity transactions from old email to new email
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.12.2011
     *
     * @param string $newEmail new email address for customer
     *
     * @return boolean
     */
    public function migrateToEmail($newEmail = null) {

        $validator = new Zend_Validate_EmailAddress();
        if (!$validator->isValid($newEmail)) {
            $this->logger->crit(sprintf('tried to migrate fidelity points of %s to an empty or malformed email', $this->_email));
            return false;
        }

        $updatedRows = 0;
        try {
            $updatedRows = $this->getTable()->migrateToEmail($this->_email, $newEmail);
        } catch (Exception $e) {
            $this->logger->err(sprintf('could not migrate fidelity-transaction-row(s) for email %s', $this->_email));
            Yourdelivery_Sender_Email::quickSend('Fidelity transaction migration failure notice', sprintf('tried to migrate email from %s to %s, but fidelity points could not be migrated. Please check logs', $this->_email, $newEmail));
            return false;
        }

        if ($updatedRows > 0) {
            $this->logger->info(sprintf('successfully migrated %d fidelity-transaction-row(s) from %s to %s', $updatedRows, $this->_email, $newEmail));
        }
        return true;
    }

    /**
     * the getTable Method
     *
     * @return Yourdelivery_Model_DbTable_Customer_FidelityTransaction
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 23.11.2011
     */
    public function getTable() {
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Customer_FidelityTransaction();
        }
        return $this->_table;
    }

    /**
     * get customer from fidelity model
     *
     * @return Yourdelivery_Model_Customer / null
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.04.2012
     */
    private function getCustomer() {
        $customer = null;
        try {
            $customer = new Yourdelivery_Model_Customer(null, $this->_email);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }
        return $customer;
    }

}

