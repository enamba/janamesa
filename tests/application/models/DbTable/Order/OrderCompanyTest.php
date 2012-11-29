<?php
/**
 * @runTestsInSeparateProcesses 
 */
class DbOrderCompanyTest extends Yourdelivery_Test {

    /**
     * function that creates a Random Company Group.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 18.05.2012
     * @return Order_company_id
     */
    public function createRandomCompanyGroup() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $orderId = $order->getId();

        $customer = $this->createCustomer();
        $custData = $customer->getData();
        $custId = $custData['id'];

        $company = $this->createCompany();
        $compData = $company->getData();
        $companyId = $compData['id'];

        $data = array(
            'orderId' => $orderId,
            'customerId' => $custId,
            'companyId' => $companyId,
            'payment' => 'bar',
        );
        $order_comp = new Yourdelivery_Model_DbTable_Order_CompanyGroup();
        $row = $order_comp->createRow($data);
        $row->save();
        return $row['id'];
    }

    /**
     * Testcases for edit, get, findById, findByOrderId, findAllByOrderId, findByCustomerId, findByCompanyId, remove
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 18.05.2012
     */
    public function testAllDbOrderCompany() {
        $id = $this->createRandomCompanyGroup();
        $order_comp = new Yourdelivery_Model_DbTable_Order_CompanyGroup();
        $data = array('payment' => 'credit');
        $order_comp->edit($id, $data);

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('order_company_group')->where('id= ' . $id);
        $result = $db->fetchRow($query);

        $this->assertEquals('credit', $result['payment']);

        $this->assertEquals(1, count($order_comp->get('id', 1, 'order_company_group')));

        $ResById = $order_comp->findById($id);
        $this->assertEquals($id, $ResById['id']);

        $this->assertEquals($result, $order_comp->findByOrderId($result['orderId']));

        $sql = $db->select()->from('order_company_group', 'COUNT(orderId) AS num')->where('orderId=' . $result['orderId']);
        $countAll = $db->fetchRow($sql);
        $this->assertEquals($countAll['num'], count($order_comp->findAllByOrderId($result['orderId'])));

        $this->assertEquals($result, $order_comp->findByCustomerId($result['customerId']));
        $this->assertEquals($result, $order_comp->findByCompanyId($result['companyId']));

        $order_comp->remove($id);
        $this->assertFalse($db->fetchRow($query));
    }

}

?>
