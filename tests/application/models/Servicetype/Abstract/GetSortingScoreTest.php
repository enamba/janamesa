<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 04.04.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Servicetype_Abstract_GetSortingScoreTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 04.04.2012
     */
    public function testGetSortingScore() {

        $service = $this->getRandomService();
        
        $service->setTopUntil("");
        $service->setNotify("sms");
        $notiySms = $service->getSortingScore();
        
        $service->setNotify("smsemail");
        $notiySmsEmail = $service->getSortingScore();
        
        $service->setNotify("fax");
        $notiyFax = $service->getSortingScore();
        
        $service->setNotify("email");
        $notiyEmail = $service->getSortingScore();
        
        $this->assertEquals($notiySms, $notiySmsEmail);
        $this->assertGreaterThan($notiyFax, $notiySms);
        $this->assertGreaterThan($notiyEmail, $notiySms);
        $this->assertEquals($notiyFax, $notiyEmail);
        
        $service->setTopUntil(date("Y-m-d", time() + 60 * 60 * 24 * 5));
        $service->setNotify("sms");
        $this->assertGreaterThan($notiySms, $service->getSortingScore());
    }
}
