<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bvb_Grid_Deploy_Tabletranslate
 * Grid with working Translation
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Bvb_Grid_Deploy_Tabletranslate extends Bvb_Grid_Deploy_Table {

    //put your code here
    protected function __($msg) {
        $msg = gettext($msg);
        $params = func_get_args();
        if (count($params) > 1) {
            return vsprintf($msg, array_slice($params, 1));
        }
        return $msg;
    }

}

?>
