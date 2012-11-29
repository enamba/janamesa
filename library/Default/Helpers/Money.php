<?php

/**
 * @package helper
 * @author mlaug
 */
class Default_Helpers_Money {

    /**
     * all possible vat (mwst) values
     */
    protected static $_vats = array
    (
        'at' => array('10', '20'),
        'de' => array('7.0', '19.0'),
        'fr' => array('19.6','5.5')
    );

    /**
     *
     * @param int $price
     * @param int $precision
     * @param string $seperator
     * @return string
     */
    static public function priceToInt($price, $precision = 2, $seperator = ",") {

        //first check for zero value
        $check = floatval($price);
        if ($check < 0) {
            return '0,00';
        }

        //calulcate based on precision
        $price = floatval($price);
        $prec = pow(10, $precision) / 100;
        $euro = intval(floor($price / 100));
        $val = round(floatval(fmod($price, 100) * $prec));
        $cent = sprintf("%0$precision.0f", $val);

        //no cents
        if ($cent == 0) {
            $cent = "00";
        }

        //if precision is 2 and we reach 100 on rounding up, we decide to raise
        //an euro and set cent to 00
        if ($cent == pow(10, $precision)) {
            $euro++;
            $cent = "00";
        }

        return $euro . $seperator . $cent;
    }

    /**
     * @author mlaug
     * @since 28.10.2010
     * @param int $brutto
     * @param int $tax
     * @return double
     */
    static public function getTax($brutto, $tax){
        return $brutto - ( $brutto / (100+$tax) * 100 );
    }

    /**
     * @author mlaug
     * @since 28.10.2010
     * @param int $brutto
     * @param int $tax
     * @return double
     */
    static public function getNetto($brutto, $tax){
        return $brutto - self::getTax($brutto, $tax);
    }

    /**
     * @author alex
     * @since 31.01.2011
     * @return array of arrays
     */
    static public function getAllVats(){
        $config = Zend_Registry::get('configuration');
        return $config->tax->types->toArray();
    }

    /**
     * @author alex
     * @since 31.01.2011
     * @param int $ladncode ('de', 'at', 'fr')
     * @return array
     */
    static public function getVat($landcode = 'de'){
        return Default_Helpers_Money::$_vats[$landcode];
    }

    /**
     * @author alex
     * @since 31.01.2011
     * @return array of distinct vats
     */
    static public function getDistinctVats(){
        $result = array();
        
        foreach (Default_Helpers_Money::$_vats as $val) {
            foreach ($val as $v) {
                if (!in_array($v, $result)) {
                    $result[$v] = $v;
                }
            }
        }
        return $result;
    }

}
