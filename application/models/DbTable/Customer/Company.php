<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_CustomerCompany.
 *
 * @copyright   Yourdelivery
 * @author      Matthias Laug
 */

class Yourdelivery_Model_DbTable_Customer_Company extends Default_Model_DbTable_Base
{

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'customer_company';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * the reference array to map on dependent tables
     * @var array
     */
    protected $_referenceMap    = array(
        'Customer' => array(
            'columns'           => 'customerId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns'        => 'id'
        ),
        'Company' => array(
            'columns'           => 'companyId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Company',
            'refColumns'        => 'id'
        )
    );

    /**
     * Adds a company<->customer relationship
     * If the customer doesn't exist, he is created and (depending on argument
     * $notify) notified. Otherwise only the relation is created and he gets notified
     * about that. If he belongs to another company or already is an employee of
     * this company, messages are thrown.
     *
     * @todo REFACTOR !!!!!!!!!!! ASAP (http://tickets.yourdelivery.de/issues/5626)
     * 
     * @param array $values
     * @param int $companyId
     * @param boolean $notify
     * @return Yourdelivery_Model_Customer_Company
     */
    public static function add($values, $companyId, $notify=true) {
        try{
            $customer = new Yourdelivery_Model_Customer(null, $values['email']);
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $customer = null;
        }

        $relationTable = new Yourdelivery_Model_DbTable_Customer_Company();

        if(is_null($customer)) {

            $customer = new Yourdelivery_Model_Customer();

            /**
             *  customer doesnt exist in database, so create him (or her)
             */

            // generate a nice password (char char char char int int)
            $password = '';
            for($i=0;$i<4;$i++){
                $password .= chr(rand(97,122));
            }

            $password .= rand(10,99);
            $values['password'] = md5($password);

            // add the customer to the database and insert company relation
            $customer->setData($values);
            $customer->save();

            $relationTable->insert(array(
                'customerId' => $customer->getId(),
                'companyId' => $companyId,
                'budgetId' => ($values['budget'] > 0 ? $values['budget'] : 'NULL'),
                'cater' => isset($values['cater']) ? $values['cater'] : false,
                'great' => isset($values['great']) ? $values['great'] : false,
                'tabaco' => isset($values['tabaco']) ? $values['tabaco'] : false,
                'alcohol' => isset($values['alcohol']) ? $values['alcohol'] : false,
                'personalnumber' => isset($values['personalnumber']) ? $values['personalnumber'] : 'NULL',
                'costcenterId' => isset($values['costcenter']) ? $values['costcenter'] : 'NULL',
            ));

            // the customer_company model, check if the new employee should be notified
            $customerCompany = new Yourdelivery_Model_Customer_Company($customer->getId(), $companyId);
            if($notify){
                // in this function password will be reseted - Why ???
                $password = $customerCompany->emailCreated();
            }
            Default_View_Notification::success(__('Nutzer ' . $customer->getPrename() . ' ' . $customer->getName() . ' erfolgreich erstellt. Password: ' . $password));

        } else {
            /**
             *  customer already exists
             */
            $companyRel = $relationTable->findByCustomerId($customer->getId());

            // test if the customer belongs to the deleted or offline company, then delete the relation
            if(!is_null($companyRel) && ($companyRel != 0)) {
                try{
                    $company = new Yourdelivery_Model_Company($companyRel['companyId']);
                    if ( ($company->getDeleted()==1) || ($company->getStatus()==0) ) {
                        self::remove($companyRel['id']);
                        $companyRel = 0;
                    }
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                }
            }

            if(is_null($companyRel) || ($companyRel == 0)) {
                // customer doesnt belong to this company, so add him
                $relationTable->insert(array(
                    'customerId' => $customer->getId(),
                    'companyId' => $companyId,
                    'budgetId' => ($values['budget'] > 0 ? $values['budget'] : 'NULL'),
                    'costcenterId' => isset($values['costcenter']) ? $values['costcenter'] : 'NULL'
                ));

                // the customer_company model, check if the new employee should be notified
                $customerCompany = new Yourdelivery_Model_Customer_Company($customer->getId(), $companyId);
                if($notify){
                    $customerCompany->emailAdded();
                }
                Default_View_Notification::success(__('Nutzer erfolgreich der Firma hinzugefügt!'));

            } elseif($companyRel['companyId'] == $companyId) {
                // customer already belongs to THIS company
                Default_View_Notification::info(__('Dieser Nutzer gehört bereits zu dieser Firma!'));
                $customerCompany = new Yourdelivery_Model_Customer_Company($customer->getId(), $companyId);

            } else {
                // customer belongs to another company
                //Default_View_Notification::error(__('Dieser Nutzer gehört bereits zu einer anderen Firma mit id ' . $company[id] . '!'));
                Default_View_Notification::error(__('Der Nutzer mit dieser eMail Adresse gehört bereits zu einer anderen Firma!'));
                $log = Zend_Registry::get('logger');
                
                $log->info('Der Nutzer '.$customer->getShortedName().' gehört bereits zu einer anderen Firma!');
                
                
                return null;

            }
        }
        return $customerCompany;
    }

    /**
     * add a right of type $what (r, c) for id $id
     * @return Zend_Db_Table_Rowset
     */
    public function addRight($cusId,$id,$what){
        $rightsTable = new Yourdelivery_Model_DbTable_UserRights();
        $rightsTable->insert(
            array(
                'customerId'=>$cusId,
                'refId'=>$id,
                'kind'=>$what
            )
        );
    }


    /**
     * fetch member rows belonging to company and budget
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 19-10-2010
     * @param int $budgetId
     * @param int $companyId
     * @return Zend_DbTable_Rowset
     */
    public function getMembers($budgetId = null, $companyId = null){
        if( is_null($budgetId) || is_null($companyId) ){
            return null;
        }
        
        $sql = 'SELECT cc.customerId FROM customer_company cc WHERE cc.companyId = '.$companyId.' AND cc.budgetId = '.$budgetId;
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('customer_company', 'customer_company.id = ' . $id);
    }


    /**
     * get a rows matching CustomerId by given value
     * @param int $customerId
     */
    public static function findByCustomerId($customerId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "customer_company") )
                    ->where( "c.customerId = " . $customerId );

        return $db->fetchRow($query);
    }
}
