<?php

/**
 * @package helper
 * @author mlaug
 */
class Default_Helpers_Web {

    /**
     * Build nice url
     * @author vpriem
     * @since 26.10.2010
     * @param string $string
     * @return string
     */
    public static function urlify($string) {

        $string = strtolower($string);
        $string = trim($string);
        $string = str_replace(
                array(" ", "à", "á", "â", "ã", "ä", "å", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "ÿ", "ß", "ą", "ć", "ę", "ł", "ń", "ó", "ś", "ź", "ż"), array("-", "a", "a", "a", "a", "ae", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "n", "o", "o", "o", "o", "oe", "o", "u", "u", "u", "ue", "y", "y", "ss", "a", "c", "e", "l", "n", "o", "s", "z", "z"), $string
        );
        $string = preg_replace('/[^a-z0-9\-]/', "", $string);
        $string = preg_replace('/\-\-+/', "-", $string);
        return $string;
    }

    /**
     * Get client ip address
     * @author vpriem
     * @since 14.09.2010
     * @return string
     */
    public static function getClientIp() {

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get host
     * @deprecated NOT !!! (in use in models/Satellite)
     * @author mlaug
     * @since 04.11.2010
     * @return string
     */
    public static function getHostname() {

        $host = defined('HOSTNAME') ? HOSTNAME : "";

        // fallback!!!
        if (!strlen($host)) {
            $config = Zend_Registry::get('configuration');
            $logger = Zend_Registry::get('logger');
            $prepend = '';
            if ($config->domain->www_redirect->enabled) {
                $prepend = 'www.';
            }
            $logger->warn(sprintf('Could not determin hostname, Fallback to %s', $prepend . $config->hostname));
            return str_replace("http://", "", $prepend . $config->hostname);
        }

        return $host;
    }

    /**
     * Get domain
     * @author vpriem
     * @since 06.12.2010
     * @param string $hostname
     * @return string
     */
    public static function getDomain($hostname = null) {

        if ($hostname === null) {
            $hostname = HOSTNAME;
        }

        if (preg_match('/[^.]+\.[^.]+$/', $hostname, $matches)) {
            switch ($matches[0]) {
                case "co.uk":
                case "com.br":
                    if (preg_match('/[^.]+\.[^.]+\.[^.]+$/', $hostname, $matches)) {
                        $hostname = $matches[0];
                    }
                    break;

                default:
                    $hostname = $matches[0];
            }
        }

        return $hostname;
    }

    /**
     * Get Subdomain
     * @author vpriem
     * @since 24.18.2011
     * @param string $hostname
     * @return string
     */
    public static function getSubdomain($hostname = null) {

        if ($hostname === null) {
            $hostname = HOSTNAME;
        }
        $parts = array_reverse(explode(".", $hostname));
        
        //default case
        $domainParts = count($parts);
        if ( $domainParts <= 2 ){
            return "www";
        }
        
        //get entire subdomain
        if ( $domainParts > 2 ){
            $subdomains = array_reverse(array_slice($parts, 2));
            return implode('.', $subdomains);
        }
        
    }

    /**
     * Set cookie
     * @author vpriem
     * @since 12.11.2010
     * @param string $name cookie name
     * @param mixed $value cookie value
     * @param timestamp $time
     * @return boolean
     */
    public static function setCookie($name, $value, $time = null) {

        //avoid that cookies are set multiple time during one request
        $alreadySetCookies = Zend_Registry::get('setCookies');
        if (in_array($name, $alreadySetCookies)) {
            return true;
        }

        $alreadySetCookies[] = $name;
        Zend_Registry::set('setCookies', $alreadySetCookies);

        // if an array was provided
        // we encode it as a json string
        if (is_array($value)) {
            $value = json_encode($value);
        }

        if (!is_string($value)) {
            return false;
        }

        if ($time === null) {
            $time = time() + 60 * 60 * 24 * 365;
        }

        $value = base64_encode($value);
        if (array_key_exists($name, $_COOKIE) && $value == $_COOKIE[$name]) {
            //avoid duplicate headers
            return true;
        }

        return setcookie($name, $value, $time, '/');
    }

    /**
     * delete a cookie from the client
     * @author mlaug
     * @since 16.01.2010
     * @param string $name
     * @return boolean
     */
    public static function deleteCookie($name) {
        if (isset($_COOKIE[$name])) {
            return setcookie($name, '', time() - 100000, '/');
        }
    }

    /**
     * Get cookie
     * @author vpriem
     * @since 12.11.2010
     * @param string $name cookie name
     * @return mixed
     */
    public static function getCookie($name, $decode = true) {

        if (isset($_COOKIE[$name])) {
            if ($decode) {
                $value = base64_decode($_COOKIE[$name]);

                // if it was a json string
                // return an associative array
                $json = json_decode($value, true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    return $json;
                }
            } else {
                $value = $_COOKIE[$name];
            }
            return $value;
        }

        return null;
    }

    /**
     * Removes cookie name from already stored cookies list
     * To be able to replace its value
     * Returns true if cookie name existed on the list, false otherwise
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 14.06.2012
     *
     * @param string $name
     * @return bool
     */
    public static function cancelCookie($name) {
        $alreadySetCookies = Zend_Registry::get('setCookies');
        if (($index = array_search($name, $alreadySetCookies)) !== false) {
            unset($alreadySetCookies[$index]);
        }
        Zend_Registry::set('setCookies', $alreadySetCookies);
        return ($index !== false);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 19.10.2010
     * @return string
     */
    public static function getReferer($onlyHost = true) {

        if (!isset($_SERVER['HTTP_REFERER'])) {
            return "UNKNOWN";
        }

        if ($onlyHost === true) {
            return parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        }

        return $_SERVER['HTTP_REFERER'];
    }

    /**
     * check the referer and get all informations we need
     * @author mlaug
     * @since 30.03.2011
     * @param string $ref
     */
    public static function parseReferer($ref) {

        $result = array(
            'referer' => $ref,
            'search-engine' => null,
            'search-term' => null,
        );

        $url = @parse_url($url);

        $pattern = '';
        switch ($url['host']) {
            case 'www.google.de':
                $pattern = '/q=([\w0-9\+]*)/';
                break;
        }

        if (isset($url['query']) && strlen($pattern) > 0) {
            $queryresult = array();
            preg_match($pattern, $url['query'], $queryresult);
            if (count($queryresult) > 0) {
                $result['search-term'] = $queryresult[1];
            }
        }

        return $result;
    }

    /**
     * Define timeout
     * @author vpriem
     * @since 20.04.2011
     * @param int $sec 
     * @return void
     */
    public static function setTimeout($sec = null) {

        if ($sec === null) {
            ini_restore('default_socket_timeout');
        } else {
            ini_set('default_socket_timeout', (integer) $sec);
        }
    }

    /**
     * get the current news of the blog
     * @author mlaug
     * @modified 09.11.2011 mlaug
     * @param string $domain
     * @return array 
     */
    public static function getBlogNews($domain) {
        // assign blog entries 
        $news = Default_Helpers_Cache::load("blog_rss_feed");
        if ($news === null) {
            $news = array();

            Default_Helpers_Web::setTimeout(2);
            // switch different blogs for different domains
            switch ($domain) {
                case 'taxiresto.fr':
                    $xml = @file_get_contents("http://blog.taxiresto.fr/feed/rss");
                    break;

                case 'lieferando.de':
                    $xml = @file_get_contents("http://blog.lieferando.de/feed/rss");
                    break;

                default :
                    return array();
            }

            Default_Helpers_Web::setTimeout();

            if ($xml !== false) {
                $doc = new DOMDocument();
                if ($doc->loadXML($xml)) {
                    $nodes = $doc->getElementsByTagName("item");
                    for ($i = 0; $i < 4; $i++) {
                        $news[] = array(
                            'title' => $nodes->item($i)->getElementsByTagName('title')->item(0)->nodeValue,
                            'description' => $nodes->item($i)->getElementsByTagName('description')->item(0)->nodeValue,
                            'link' => $nodes->item($i)->getElementsByTagName('link')->item(0)->nodeValue,
                        );
                    }
                }
                Default_Helpers_Cache::store("blog_rss_feed", $news);
            }
        }

        return $news;
    }

    /**
     * @param string $url
     * 
     * @return boolean 
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.07.2012
     */
    public static function url_exists($url) {
        if ((strpos($url, "http")) === false)
            $url = "http://" . $url;

        $headers = @get_headers($url);
        if (is_array($headers) && (strpos($headers[0], '200 OK') !== false)) {
            return true;
        } else {
            return false;
        }
    }

}
