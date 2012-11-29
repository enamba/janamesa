<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mailing
 *
 * @author daniel
 */
class Default_Helpers_Grid_Mailing {
    
        
    /**
     * @author Daniel Hahn
     * @since 27.06.2012
     * @param type $mailingId
     * @return type 
     */
    public function mailingOptions($mailingId) {
        
        
        return sprintf( '<a href="/administration_mailing/edit/id/%d">' . __b('Editieren') . '</a>', $mailingId);
        
    }
    
    /**
     * @author Daniel Hahn
     * @since 27.06.2012
     * @param type $status
     * @return type 
     */
    public function mailingStatus($status) {
        return ($status == 1)? __b('aktiviert'): __b('deaktiviert');
    }
    
}

?>
