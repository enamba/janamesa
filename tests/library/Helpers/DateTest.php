<?php
/**
 * @author vpriem
 * @since 24.08.2010
 */
class HelpersDateTest extends Yourdelivery_Test{

    /**
     * @author vpriem
     * @since 24.08.2010
     */
    public function testGetWeekDays(){

        $days = Default_Helpers_Date::getWeekDays("2000-01-02");
        $this->assertEquals(count($days), 7);
        $this->assertEquals(date("Y-m-d", $days[0]), "2000-01-02");
        $this->assertEquals(date("Y-m-d", $days[1]), "1999-12-27");
        $this->assertEquals(date("Y-m-d", $days[2]), "1999-12-28");
        $this->assertEquals(date("Y-m-d", $days[3]), "1999-12-29");
        $this->assertEquals(date("Y-m-d", $days[4]), "1999-12-30");
        $this->assertEquals(date("Y-m-d", $days[5]), "1999-12-31");
        $this->assertEquals(date("Y-m-d", $days[6]), "2000-01-01");

        $days = Default_Helpers_Date::getWeekDays("2007-06-05");
        $this->assertEquals(count($days), 7);
        $this->assertEquals(date("Y-m-d", $days[0]), "2007-06-10");
        $this->assertEquals(date("Y-m-d", $days[1]), "2007-06-04");
        $this->assertEquals(date("Y-m-d", $days[2]), "2007-06-05");
        $this->assertEquals(date("Y-m-d", $days[3]), "2007-06-06");
        $this->assertEquals(date("Y-m-d", $days[4]), "2007-06-07");
        $this->assertEquals(date("Y-m-d", $days[5]), "2007-06-08");
        $this->assertEquals(date("Y-m-d", $days[6]), "2007-06-09");

        $days = Default_Helpers_Date::getWeekDays("2009-11-14");
        $this->assertEquals(count($days), 7);
        $this->assertEquals(date("Y-m-d", $days[0]), "2009-11-15");
        $this->assertEquals(date("Y-m-d", $days[1]), "2009-11-09");
        $this->assertEquals(date("Y-m-d", $days[2]), "2009-11-10");
        $this->assertEquals(date("Y-m-d", $days[3]), "2009-11-11");
        $this->assertEquals(date("Y-m-d", $days[4]), "2009-11-12");
        $this->assertEquals(date("Y-m-d", $days[5]), "2009-11-13");
        $this->assertEquals(date("Y-m-d", $days[6]), "2009-11-14");

    }
    
    /**
     * @author vpriem
     * @since 26.10.2010
     */
    public function testIsDate(){

        $date = Default_Helpers_Date::isDate("05.06.2007");
        $this->assertTrue(is_array($date));
        $this->assertEquals($date['d'], "05");
        $this->assertEquals($date['m'], "06");
        $this->assertEquals($date['y'], "2007");

        $date = Default_Helpers_Date::isDate("2007-01-04");
        $this->assertTrue(is_array($date));
        $this->assertEquals($date['d'], "04");
        $this->assertEquals($date['m'], "01");
        $this->assertEquals($date['y'], "2007");
        
        $this->assertTrue(is_array(Default_Helpers_Date::isDate("05-06-2007")));
        $this->assertTrue(is_array(Default_Helpers_Date::isDate("05/06/2007")));
        $this->assertTrue(is_array(Default_Helpers_Date::isDate("2007-06-05")));
        $this->assertFalse(Default_Helpers_Date::isDate("31.02.2010"));
              
    }


}
