<?php

/**
 * @author vpriem
 * @since 24.08.2010
 */
class YourdeliveryWriterSitemapTest extends Yourdelivery_Test {

    /**
     * @author vpriem
     * @since 24.08.2010
     */
    public function testSitemap() {

        @unlink(APPLICATION_PATH . "/../public/sitemap-test.xml");
        
        $sitemap = new Default_Writer_Sitemap(APPLICATION_PATH . "/../public/sitemap-test.xml");
        $this->assertTrue($sitemap->add("http://www.yourdelivery.de/foo"));
        $this->assertTrue($sitemap->add("http://www.yourdelivery.de/bar", "2007-06-05", "weekly", "0.5"));
        $this->assertFalse($sitemap->add("http://www.yourdelivery.de/bar"));
        $this->assertFalse($sitemap->remove("http://www.yourdelivery.de/foobar"));
        $this->assertTrue($sitemap->add("http://www.yourdelivery.de/foobar"));
        $this->assertTrue($sitemap->remove("http://www.yourdelivery.de/foobar"));
        $sitemap->save();

        $this->assertFileExists(APPLICATION_PATH . "/../public/sitemap-test.xml");

        $doc = new DOMDocument();
        $doc->load(APPLICATION_PATH . "/../public/sitemap-test.xml");

        $items = $doc->getElementsByTagName("urlset");
        $this->assertEquals($items->length, 1);

        $items = $doc->getElementsByTagName("loc");
        $this->assertEquals($items->length, 2);
        $this->assertEquals($items->item(0)->nodeValue, "http://www.yourdelivery.de/foo");
        $this->assertEquals($items->item(1)->nodeValue, "http://www.yourdelivery.de/bar");

        $items = $doc->getElementsByTagName("lastmod");
        $this->assertEquals($items->length, 2);
        $this->assertEquals($items->item(0)->nodeValue, date("Y-m-d"));
        $this->assertEquals($items->item(1)->nodeValue, "2007-06-05");

        $items = $doc->getElementsByTagName("changefreq");
        $this->assertEquals($items->length, 2);
        $this->assertEquals($items->item(0)->nodeValue, "daily");
        $this->assertEquals($items->item(1)->nodeValue, "weekly");

        $items = $doc->getElementsByTagName("priority");
        $this->assertEquals($items->length, 2);
        $this->assertEquals($items->item(0)->nodeValue, "1.0");
        $this->assertEquals($items->item(1)->nodeValue, "0.5");

        @unlink(APPLICATION_PATH . "/../public/sitemap-test.xml");
    }

}
