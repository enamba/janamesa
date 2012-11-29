<?php
/**
 * SEO Sitempas controller
 * @author vpriem
 * @since 03.12.2010
 */
class Administration_Seo_SitemapsController extends Default_Controller_AdministrationBase{

    /**
     * Index
     * @author vpriem
     * @since 03.12.2010
     */
    public function indexAction(){

        // assign domains
        $this->view->assign('domains', Yourdelivery_Model_DbTable_Link::getDomains());
        

    }

}
