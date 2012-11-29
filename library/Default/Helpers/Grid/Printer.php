<?php

/**
 * Grid Printer Helper
 *
 * @author Alex Vait <vait@lieferando.de>
 * @since 30.08.2012
 */
class Default_Helpers_Grid_Printer{

    /**
     * @author Aelx Vait <vait@lieferando.de>
     * @since 30.08.2012
     * @param integer $printerId
     */
    public static function getPrinterStatesHistory($printerId, $actualState) {
        if ($printerId>0) {
            return sprintf("<div class='yd-grid yd-show-printer-states-history'>
                             <a href='#' class='yd-grid-trigger' 
                                data-printerId='%s'
                                data-grid-callback='printerstateshistory'                                
                                >%s</a>
                        </div>", $printerId, $actualState);
        }

        return '';
    }

}