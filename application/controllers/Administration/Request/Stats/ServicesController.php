<?php
/**
 * Administration_Request_Stats_ServicesController
 * @author vpriem
 * @since 14.12.2010
 */
class Administration_Request_Stats_ServicesController extends Default_Controller_RequestAdministrationBase {

    /**
     * Save benchmark
     * @author vpriem
     * @since 14.12.2010
     */
    public function benchmarkAction(){

        // no view
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        // post
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            
            if (isset($post['city']) && isset($post['restaurants'])) {
                try {
                    $dbTable = new Yourdelivery_Model_DbTable_Restaurant_Benchmark();
                    $row = $dbTable->findByCity($post['city']);
                    if (!$row) {
                        $row = $dbTable->createRow(array(
                            'city' => $post['city']
                        ));
                    }
                    $row->restaurants = $post['restaurants'];
                    if ($row->save()) {
                        echo Zend_Json::encode(array('success' => true));
                        return;
                    }
                    
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                }
            }

            echo Zend_Json::encode(array('success' => false));

        }

    }

}
