<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * Check the sitemap's links
 * @author vpriem
 * @since 21.01.2011
 */
clog('info', 'checking sitemap');

$failed = array();

$config = Zend_Registry::get('configuration');
$hostname = 'www.' . $config->domain->base;

$sitemap = new Default_Writer_Sitemap(APPLICATION_PATH . "/../storage/public/sitemap-" . $hostname . ".xml");
$urls = $sitemap->getUrls();

foreach ($urls as $url) {

    $m = microtime(true);
    $headers = @get_headers($url);
    $m = microtime(true) - $m;

    if (is_array($headers) && $headers[0] == "HTTP/1.1 200 OK") {
        
    } else {
        $failed[] = '<a href="' . $url . '">' . $url . '</a>' . " [" . $headers[0] . "]";
    }

    // give the server time to breathe
    if ($m > 2) {
        usleep(800000);
    } elseif ($m > 1) {
        usleep(500000);
    } else {
        usleep(100000);
    }
}

if (count($failed)) {
    Yourdelivery_Sender_Email::quickSend("Error", "The following URLs cannot be reached:<br />" . implode("<br />", $failed), null, "seo");
}
