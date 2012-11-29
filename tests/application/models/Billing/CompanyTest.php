<?php

/**
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class BillingCompanyTest extends Yourdelivery_Test {

    protected function createAsset($company,$from = null,$until = null){
        // create billing asset
        $restaurant = $this->getRandomService();

        $billingAsset = new Yourdelivery_Model_BillingAsset();
        $billingAsset->setData(
                array(
                    'companyId' => $company->getId(),
                    'restaurantId' => $restaurant->getId(),
                    'courierId' => 0,
                    'total' => 4500,
                    'mwst' => 19,
                    'couriertotal' => 0,
                    'couriermwst' => 7,
                    'timeFrom' => $from === null ? date('Y-m-d H:i:s',strtotime('-2month')) : $from,
                    'timeUntil' => $until === null ? date('Y-m-d H:i:s',strtotime('-1month')) : $until,
                    'description' => 'blub',
                    'fee' => 10
                )
        );
        $id = $billingAsset->save();
        $this->assertTrue((integer) $id > 0);
    }
    
    public function testCreate() {         
        // create billing asset
        $company = $this->getRandomCompany();
        $this->createAsset($company);
        $bill = $company->getNextBill();
        $this->assertTrue($bill->create());
    }
    
    /**
     * @author mlaug
     * @since 19.08.2011
     */
    public function testGetBillingAssets(){
        // create billing asset
        $company = $this->getRandomCompany();
        $bill = $company->getNextBill();
        $countAssets = count($bill->getBillingAssets());
        
        //create one in the future
        $this->createAsset($company, date('Y-m-d H:i:s',strtotime('+2month')), date('Y-m-d H:i:s',strtotime('+3month')));
        $bill->assets = null; //reset
        $this->assertEquals($countAssets,count($bill->getBillingAssets()));
        
        //create one in the past
        $this->createAsset($company);
        $bill->assets = null; //reset
        $this->assertEquals($countAssets+1,count($bill->getBillingAssets()));
    }

}
