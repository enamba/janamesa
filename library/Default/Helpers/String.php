<?php

/**
 * Description of String
 *
 * @author mlaug
 */
class Default_Helpers_String {

    /**
     * get a synonym list to replace some strings like Straße and str.
     * to find even more matches in some algos
     * @author matej
     * @since 17.01.2011
     * @return array
     */
    public static function parseStreetSynonyms($street) {
        $syn = array(
            'Strasse',
            'Str.',
            'str.',
            'Straße',
            '-Strasse',
            '-Str.',
            '-Straße'
        );

        return array($street);
    }

    /**
     * @author mlaug
     * @since 08.06.2011
     * @param string $msg
     * @return string
     */
    public static function replaceUmlaute($msg) {
        $replacePairs = array(
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'Ä' => 'Ae'
        );
        return strtr($msg, $replacePairs);
    }

}

?>
