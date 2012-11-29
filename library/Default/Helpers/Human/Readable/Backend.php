<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Backend
 *
 * @author mlaug
 */
class Default_Helpers_Human_Readable_Backend {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.10.2011
     * @param array $modelIds
     * @return string 
     */
    public static function linkToModel($modelIds) {

        if (!empty($modelIds['modelType']) && $modelIds['modelType'] == Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER) {
            unset($modelIds['modelType']);
            $return = "Bestellungen: ";
            $count = count($modelIds);
            $i = 0;
            foreach ($modelIds as $id) {
                $i++;
                if ($i == $count) {
                    $return .= "<a href='/administration_order/index/type/view_grid_orders/IDgrid/" . $id . "' >" . $id . "</a> ";
                } else {
                    $return .= "<a href='/administration_order/index/type/view_grid_orders/IDgrid/" . $id . "' >" . $id . "</a>, ";
                }
            }


            // return "Bestellungen: ". implode(",", $modelIds);
        } elseif (!empty($modelIds['modelType']) && $modelIds['modelType'] == Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_SERVICE) {
            unset($modelIds['modelType']);
            $return = "Dienstleister: ";
            $count = count($modelIds);
            $i = 0;
            foreach ($modelIds as $id) {
                $i++;
                if ($i == $count) {
                    $return .= "<a href='/administration/services/IDgrid/" . $id . "' >" . $id . "</a> ";
                } else {
                    $return .= "<a href='/administration/services/IDgrid/" . $id . "' >" . $id . "</a>, ";
                }
            }
        } elseif (!empty($modelIds['modelType']) && $modelIds['modelType'] == Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_RABATT) {
            unset($modelIds['modelType']);
            $return = "Rabatt: ";
            $count = count($modelIds);
            $i = 0;
            foreach ($modelIds as $id) {
                $i++;
                if ($i == $count) {
                    $return .= "<a href='/administration/discounts/IDgrid/" . $id . "' >" . $id . "</a> ";
                } else {
                    $return .= "<a href='/administration/discounts/IDgrid/" . $id . "' >" . $id . "</a>, ";
                }
            }
        }


        return $return;
    }

    /**
     *
     * @param integer $state
     * @param string $mode
     * @return string 
     */
    public static function intToOrderStatus($state, $mode = '') {

        if ($mode == 'canteen') {
            switch ($state) {
                default: return 'unbekannt';
                case '-4': return 'Not affirmed on billing';
                case '-3': return 'Fake';
                case '-2': return 'Storno';
                case '-1':
                case '0': return 'Not affirmed';
                case '1': return 'Affirmed';
            }
        } elseif ($mode == 'partner') {
            switch ($state) {
                default: return __('unbekannt');
                case '1': return __('Bestätigt');
                case '-2': return __('Storno');
            }
        } else {
            switch ($state) {
                default: return 'unbekannt';
                case '-22': return 'Rejected by Restaurant';
                case '-7': return 'Storno Discount';
                case '-6': return 'Blacklist';
                case '-5': return 'Prepayment';
                case '-4': return 'Not affirmed on billing';
                case '-3': return 'Fake';
                case '-2': return 'Storno';
                case '-15': return 'Fax eventuell durchgegangen, bitte anrufen!';
                case '-1': return 'Error';
                case '0' : return 'Not affirmed';
                case '1' : return 'Affirmed';
                case '2' : return 'Delivered';
            }
        }
    }

}
