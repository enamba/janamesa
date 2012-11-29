<?php


/**
 * @since 01.02.2011
 * @author mlaug
 */
interface Yourdelivery_Sender_Fax_Interface {
    
    /**
     * must be implemented to use the fax wrapper
     */
    public function send($to, $pdf, $type, $unique = null);
    
    /**
     * must be implemented to use the fax wrapper
     */
    public function processReports();
    
}
?>
