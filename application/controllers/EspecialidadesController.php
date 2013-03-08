<?php
class EspecialidadesController extends Default_Controller_Base {



    public function __call($methodName, $args) {
        return $this->indexAction($methodName);
    }

    
    /**
     * get all services located in one city area
     * @author mlaug
     * @since 07.07.2011
     */
    public function indexAction($target = null) {
        $this->view->extra_css = 'step2';
        $this->view->GADocumentReady = true;
        $this->view->enableCache();

        $request = $this->getRequest();
        
        $categoryName = strstr($target, "Action", true);
                
        $category = Yourdelivery_Model_Servicetype_Categories::getCategoryIdByName($categoryName);
        
        if ($category=='' ){
            return $this->_redirect('/');
        }
        
        $this->view->title = 'Restaurantes da especialidade ' . $category['name'] . ' - Janamesa';
        $this->view->breadcrumbs = ' / especialidades / ' . $category['name'];
        $meta[] = '<meta name="robots" content="index,follow" />';
        $meta[] = '<meta name="keywords" content="restaurantes, delivery, comida, '.$categoryName.'" />';
        $meta[] = '<meta name="description" content="Restaurantes que entregam comida '.$category['name'].' online onde estiver." />';
        $this->view->assign('additionalMetatags', $meta);
        $this->view->services = $services = Yourdelivery_Model_Order_Abstract::getServicesByCagegoryId($category['id'], null, 150);
        $this->view->offlineServices = $offlineServices = Yourdelivery_Model_Order_Abstract::getOfflineServicesByCityId($category['id'], null, 30);

        $this->logger->info(sprintf('found %d online and %d offline services in #%s', count($services), count($offlineServices), $categoryName));
         $this->_helper->viewRenderer->setRender('index');

        $this->setCache(28800); //8 hours
    }
}
?>
