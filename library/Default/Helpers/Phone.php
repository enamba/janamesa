<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 */
class Default_Helpers_Phone {

    /**
     *  See http://www.thephonebook.bt.com/publisha.content/en/search/international/record.publisha?id=L15
     * @author Vincent Priem <priem@lieferando.de>
     * @since 24.02.2012
     * @return boolean
     */
    public static function isMobile($phoneNumber) {

        if (!is_string($phoneNumber)) {
            return false;
        }

        $countryCode = substr($phoneNumber, 0, 4);

        switch ($countryCode) {
            // DE
            case "0049":
                $prefix = substr($phoneNumber, 4, 2);
                if (in_array($prefix, array("15", "16", "17"))) {
                    return true;
                }
                break;

            // FR
            case "0033":
                $prefix = substr($phoneNumber, 4, 1);
                if (in_array($prefix, array("6", "7"))) {
                    return true;
                }
                break;

            // AT
            case "0043":
                $prefix = substr($phoneNumber, 4, 3);
                if (in_array($prefix, array("644", "650", "651", "652", "653", "655", "657", "659"))) {
                    return true;
                }
                break;

            // CH
            case "0041":
                $prefix = substr($phoneNumber, 4, 2);
                if (in_array($prefix, array("76", "77", "78", "79"))) {
                    return true;
                }
                break;

            // PL
            case "0048":
                $prefix = substr($phoneNumber, 4, 2);
                if (in_array($prefix, array("50", "51", "53", "60", "66", "69", "72", "78", "79"))) {
                    return true;
                }
                break;
        }

        return false;
    }
}
