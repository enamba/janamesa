<?php

/**
 * Test for the cssPackeger
 *
 * @author Toni Meuschke <meuschke@lieferando.de>
 * @since 19.03.2012
 * 
 * 
 */
/**
 * @runTestsInSeparateProcesses 
 */
class testCssPackager extends Yourdelivery_Test {

    /**
     * Test for Satellite css / Test for File exists and File include the right step
     *
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 19.03.2012
     * 
     * 
     */
    public function testCssPackagerSatellite() {
        //TEST FILE exists
        $this->deleteCssCache();
        $rand = $this->callCssPackager('/media/css/compiled/satellite-no-');
        $filename = APPLICATION_PATH . '/../public/media/css/compiled/satellite-no-' . $rand . '.css';
        $this->assertTrue(file_exists($filename), 'blub');

        //Test File includ the right Step
        $this->deleteCssCache();
        $rand = $this->callCssPackager('/media/css/compiled/satellite-step3-');

        $file1 =  utf8_encode(str_replace(array("\r", "\r\n", "\n"),'',(String)file_get_contents(APPLICATION_PATH . '/../public/media/css/satellites/yd-satellite-step3.css')));
        $file2 =  utf8_encode(str_replace(array("\r", "\r\n", "\n"),'',(String)file_get_contents(APPLICATION_PATH . '/../public/media/css/compiled/satellite-step3-' . $rand . '.css')));
        $regex = '/'.preg_quote(substr($file1, 200, 400)).'/i';
        $this->assertTrue(preg_match($regex, $file2) !== false, 'blub');
    }

    /**
     * Test for Satellite css / Test for File exists and File include the right step
     *
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 19.03.2012
     * 
     * 
     */
    public function testCssPackagerFrontend() {
        //TEST FILE exists
        $this->deleteCssCache();
        $rand = $this->callCssPackager('/media/css/compiled/frontend-no-');
        $filename = APPLICATION_PATH . '/../public/media/css/compiled/frontend-no-' . $rand . '.css';
        $this->assertTrue(file_exists($filename), 'blub');
        //Test File includ the right Step
        $this->deleteCssCache();
        $rand = $this->callCssPackager('/media/css/compiled/frontend-step3-');
        $file1 = utf8_encode(str_replace(array("\r", "\r\n", "\n"),'',(String)  file_get_contents(APPLICATION_PATH . '/../public/media/css/www.lieferando.de/yd-frontend-step3.css')));
        $file2 =  utf8_encode(str_replace(array("\r", "\r\n", "\n"),'',(String)  file_get_contents(APPLICATION_PATH . '/../public/media/css/compiled/frontend-step3-' . $rand . '.css')));
        $regex = '/'.preg_quote(substr($file1, 200, 400)).'/i';
        $this->assertTrue(preg_match($regex, $file2) !== false, 'blub');

    }

    /**
     * Clear CSS Compiled
     *
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 19.03.2012
     * 
     * 
     */
    private function deleteCssCache() {
        $files = glob(APPLICATION_PATH . '/../public/media/css/compiled/*.*');
        foreach ($files as $file)
            unlink($file);
    }

    /**
     * Call the Css Packager Function
     *
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 19.03.2012
     * @parem $uri= css url like this "/media/css/compiled/frontend-no-"
     * 
     */
    private function callCssPackager($uri) {
        global $_SERVER;
        $rand = rand(100, 1000000000);
        $_SERVER['REQUEST_URI'] = $uri . $rand . '.css';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        require( APPLICATION_PATH . '/../public/css-packager.php');
        sleep(5);
        return $rand;
    }

}

?>
