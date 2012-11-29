<?php

/**
 * Description of Company
 *
 * @package order
 * @author mlaug
 */
class Yourdelivery_Model_Order_Company extends Yourdelivery_Model_Order_Abstract {

    /**
     * stores all budgets
     * @var array
     */
    protected $_budget = array();

    /**
     * get current budget items
     * @todo should this function not be in Yourdelivery_Model_Order_Company_Single_Restaurant?
     * @return array
     */
    public function getBudget() {
        return $this->_budget;
    }

    /**
     * @author mlaug
     * @since 12.11.2010
     * @return Yourdelivery_Model_Order_Pdf_Company_Single_Fax 
     */
    public function getFaxClass() {
        return new Yourdelivery_Model_Order_Pdf_Company_Single_Fax();
    }

    /**
     * @author mlaug
     * @since 12.11.2010
     * @return Yourdelivery_Model_Order_Pdf_Private_Single_FaxCourier 
     */
    public function getCourierFaxClass() {
        return new Yourdelivery_Model_Order_Pdf_Company_Single_FaxCourier();
    }
    
    /**
     *
     * @param boolean $charge deprecated
     * @param boolean $discount
     * @param boolean $deliver
     * @param boolean $credit
     * @param boolean $budget
     * @param boolean $ownBudget
     * @param boolean $floorfee
     * @return int
     * @author mlaug
     * @since 12.08.2010
     */
    public function getAbsTotal($charge=true, $discount=true, $deliver=true, $credit=true, $budget=true, $ownBudget=true, $floorfee=true) {

        //first get total without any charge
        $total = parent::getAbsTotal(false, $discount, $deliver, $credit);

        if ($budget && $this->getMode() == "rest") {
            //remove share of invited employees
            foreach ($this->getBudget() as $share) {
                $total -= intval($share[1]);
            }

            if ($ownBudget) {
                //remove own budget
                $budget = $this->getCustomer()->getCurrentBudget();
                if ($total < $budget) {
                    $total = 0;
                } else {
                    $total -= $budget;
                }
            }
        }
        //if this is not an restaurant order, we have unlimited budget
        elseif ( $budget && $this->getMode() != 'rest' ){
            return 0;
        }

        return $total;
    }

    /**
     * get already payed amount
     * @return int
     * @author mlaug
     * @todo: this needs to be fixed (why?)
     */
    public function getPayedAmount() {
        $total = $this->getAbsTotal(true, false, true, false, false);
        $total_without_budget = $this->getAbsTotal();
        return $total - $total_without_budget;
    }

    /**
     *
     * @author mlaug
     * @return boolean
     */
    public function finish() {
        $db = Zend_Registry::get('dbAdapter');
        $db->beginTransaction();

        try {
            $result = parent::preFinish();
            if (!$result) {
                $this->error(__('Bestellung konnte nicht abgeschlossen werden'));
                return false;
            }

            $result = parent::postFinish();
            if (!$result) {
                $db->rollback();
                return false;
            }
        } catch (Exception $e) {
            Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
            $this->logger->crit('Order could not be finished: ' . $e->getMessage() . $e->getTraceAsString());
            $db->rollback();
            return false;
        }

        //second check, uneeded, but who cares
        if (!$result) {
            $this->error(__('Bestellung konnte nicht abgeschlossen werden'));
            return false;
        } else {

            try {
                // be sure, that customer is in budget-group
                if (!is_object($this->getCustomer()->getBudget())) {
                    $this->error(__('Bestellung konnte nicht abgeschlossen werden'));
                    return false;
                }

                //basic budget system
                if ( $this->getMode() == 'rest' ){
                    $this->createCompanyRow();
                    $this->createMembersRow();
                }
                else{
                    $this->createCompanyRowGreatCater();
                }
                
            } catch (Exception $e) {
                Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
                $db->rollback();
                return false;
            }
            
            $db->commit();
            //send emails for basic budget
            //inform members of shared budget

            $order = new Yourdelivery_Model_Order($this->getId());

            foreach ($this->getBudget() as $budgets) {

                $member = $budgets[0];
                $amount = $budgets[1];

                if (!is_object($member)) {
                    continue;
                }

                if ($member->getId() == $this->getCustomer()->getId()) {
                    continue;
                }

                $inform = new Yourdelivery_Sender_Email_Template('budgetsharing');
                $inform->setSubject('Budgetsharing');
                if (file_exists($file)) {
                    $attachment = $inform->createAttachment(
                                    file_get_contents($file), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
                    );
                    $attachment->filename = __('bestellzettel.pdf');
                }
                $inform->addTo($member->getEmail());
                $inform->assign('cust', $member);
                $inform->assign('order', $order);
                $inform->assign('amount', $amount);
                $inform->send();
            }
            
            unset($order);            
        }
          
        return true;
    }

    /**
     * create basis for company row
     * @author mlaug
     * @since 17.09.2011
     * @return Zend_Db_Row_Abstract
     */
    public function _createCompanyRow(){
        
        //check if any project code has been provided
        $projectId = null;
        if (is_object($this->getProject())) {
            $projectId = $this->getProject()->getId();
        } else {
            $projectId = null;
        }

        $costcenterId = null;
        if (is_object($this->getCustomer()->getCostcenter())) {
            $costcenterId = $this->getCustomer()->getCostcenter()->getId();
        } else {
            $costcenterId = null;
        }

        $groupOrderNN = new Yourdelivery_Model_DbTable_Order_CompanyGroup();


        //store the person who has created that order
        $newRow = $groupOrderNN->createRow(
                        array(
                            "orderId" => $this->getId(),
                            "customerId" => $this->getCustomer()->getId(),
                            "companyId" => $this->getCustomer()->getCompany()->getId(),
                            "projectId" => $projectId,
                            "costcenterId" => $costcenterId,
                            "projectAddition" => $this->getProjectAddition()
                        )
        );
        return $newRow;
    }
    
    /**
     * create company row for a none rest order
     * @author mlaug
     * @since 17.09.2011
     */
    public function createCompanyRowGreatCater(){
        $newRow = $this->_createCompanyRow();

        //if not we store the private amount of this order seperatly
        $totalComp = 0;
        //get customers budget
        $totalComp = $this->getAbsTotal(true,true,true,true,false);

        $newRow->coveredAmount = $totalComp;
        $newRow->amount = 0;
        $newRow->privAmount = $totalPriv;
        $newRow->save();
    }
    
    /**
     * @author mlaug
     */
    public function createCompanyRow() {     
        $newRow = $this->_createCompanyRow();

        //if not we store the private amount of this order seperatly
        $totalComp = 0;
        $totalPriv = 0;

        //get customers budget and its private amount
        $totalPriv = $this->getAbsTotal();
        //sry, initial user must always use its entire budget, sucks to be him
        $leftToPay = $this->getAbsTotal(false, true, true, true, true, false);
        $ownBudget = $this->getCustomer()->getCurrentBudget();
        if ($leftToPay < $ownBudget) {
            $totalComp = $leftToPay;
        } else {
            $totalComp = $ownBudget;
        }

        $newRow->coveredAmount = 0;
        $newRow->amount = $totalComp;
        $newRow->privAmount = $totalPriv;
        if ($totalPriv > 0) {
            $newRow->payment = $this->getCurrentPayment();
        }

        $newRow->save();
    }

    /**
     * @author mlaug
     */
    public function createMembersRow() {

        $groupOrderNN = new Yourdelivery_Model_DbTable_Order_CompanyGroup();

        foreach ($this->getBudget() as $elem) {
            $member = $elem[0];
            $amount = intval($elem[1]);
            $project = $elem[2];
            $addition = $elem[3];

            $projectId = null;
            if (is_object($project)) {
                $projectId = $project->getId();
            }

            $costcenterId = null;
            if (is_object($member->getCostcenter())) {
                $costcenterId = $member->getCostcenter()->getId();
            } else {
                $costcenterId = null;
            }

            if (is_object($member) && $member->isEmployee()) {
                $newRow = $groupOrderNN->createRow(
                                array(
                                    "orderId" => $this->getId(),
                                    "customerId" => $member->getId(),
                                    "companyId" => $member->getCompany()->getId(),
                                    "projectId" => $projectId,
                                    "costcenterId" => $costcenterId,
                                    "projectAddition" => $addition,
                                    "payment" => null
                                )
                );
                $newRow->amount = $amount;
                $newRow->save();


                //inform user that his budget has been shared for this order
                $member->createPersistentMessage('warn', sprintf(__('Ihr Budget in Höhe von %s wurde von %s (%s) für die Bestellung #%s verwendet'), intToPrice($amount), $this->getCustomer()->getFullname(), $this->getCustomer()->getEmail(), $this->getNr()));
            }
        }
    }

    /**
     * @author mlaug
     * @return int
     */
    public function getMembersBudget() {
        $total = 0;
        foreach ($this->getBudget() as $share) {
            $total += intval($share[1]);
        }
        return $total;
    }

    /**
     * @author mlaug
     * @return Yourdelivery_Model_OrderAbstract
     */
    public function setup(Yourdelivery_Model_Customer_Abstract $customer, $mode = 'rest') {

        //should not setup a company order with an unemployeed customer
        if ( !$customer->isEmployee() ){
            throw new Yourdelivery_Exception_InvalidAction(sprintf('Trying to setup a company order with an not employeed customer #%s!',$customer->getId()));
        }
        
        //set current customer as default ordering customer, mh who else :)
        $this->setCustomer($customer);

        //generate secret key
        $this->_secret = Default_Helper::generateRandomString(20);

        //generate a custom number / must be unique
        $this->generateOrderNumber();

        //set standard modes, ( this will be used for identification )
        $this->setTime(time());
        $this->setDeliverTime(time());
        $this->setMode($mode);
        $this->setKind('comp');
        $this->setIdent($this->_secret);
        $this->setCompany($customer->getCompany());
        $this->setPayment('bill');

        //create a new order
        return $this;
    }

    /**
     * @author mlaug
     * @return array
     */
    public function addBudget($email, $budget, $code=null, $addition=null) {
        try {

            if ($this->getAbsTotal(true, true, true, true, true, false) <= 0) {
                return array(null, 0, __('Der zu zahlende Betrag ist bereits gedeckt'));
            }

            try {
                $customer = new Yourdelivery_Model_Customer(null, $email);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return array(null, 0, sprintf(__('Dies ist leider kein Firmenaccount')));
            }

            if (!$customer->isEmployee() || (is_null($customer->getCompany())) ) {
                return array(null, 0, sprintf(__('Dies ist leider kein Firmenaccount')));
            }

            $customer = new Yourdelivery_Model_Customer_Company($customer->getId(), $customer->getCompany()->getId());

            if ($customer->getCompany()->getId() != $this->getCustomer()->getCompany()->getId()) {
                return array(null, 0, sprintf(__('Sie können diesen Mitarbeiter leider nicht einladen!')));
            }

            if ($customer->getId() == $this->getCustomer()->getId()) {
                return array(null, 0, sprintf(__('Sie können sich nicht selber zum Budget hinzufügen')));
            }

            if ($budget > $customer->getCurrentBudget()) {
                return array(null, 0, sprintf(__('Der Mitarbeiter hat zur Zeit nur ein Budget von %s € zur Verfügung'), intToPrice($customer->GetCurrentBudget())));
            }

            if (array_key_exists($customer->getId(), $this->_budget)) {
                return array(null, 0, __('Mitarbeiter wurde bereits hinzugefügt'));
            }

            $project = null;
            try {
                $project = Yourdelivery_Model_Projectnumbers::findByNumber($code, $this->getCustomer()->getCompany());
                if ($project === false && $this->getCustomer()->getCompany()->isCode()) {
                    throw new Yourdelivery_Exception_Database_Inconsistency();
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $codeVariant = $this->getCustomer()->getCompany()->getCodeVariant();
                if ($codeVariant == '1') {
                    return array(null, 0, sprintf(__('Der Projektcode %s konnte nicht gefunden werden'), strval($code)));
                } else if ($codeVariant == '2' || $codeVariant == '3') {
                    // create new projectcode
                    if ($project === false) {
                        $project = new Yourdelivery_Model_Projectnumbers();
                        $project->setNumber($code);
                        $project->setCompany($this->getCustomer()->getCompany());
                        $project->save();
                    }
                }
            }

            $current = $this->getAbsTotal(true, true, true, true, true, false);
            if ($budget > $current) {
                $budget = $this->getAbsTotal(true, true, true, true, true, false);
            }

            $this->_budget[$customer->getId()] = array($customer, $budget, $project, $addition);

            return array($customer, $budget, __('Mitarbeiter wurde erfolgreich hinzugefügt'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return array(null, null, __('Mitarbeiter konnte nicht hinzugefügt werden'));
        }
    }

}

?>
