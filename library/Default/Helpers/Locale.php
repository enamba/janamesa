<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 13.06.2012
 */
class Default_Helpers_Locale {
    
    /**
     * Default locale cookie expire time (15 days)
     * @var integer 
     */
    const EXPIRE_DELAY = 1296000;

    /**
     * Extensible list of available locales
     * @var array
     */
    protected static $locales = array(
        'de_DE' => 'Deutsch',
        'es_ES' => 'Español',
        'fr_FR' => 'Français',
        'it_IT' => 'Italiano',
        'pl_PL' => 'Polski',
        'pt_BR' => 'Português (Brasil)',
    );

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 13.06.2012
     * @return array
     */
    public static function getList() {
        
        return self::$locales;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.07.2012
     * @return boolean
     */
    public static function isAvailable($locale) {
        
        return array_key_exists($locale, self::$locales);
    }

    /**
     * Applies passed locale and tells whether it has been successful
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 11.07.2012
     *
     * @param string $locale
     * @param string $encoding
     * @param integer $category
     * @return boolean
     */
    public static function apply($locale, $encoding = 'UTF-8', $category = LC_ALL) {
        return (setlocale($category, sprintf('%s.%s', $locale, $encoding)) !== false);
    }

    /**
     * Returns locale identifier which is a best match to accept languages list send by browser
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 13.06.2012
     *
     * @return string
     */
    public static function detect() {
        // Is there any way to force Zend_Locale('browser') to return a list of locales, as they are proposed in raw HTTP header?
        // Then we could use Zend_Locale instead of processing raw HTTP header in non-ZF style
        $httpAcceptLanguage = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))? $_SERVER['HTTP_ACCEPT_LANGUAGE']: '';
        if (($semicolonPos = strpos($httpAcceptLanguage, ';')) !== false) {
            // Extraction: pl,en-us;q=0.7,en;q=0.3 -> pl,en-us
            $httpAcceptLanguage = substr($httpAcceptLanguage, 0, $semicolonPos);
        }

        $acceptedLanguages = array();
        foreach (explode(',', $httpAcceptLanguage) as $rawChunk) {
            // converting: en-us -> en_US
            $subChunks = explode('-', $rawChunk);
            if (isset($subChunks[1])) {
                $subChunks[1] = strtoupper($subChunks[1]);
            }
            $acceptedLanguages[] = implode('_', $subChunks);
        }

        if(count($acceptedLanguages) == 0) {
            return null;
        }
                
        // Looking for best matching locale and returning it
        foreach (array_keys(self::$locales) as $lc) {
            foreach ($acceptedLanguages as $lang) {
                // de_DE vs. de_DE, pl vs. pl_PL
                if (strpos($lc, $lang) === 0) {
                    // we've got the goal
                    return $lc;
                }
            }
        }

        // Nothing matches if we are here - locale won't be overriden
        return null;
    }

    /**
     * Returns default expire time for locale cookie
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 14.06.2012
     * 
     * @return int
     */
    public static function getExpireTime() {
        return self::EXPIRE_DELAY + time();
    }
}
