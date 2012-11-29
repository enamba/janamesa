<?php

/**
 * Sitemap Writer
 * @author vpriem
 * @since 04.08.2010
 */
class Default_Writer_Sitemap extends DOMDocument {

    /**
     * @var string
     */
    private $_sitemap;

    /**
     * @var DOMNode
     */
    private $_urlset;

    /**
     * @var boolean
     */
    private $_geo = false;

    /**
     * @var boolean
     */
    private $_mobile = false;

    /**
     * @var array
     */
    private $_urls = array();

    /**
     * Constructor
     * @author vpriem
     * @since 04.08.2010
     * @param string $sitemap
     * @return void
     */
    public function __construct($sitemap) {

        // load
        $this->_sitemap = $sitemap;
        if (file_exists($this->_sitemap)) {
            $this->load($this->_sitemap);

            $items = $this->getElementsByTagName("urlset");
            if ($items->length) {
                $this->_urlset = $items->item(0);

                $items = $this->_urlset->getElementsByTagName('loc');
                for ($i = 0, $n = $items->length; $i < $n; $i++) {
                    $item = $items->item($i);
                    $this->_urls[$item->nodeValue] = $item->parentNode;
                }
            }
        }

        // create urlset
        if ($this->_urlset === null) {
            parent::__construct("1.0", "UTF-8");

            $this->_urlset = $this->appendChild(
                $this->createElementNS("http://www.sitemaps.org/schemas/sitemap/0.9", "urlset")
            );
        }

        // set nice output format for developement
        if (APPLICATION_ENV != "production") {
            $this->formatOutput = true;
        }
    }

    /**
     * @author vpriem
     * @since 20.09.2010
     * @return array
     */
    public function getUrls() {

        return array_keys($this->_urls);
    }

    /**
     * @author vpriem
     * @since 04.08.2010
     * @return boolean
     */
    public function setGeo() {

        if ($this->_mobile) {
            return false;
        }
        
        $this->_urlset->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:geo", "http://www.google.com/geo/schemas/sitemap/1.0");
        return $this->_geo = true;
    }

    /**
     * @author vpriem
     * @since 04.08.2010
     * @return boolean
     */
    public function setMobile() {

        if ($this->_geo) {
            return false;
        }
        
        $this->_urlset->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:mobile", "http://www.google.com/schemas/sitemap-mobile/1.0");
        return $this->_mobile = true;
    }

    /**
     * @author vpriem
     * @since 04.08.2010
     * @param string $name
     * @param string $value
     * @return DOMNode
     */
    public function createElement($name, $value = null) {

        $node = parent::createElement($name);
        if ($value !== null) {
            $node->appendChild(
                $this->createTextNode($value)
            );
        }
        return $node;
    }

    /**
     * Add an URL
     * @author vpriem
     * @since 04.08.2010
     * @param string $loc
     * @param string $lastmod
     * @param string $changefreq
     * @param string $priority
     * @return boolean
     */
    public function add($loc, $lastmod = null, $changefreq = "daily", $priority = "1.0") {

        // only add if not already exists
        if (array_key_exists($loc, $this->_urls)) {
            return false;
        }

        // url
        $item = $this->_urlset->appendChild(
            $this->createElement("url")
        );

        // url loc
        $item->appendChild(
            $this->createElement("loc", $loc)
        );

        // url lastmod
        if ($lastmod === null) {
            $lastmod = date("Y-m-d");
        }
        $item->appendChild(
            $this->createElement("lastmod", $lastmod)
        );

        // url changefreq
        $item->appendChild(
            $this->createElement("changefreq", $changefreq)
        );

        // url priority
        $item->appendChild(
            $this->createElement("priority", $priority)
        );

        $this->_urls[$loc] = $item;

        // url geo
        if ($this->_geo) {
            $item->appendChild(
                $this->createElement("geo:geo")
            )->appendChild(
                $this->createElement("geo:format", "kml")
            );
        }

        // url mobile
        if ($this->_mobile) {
            $item->appendChild(
                $this->createElement("mobile:mobile")
            );
        }
        
        return true;
    }

    /**
     * Remove an URL
     * @author vpriem
     * @since 24.08.2010
     * @param string $loc
     * @return boolean
     */
    public function remove($loc) {

        if (!array_key_exists($loc, $this->_urls)) {
            return false;
        }

        $item = $this->_urls[$loc];
        $this->_urlset->removeChild($item);
        unset($this->_urls[$loc]);

        return true;
    }

    /**
     * Print content
     * @author vpriem
     * @since 04.08.2010
     * @return void
     */
    public function output() {

        header('Content-Type: text/xml');
        die($this->saveXML());
    }

    /**
     * Save file
     * @author vpriem
     * @since 04.08.2010
     * @return boolean
     */
    public function save() {

        return (boolean) parent::save($this->_sitemap);
    }

}
