<?php
/**
 * @author mlaug
 */
class Administration_TicketsystemController extends Default_Controller_AdministrationBase {

    public function indexAction() {
        $this->view->adminName = $this->session_admin->admin->getName();
    }

}
