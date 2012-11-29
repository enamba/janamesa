<?php
/**
 * Iframe controller
 * @author vpriem
 */
class IframeController extends Default_Controller_Base{

    /**
     * Index
     * @author vpriem
     */
    public function indexAction(){

        $this->_redirect("/");
        
    }

    /**
     * Start
     * @author vpriem
     */
    public function startAction(){

        // get background
        $picture = $this->_request->getParam('picture');
        if ($picture === null) {
            $this->_redirect("/");
        }
        $picture = basename($picture) . ".jpg";
        if (!file_exists(APPLICATION_PATH . "/../public/media/images/yd-frame/blogs/" . $picture)) {
            $this->_redirect("/");
        }
        $this->view->assign('picture', $picture);

        // get title
        $title = $this->_request->getParam('title');
        if ($title === null) {
            $this->_redirect("/");
        }
        $this->view->assign('title', $title);

    }

    /**
     * Payment fake
     * @author vpriem
     * @since 14.09.2010
     */
    public function paymentAction(){

        $order = $this->session->currentOrder;
        if (is_object($order)) {
            $customer = $order->getCustomer();
            $this->view->assign("owner", $customer->getPrename() . " " . $customer->getName());
        }

    }

}
