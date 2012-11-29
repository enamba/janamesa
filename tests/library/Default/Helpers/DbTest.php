<?php
/**
 * Test suite for DB helper class
 *
 * @author Marek Hejduk <m.hejduk@pyszne.pl>
 * @since 19.07.2012
 */
/**
 * @runTestsInSeparateProcesses
 */
class Default_Helpers_DbTest extends Yourdelivery_Test {
    /**
     * Tests whether LIKE escaper works as expected
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 19.07.2012
     */
    public function testLike() {
        $this->assertEquals(Default_Helpers_Db::like('any'), '%any%');
        $this->assertEquals(Default_Helpers_Db::like('any', Default_Helpers_Db::LIKE_TYPE_CONTAINS), '%any%');
        $this->assertEquals(Default_Helpers_Db::like('any', Default_Helpers_Db::LIKE_TYPE_STARTS_WITH), 'any%');
        $this->assertEquals(Default_Helpers_Db::like('any', Default_Helpers_Db::LIKE_TYPE_ENDS_WITH), '%any');
        $this->assertEquals(Default_Helpers_Db::like('_a%n_y%'), '%\\_a\\%n\\_y\\%%');
    }
}
