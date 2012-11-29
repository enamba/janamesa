<?php
/**
 * Description of BillingassetsController
 *
 * @author alex
 * @since 15.12.2010
 */
class Request_BillingassetsController extends Default_Controller_RequestBase {
    /*
     * list of all departments of this company to build a drop-down menu in view
     * @author alex
     * @since 15.12.2010
     */
    public function departmentsAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();

            if (strlen($post['companyId']) > 0) {
                try {
                    $company = new Yourdelivery_Model_Company($post['companyId']);
                    $this->view->assign('departments', $company->getDepartments());
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                }
            }

            if (strlen($post['billingassetId']) > 0) {
                try {
                    $billasset = new Yourdelivery_Model_BillingAsset($post['billingassetId']);
                    $this->view->assign('billasset', $billasset);
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                }
            }
        }
    }

    /*
     * list of all project numbers  of this company to build a drop-down menu in view
     * @author alex
     * @since 15.12.2010
     */
    public function projectnumbersAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $post = $request->getPost();

            if (strlen($post['companyId']) > 0) {
                try {
                    $company = new Yourdelivery_Model_Company($post['companyId']);
                    $this->view->assign('projectnumbers', $company->getProjectNumbers());
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                }
            }

            if (strlen($post['billingassetId']) > 0) {
                try {
                    $billasset = new Yourdelivery_Model_BillingAsset($post['billingassetId']);
                    $this->view->assign('billasset', $billasset);
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ) {
                }
            }
        }
    }
}
