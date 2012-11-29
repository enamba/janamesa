<?php

/**
 * This class extends the Customer model  and provides additional functionality,
 * which can only be used if the customer belongs to a company, which is set up
 * when the model is created!
 * @package customer
 * @author mlaug
 */
class Yourdelivery_Model_Customer_Company extends Yourdelivery_Model_Customer {

    /**
     * the company this customer belongs to
     * @var Yourdelivery_Model_Company
     */
    public $company = null;

    /**
     * The Row of the company<->customer relation
     * @var Zend_Db_Table_Row
     */
    public $relation = null;

    /**
     * current budget amount of employee
     * @var int
     */
    public $currentBudgetAmount = null;

    /**
     * current budget object of employee
     * @var Yourdelivery_Model_Budget
     */
    public $currentBudget = null;

    /**
     * Sets up the model and it's attributes
     *
     * @param int $customerId
     * @param int $companyId
     * @throws Yourdelivery_Exception_Database_Inconsistency
     * @return void|null
     */
    public function __construct($customerId = null, $companyId = null) {
        if (is_null($customerId) || is_null($companyId)) {
            return null;
        }
        //echo "hier"; die();
        parent::__construct($customerId);      
        $hash = $this->getCacheTag("company");
        $company = Default_Helpers_Cache::load($hash);
        if ($company) {
            $this->company = $company;
        } else {
            $this->company = new Yourdelivery_Model_Company($companyId);
            $company = Default_Helpers_Cache::store($hash, $this->company);
        }

        $hashRelation = $this->getCacheTag("company_relation");
        $relation = $company = Default_Helpers_Cache::load($hashRelation);

        if ($relation) {           
            $this->relation = $relation;
        } else {
            $relationTable = new Yourdelivery_Model_DbTable_Customer_Company();
            $this->relation = $relationTable->fetchRow("companyId = " . $this->company->getId() . " AND customerId = " . $this->getId());
            $company = Default_Helpers_Cache::store($hashRelation, $this->relation);
        }


        if (is_null($this->relation)) {
            throw new Yourdelivery_Exception_Database_Inconsistency('Could not find employee relation ' . $customerId);
        }
    }

    /**
     * Adds a company<->customer relationship
     * If the customer doesn't exist, he is created and (depending on argument
     * $notify) notified. Otherwise only the relation is created and he gets notified
     * about that. If he belongs to another company or already is an employee of
     * this company, messages are thrown.
     * @author mlaug
     * @param array $values
     * @param int $companyId
     * @param boolean $notify
     * @return Yourdelivery_Model_Customer_Company
     */
    public static function add($values, $companyId=null, $notify=true) {
        if (is_null($companyId)) {
            return false;
        }
        $table = new Yourdelivery_Model_DbTable_Customer_Company();
        return $table->add($values, $companyId, $notify);
    }

    /**
     * Edit Name, prename, email and some information of the company<->customer
     * relationship and (depending on $notify) notify him about the changes.
     * @author mlaug, fhaferkorn
     * @param array $values
     * @param boolean $notify
     */
    public function update($values, $notify=true) {
        // update user data
        parent::update($values);

        $changed = false;

        if (!is_null($values['changedemail'])) {
            $changed = true;
        }

        if (!empty($values['newpass'])) {
            $changed = true;
        }

        if ($changed && $notify) {
            $this->loginChanged($this->getEmail(), $values['newpass']);
        } else {
            $relationTable = new Yourdelivery_Model_DbTable_Customer_Company();
            $relationTable->update(
                    array('emailSent' => NULL), sprintf('companyId = %d AND customerId = %d', $this->company->getId(), $this->getId())
            );
        }

        // update budget group
        $relationTable = new Yourdelivery_Model_DbTable_Customer_Company();
        $relationTable->update(
                array_merge(array('budgetId' => $values['budget']), $values), sprintf('companyId = %d AND customerId = %d', $this->company->getId(), $this->getId())
        );
        
        Default_Helpers_Cache::remove($this->getCacheTag("company_relation"));
        
        $this->success(__('Änderungen erfolgreich gespeichert!'));
    }

    /**
     * "Hey, one company created a yourdelivery account for you!"
     * @author mlaug
     * 
     * @return string $password
     */
    public function emailCreated() {

        $pass = $this->resetPassword();

        $email = new Yourdelivery_Sender_Email_Template('registercompany');
        $email->setSubject(__('Neuer Firmenaccount bei %s', $this->config->domain->base));
        $email->assign('cust', $this);
        $email->assign('password', $pass);
        $email->addTo($this->getEmail());
        $email->send();

        $relationTable = new Yourdelivery_Model_DbTable_Customer_Company();
        $relationTable->update(
                array('emailSent' => 1), sprintf('companyId = %d AND customerId = %d', $this->company->getId(), $this->getId())
        );
        return $pass;
    }

    /**
     * "Hey, your yourdelivery.de company changed some of your data!"
     * @author mlaug, fhaferkorn
     */
    public function loginChanged($email, $pass) {

        try {
            $mail = new Yourdelivery_Sender_Email_Template('loginchanged');
            $mail->setSubject(__('Ihre Zugangsdaten wurden geändert'));
            $mail->assign('customer', $this);
            $mail->assign('pass', $pass);
            $mail->addTo($email);
            $mail->send();
            $emailSent = 1;
            $this->success(__('Benachrichtigung wurde gesendet'));
        } catch (Zend_Exception $e) {
            $emailSent = 0;
        }
    }

    /**
     * Checks if employee has admin access to this company
     * @author mlaug
     * @return boolean
     */
    public function isCompanyAdmin() {
        $rights = $this->getRights();
        if (isset($rights['c']) && in_array($this->company->getId(), $rights['c'])) {
            return true;
        }
        return false;
    }

    /**
     * get fullname with company additions
     * @author mlaug
     * @return string
     */
    public function getFullname($customerNumberSeperator=" ") {
        $fullname = parent::getFullname();

        if (is_object($this->relation)) {
            $number = $this->relation->personalnumber;
        } else {
            $number = null;
        }

        //append personal number
        if (!empty($number) && !is_null($number) && $number != "NULL") {
            return $fullname . $customerNumberSeperator . "(" . $number . ")";
        }

        return $fullname;
    }

    /**
     * get one employees status
     * @author mlaug
     * @return boolean
     */
    public function getStatus() {
        return (boolean) $this->relation->status;
    }

    /**
     * Get one employee's budget
     * @author mlaug
     * @return Yourdelivery_Model_Budget
     */
    public function getBudget() {
        if (is_null($this->currentBudget)) {
            try {
                if ($this->relation->budgetId <= 0) {
                    return null;
                }
                $this->currentBudget = new Yourdelivery_Model_Budget($this->relation->budgetId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
        }
        return $this->currentBudget;
    }

    /**
     * get customers current budget
     * @author mlaug
     * @return int
     */
    public function getCurrentBudget() {

        //put in a lifetime
        if (is_null($this->currentBudgetAmount) || (is_array($this->currentBudgetAmount) && $this->currentBudgetAmount['lifetime'] <= 0)) {

            if (is_null($this->getBudget())) {
                return 0;
            }

            $time = time();


            /**
             * @todo: put that into DbTables
             * get today's budget from current
             * employee
             */
            $currentTimeSlot = $this->getTable()
                    ->getAdapter()
                    ->query("SELECT
                                                COALESCE(cbt.amount,0) as amount, cbt.from, cbt.until
                                             FROM customer_company cc
                                             INNER JOIN company_budgets cb ON cb.id=cc.budgetId
                                             INNER JOIN company_budgets_times cbt ON cbt.budgetId=cc.budgetId
                                             WHERE
                                                cc.customerId=" . $this->getId() . " AND
                                                day = " . date('w', time()) . " AND
                                                date_format(now(),'%H:%i:%s') >= cbt.from AND
                                                date_format(now(),'%H:%i:%s') <= cbt.until;"
                    )
                    ->fetch();

            $budgetToday = (integer) $currentTimeSlot['amount'];
            //take the current time slot and use it to find only those orders inside that slot
            $from = $currentTimeSlot['from'];
            $until = $currentTimeSlot['until'];

            //TODO:
            //if budgetToday == 0 we have infinit budget

            /**
             * @todo: put that into DbTables
             * get all orders, that have been placed today and sum up the amounts
             * this is the amount that has been used today and should be remove from available amount
             */
            $today = date('Y-m-d', time());
            $abused = $this->getTable()
                    ->getAdapter()
                    ->query(sprintf("SELECT
                                                    SUM(COALESCE(co.amount,0)) AS total
                                               FROM order_company_group co
                                               INNER JOIN orders o ON co.orderId=o.id
                                               WHERE
                                                    o.state>=0 AND
                                                    co.customerId=%d AND
                                                    o.time between '%s %s' and '%s %s'", $this->getId(), $today, $from, $today, $until))
                    ->fetchColumn();

            /**
             * calculate the difference
             */
            $amount = $budgetToday - $abused;
            if ($amount < 0) {
                //this should never happen
                $this->logger->warn(sprintf('Amount of customer #%s is %d, but that should not happen!', $this->getId(), $amount));
            }

            //get monthly max
            $mMax = (integer) $this->getBudget()->getMonthlyMax();
            if ($mMax > 0) {
                $abusedMonthly = $this->getTable()
                        ->getAdapter()
                        ->query(sprintf("SELECT
                                                    SUM(COALESCE(co.amount,0)) AS total
                                               FROM order_company_group co
                                               INNER JOIN orders o ON co.orderId=o.id
                                               WHERE
                                                    o.state>=0 AND
                                                    co.customerId=%d AND
                                                    MONTH(o.time) = MONTH(NOW()) AND
                                                    YEAR(o.time) = YEAR(NOW())", $this->getId()))
                        ->fetchColumn();
                //allow only monthly max
                $mAvail = $mMax - $abusedMonthly;
                if ($mAvail < $amount) {
                    $amount = $mAvail;
                }
            }

            $this->currentBudgetAmount["lifetime"] = 10;
            $this->currentBudgetAmount["amount"] = $amount;
        }


        $this->currentBudgetAmount["lifetime"]--;
        return (integer) $this->currentBudgetAmount["amount"];
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_Company
     */
    public function getCompany() {
        try {
            return new Yourdelivery_Model_Company($this->company->getId());
        } catch (Yourdelivery_Exception_AlreadyFinished $e) {
            return null;
        }
    }

    /**
     * check if a user works at a company
     * @author mlaug
     * @return boolean
     */
    public function isEmployee() {
        return parent::isEmployee();  
    }

    /**
     * check if user is allowed to smoke weed every day
     * @author mlaug
     * @return boolean
     */
    public function allowTabaco() {
        if ($this->relation->tabaco == 1 || $this->isCompanyAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * check if user is allowed to order alcoholics, *hicks
     * @author mlaug
     * @return boolean
     */
    public function allowAlcohol() {
        if ($this->relation->alcohol == 1 || $this->isCompanyAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * check uf user us allowed to order at great on company bill
     * @author mlaug
     * @return boolean
     */
    public function allowCater() {
        if ($this->relation->cater == 1 || $this->isCompanyAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * check if user is allowed to order at great on company bill
     * @author mlaug
     * @return boolean
     */
    public function allowGreat() {
        if ($this->relation->great == 1 || $this->isCompanyAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * @author mlaug
     * @since 18.01.2010
     * @return boolean
     */
    public function allowFruit() {
        return $this->allowGreat();
    }

    /**
     * @author mlaug, fhaferkorn
     * @return boolean
     */
    public function allowCanteen() {
        return false;
    }

    /**
     * get cost center
     * @author mlaug
     * @return Yourdelivery_Model_Department
     */
    public function getCostcenter() {
        $id = $this->relation->costcenterId;
        if ($id == 0 || is_null($id)) {
            return null;
        }
        $costcenter = null;
        try {
            $costcenter = new Yourdelivery_Model_Department($id);
            return $costcenter;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

    /**
     * get relation to company
     * @author mlaug
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getRelation() {
        return $this->relation;
    }

    /**
     * get personal number
     * @author mlaug
     * @return string
     */
    public function getPersonalnumber() {
        return $this->relation->personalnumber;
    }

    /**
     * return email as default
     * @author mlaug
     * @return string
     */
    public function __toString() {
        if (!is_null($this->getId())) {
            return $this->getEmail();
        }
    }

}
