<?php

/**
 * Description of Ecletica
 *
 * @author mlaug
 */
class Janamesa_Api_Ecletica {

    /**
     * @var Yourdelivery_Log 
     */
    protected $logger = null;

    public function __construct() {
        $this->logger = Zend_Registry::get('logger');
    }

    /**
     * @author Matthias Laug
     * @since 06.02.2012
     * @param Yourdelivery_Model_Order $order 
     */
    public function send(Yourdelivery_Model_Order $order) {
        $config = Zend_Registry::get('configuration');

        $this->_smarty = new Smarty();
        $this->_smarty->template_dir = APPLICATION_PATH . '/templates/janamesa/ecletica/';
        $this->_smarty->compile_dir = $config->smarty->compile_dir . '/janamesa/';
        $this->_smarty->config_dir = $config->smarty->config_dir;
        $this->_smarty->cache_dir = $config->smarty->cache_dir;
        $this->_smarty->caching = false;
        $location = $order->getLocation();

        $this->_smarty->assign('order', $order);
        
        $output = $this->_smarty->fetch('order.txt');
        $output = preg_replace('/[\r\n]+/',"\r\n", $output);
        
        $storage = new Default_File_Storage();
        $storage->setSubFolder('ecletica');
        $storage->setSubFolder($order->getService()->getId());
        $storage->store($order->getId() . '_jnm.txt', $output);

        $this->createFtpAccount($storage->getCurrentFolder());
        return true;
    }

    /**
     * create an ftp account for this service using the $dir
     * 
     * @author Matthias Laug
     * @since 12.03.2012 
     */
    public function createFtpAccount($ftpDir) {
        
    }

    /**
     * process all found reports for each service
     * 
     * @author Matthias Laug
     * @since 13.03.2012 
     */
    public function processReports() {
        $table = new Yourdelivery_Model_DbTable_Restaurant();
        $services = $table->select()
                ->where('notify="ecletica"')
                ->query()
                ->fetchAll();
        $storage = new Default_File_Storage();

        foreach (array('sucesso', 'falha') as $folder) {
            foreach ($services as $service) {
                $storage->resetSubFolder();
                $storage->setSubFolder(
                        array(
                            'ecletica',
                            $service['id'],
                            $folder
                        )
                );
                $this->logger->debug(sprintf('ECLETICA: searching through dir %s', $storage->getCurrentFolder()));
                $this->logger->debug(sprintf('ECLETICA: found %d files', count($storage->ls())));
                foreach ($storage->ls() as $file) {
                    $file = basename($file);
                    $id = (integer) str_replace('_jnm.txt', '', $file);
                    $this->logger->debug(sprintf('ECLETICA: found file %s and extracted orderId %d', $file, $id));
                    try {
                        $order = new Yourdelivery_Model_Order($id);
                        if ( $order->getState() == 0 ){
                            switch ($folder) {
                                case 'sucesso':
                                    $this->logger->info(sprintf('ECLETICA: found file %s in success folder', $file));
                                    $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED, 
                                             new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ECLECTICA_SUCCESS)     
                                            );
                                    break;

                                case 'falha':
                                    $this->logger->info(sprintf('ECLETICA: found file %s in falha folder', $file));
                                    $order->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERERROR,
                                            new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ECLECTICA_FAIL) 
                                     );
                                    break;
                            }
                        }
                        else{
                            $this->logger->info(sprintf('ECLETICA: found file %s, but order %d in state %d alrady processed or should not be processed', $file, $order->getId(), $order->getState()));
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        
                    }
                }
            }
        }
    }

}
