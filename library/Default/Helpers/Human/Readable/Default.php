<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Default
 *
 * @author mlaug
 */
class Default_Helpers_Human_Readable_Default {

    /**
     * get (translated) state associated with domain in application.ini
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.05.2011
     * 
     * @return string (translated) state
     */
    public static function domains($domain = null) {

        if ($domain == null) {

            try {
                $config = Zend_Registry::get('configuration');
                $domain = $config->domain->base;
            } catch (Exception $e) {
                return __('Deutschland');
            }
        }

        switch ($domain) {
            case 'lieferando.de':
                return __('Deutschland');
                break;
            case 'lieferando.at':
                return __('Österreich');
                break;
            case 'lieferando.ch':
                return __('Schweiz');
                break;
            case 'taxiresto.fr':
                return __('Frankreich');
                break;
            case 'smakuje.pl':
                return __('Polen');
                break;
            case 'elpedido.es':
                return __('Spanien');
                break;
            case 'appetitos.it':
                return __('Italien');
                break;
            default:
                return __('Deutschland');
                break;
        }
    }

    /**
     * @author Matthias Laug
     * @since 08.06.2012
     * @param string $payment
     * @return string
     */
    public static function payment($payment) {
        switch ($payment) {
            case 'bill': return __('Rechnung');
            case 'credit': return __('Kreditkarte');
            case 'paypal': return __('PayPal');
            case 'bar': return __('Barzahlung');
            case 'debit': return __('Lastschrift');
            case 'ebanking': return __('Überweisung');
            default: return __('Unbekannt');
        }
    }

    /**
     * @author Matthias Laug
     * @since 08.06.2012
     * @param string $mode
     * @return string 
     */
    public static function ordermode($mode) {
        switch ($mode) {
            case 'rest': return __b('Restaurant');
            case 'cater': return __b('Catering');
            case 'fruit': return __b('Obst');
            case 'great' : return __b('Großhandel');
            case 'canteen' : return __b('Kantine');
            default: return __b('unbekannt');
        }
    }

    /**
     * @author Matthias Laug
     * @since 08.06.2012
     * @param string $mode
     * @return string 
     */
    public static function orderkind($kind) {
        switch ($kind) {
            case 'priv': return __b('Privat');
            case 'comp': return __b('Firma');
            default: return __b('unbekannt');
        }
    }

}
