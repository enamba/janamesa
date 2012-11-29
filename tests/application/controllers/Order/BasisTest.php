<?php
/**
 * @runTestsInSeparateProcesses 
 */
class OrderBasisControllerTest extends AbstractOrderController {

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     */
    public function testCitystreetActionFail() {
        $request = $this->getRequest();
        $request->setMethod('post');
        $request->setParams(array(
            'cityId' => 0
        ));
        $this->dispatch('/order_basis/citystreet');
        $this->assertRedirect();
        $this->assertRedirectTo('/?city=&street=#plzerror');
    }


    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     */
    public function testCitystreetActionFailAndAddParams() {
        $request = $this->getRequest();
        $request->setMethod('post');
        $request->setParams(array(
            'cityId' => 0,
            'city' => 'anywhere',
            'street' => 'anyhow'
        ));
        $this->dispatch('/order_basis/citystreet');
        $this->assertRedirect();
        $this->assertRedirectTo('/?city=anywhere&street=anyhow#plzerror');
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     */
    public function testCitystreetActionSuccess(){
        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $request = $this->getRequest();
        $request->setMethod('post');
        $request->setParams(array(
            'cityId' => $city->getId()
        ));
        $this->dispatch('/order_basis/citystreet');
        $this->assertRedirect();
        $this->assertRedirectTo('/' . $city->getUrl('rest'));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     */
    public function testCitystreetActionSuccessAndRemoveGetParams(){
        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $request = $this->getRequest();
        $request->setMethod('post');
        $params = array(
            'cityId' => $city->getId(),

            //any parameters which will be removed
            'city' => $city->getCity(),
            'street' => 'adsadsadsa2',
            'hausnr' => $city->getPlz()
        );
        $request->setParams($params);
        $this->dispatch('/order_basis/citystreet');
        $this->assertRedirect();
        $this->assertRedirectTo('/' . $city->getUrl('rest'), print_r($params, true));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     */
    public function testPlzActionFail() {
        $request = $this->getRequest();
        $request->setParams(array(
            'plz' => 0
        ));
        $this->dispatch('/order_basis/plz');
        $this->assertRedirect();
        $this->assertRedirectTo('/#plzerror');
    }

}
