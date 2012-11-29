<?php

/**
 * Testcase for ServicetypeOpenings
 *
 * @runTestsInSeparateProcesses
 */
class Servicetype_OpeningsTest extends Yourdelivery_Test {

    /**
     * Service
     * @var Yourdelivery_Model_Servicetype_Restaurant
     */
    protected $_service = null;

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function setUp() {
        parent::setUp();
        $this->_service = $this->createRestaurant();
    }

    /**
     * Splits multiple day openings into two separate opening instances.
     * this is needed for the database to work properly
     *
     * @author mlaug
     * @author Andre Ponert <ponert@lieferando.de>
     * @param timestamp $from
     * @param timestamp_type $until
     * @param Yourdelivery_Model_Servicetype_Restaurant $service
     */
    protected function _splitMultipleDays($from, $until, $service) {
        if (date('w', $from) != date('w', $until)) {
            $this->assertGreaterThan(0, $service->getOpening()->addNormalOpening(array(
                        'day' => date('w', $from),
                        'from' => date('H:i:s', $from),
                        'until' => '24:00:00'
                    )));
            $this->assertGreaterThan(0, $service->getOpening()->addNormalOpening(array(
                        'day' => date('w', $until),
                        'from' => '00:00:00',
                        'until' => date('H:i:s', $until)
                    )));
        } else {
            $this->assertGreaterThan(0, $service->getOpening()->addNormalOpening(array(
                        'day' => date('w', $from),
                        'from' => date('H:i:s', $from),
                        'until' => date('H:i:s', $until)
                    )));
        }
    }

    /**
     * service is open now
     * - time() between now -1 hour and now +1 hour
     * - single timeslot today
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 29.05.2012
     */
    public function testIsOpenNow() {

        $from = strtotime("-10 minutes");
        $until = strtotime("+10 minutes");

        $timebase = time();
        $from = strtotime("-2 minutes", $timebase);
        $until = strtotime("+2 minutes", $timebase);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array('restaurantId' => $this->_service->getId(),
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));
        $this->assertTrue($this->_service->getOpening()->isOpen($timebase), $this->_service->getId());
        $this->assertTrue($this->_service->getOpening()->isOpen(), $this->_service->getId());
    }

    /**
     * service is open now
     * - time() between now -1 hour and now +1 hour
     * - multiple timeslots today
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 29.05.2012
     */
    public function testIsOpenNowMultiple() {

        $timebase = time();
        $from = strtotime("-2 minutes", $timebase);
        $until = strtotime("+2 minutes", $timebase);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'restaurantId' => $this->_service->getId(),
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));

        $from = strtotime("+4 minutes", $timebase);
        $until = strtotime("+6 minutes", $timebase);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'restaurantId' => $this->_service->getId(),
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen($timebase), $this->_service->getId());
        $this->assertFalse($this->_service->getOpening()->isOpen(strtotime('+3 minutes', $timebase)), $this->_service->getId());
        $this->assertTrue($this->_service->getOpening()->isOpen(strtotime('+5 minutes', $timebase)), $this->_service->getId());
    }

    /**
     * service is not open now
     * - time()+2 NOT between now -1hour and now +1hour
     * - single timeslot
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsNotOpenIn2Minutes() {
        // preparing time statements
        $timebase = time();
        $from = strtotime('-1 minute', $timebase);
        $until = strtotime('+1 minute');
        $inTwoMinutes = strtotime('+2 minutes');

        // Assert, that openings have been added correctly
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'restaurantId' => $this->_service->getId(),
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                ))
        );

        // Assert, that the restaurant is not opened.
        $this->assertFalse($this->_service->getOpening()->isOpen($inTwoMinutes), $this->_service->getId());
    }

    /**
     * service is not open now
     * - time()+2 NOT between now -1hour and now +1hour
     * - multiple timeslots
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsNotOpenIn2MinutesMultiple() {
        // preparing time statements
        $timebase = time();
        $from_1 = strtotime('-1 minute', $timebase);
        $until_1 = strtotime('+1 minutes', $timebase);

        $from_2 = strtotime('+4 minutes', $timebase);
        $until_2 = strtotime('+5 minutes', $timebase);

        $inTwoMinutes = strtotime('+2 minutes', $timebase);

        // Assert, that openings 1 have been added correctly
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'restaurantId' => $this->_service->getId(),
                            'day' => date('w'),
                            'from' => date('H:i:s', $from_1),
                            'until' => date('H:i:s', $until_1)
                ))
        );

        // Assert, that openings 2 have been added correctly
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'restaurantId' => $this->_service->getId(),
                            'day' => date('w'),
                            'from' => date('H:i:s', $from_2),
                            'until' => date('H:i:s', $until_2)
                ))
        );

        // Assert, that the restaurant is not opened.
        $this->assertFalse($this->_service->getOpening()->isOpen($inTwoMinutes), $this->_service->getId());
    }

    /**
     * service is not open now
     * - time()+1 NOT between now -1hour and now +1hour
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsNotOpenLast2Minutes() {
        $timebase = time();
        // preparing time statements
        $from = strtotime('-1 minute', $timebase);
        $until = strtotime('+1 minute', $timebase);
        $lastTwoHours = strtotime('-2 minutes', $timebase);

        // Assert, that openings have been added correctly
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                ))
        );

        // Assert, that the restaurant is not opened.
        $this->assertFalse($this->_service->getOpening()->isOpen($lastTwoHours), $this->_service->getId());
    }

    /**
     * service is not open now
     * - time()-21 NOT between now -1hour and now +1hour
     *
     *  @author Andre Ponert <ponert@lieferando.de>
     *  @since 31.05.2012
     */
    public function testIsNotOpenLast2MinutesMultiple() {
        $timebase = time();
        // preparing time statements
        $from_1 = strtotime('-1 minute', $timebase);
        $until_1 = strtotime('+1 minute', $timebase);

        $from_2 = strtotime('-4 minutes', $timebase);
        $until_2 = strtotime('-5 minutes', $timebase);

        $lastTwoMinutes = strtotime('-2 minutes', $timebase);

        // Assert, that openings 1 have been added correctly
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from_1),
                            'until' => date('H:i:s', $until_1)
                ))
        );

        // Assert, that openings 2 have been added correctly
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from_2),
                            'until' => date('H:i:s', $until_2)
                ))
        );

        // Assert, that the restaurant is not opened.
        $this->assertFalse($this->_service->getOpening()->isOpen($lastTwoMinutes), $this->_service->getId());
    }

    /**
     * service is open for timeslot at next day
     *
     * @author Mohammad Rawaqah <rawaqah@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsOpenNextDay() {
        $timebase = time();

        $time = strtotime('10 am', strtotime("+1 day", $timebase));

        $from = strtotime('9 am', strtotime("+1 day", $timebase));
        $until = strtotime('11 am', strtotime("+1 day", $timebase));

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w', $time),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen($time), sprintf('service #%d is not open at %s but should be, because normal opening was added before', $this->_service->getId(), date('l, d.m.Y H:i:s', $time)));
    }

    /**
     * service is NOT open for timeslot at next day
     *
     * @author Mohammad Rawaqah <rawaqah@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsNotOpenNextDay() {
        $timebase = time();

        $time = strtotime('8 am', strtotime("+1 day", $timebase));

        $from = strtotime('9 am', strtotime("+1 day", $timebase));
        $until = strtotime('11 am', strtotime("+1 day", $timebase));

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w', $time),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));

        $this->assertFalse($this->_service->getOpening()->isOpen($time), sprintf('service #%d is open at %s but NOT should be, because normal opening was added before', $this->_service->getId(), date('l, d.m.Y H:i:s', $time)));
    }

    /**
     * time() is in timeslot at day 10
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.05.2012
     */
    public function testIsOpenHoliday() {
        $timeBuffer = time();
        // remove holiday to avoid duplicate entries
        $holidayTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        $holidayTable->delete(sprintf('date = "%s" and stateId = %d', date('Y-m-d'), $this->_service->getCity()->getStateId()));

        // add holiday in state of service
        $inserted = $holidayTable->insert(array('date' => date('Y-m-d'), 'stateId' => $this->_service->getCity()->getStateId()));
        $this->assertGreaterThan(0, $inserted);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addHolidayOpening(
                        array(
                            'from' => date('H:i:s', strtotime('-10 minutes', $timeBuffer)),
                            'until' => date('H:i:s', strtotime('+10 minutes', $timeBuffer))
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen(), $this->_service->getId());
        $this->assertFalse($this->_service->getOpening()->isOpen(strtotime('+12 minutes', $timeBuffer)), $this->_service->getId());
        $this->assertFalse($this->_service->getOpening()->isOpen(strtotime('-12 minutes', $timeBuffer)), $this->_service->getId());
    }

    /**
     * time() is in timeslot of weekday
     * - service has no holiday opening (day 10)
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function testIsOpenNoHolidayOpening() {
        $timeBuffer = time();
        // remove holiday to avoid duplicate entries
        $holidayTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        $holidayTable->delete(sprintf('date = "%s" and stateId = %d', date('Y-m-d'), $this->_service->getCity()->getStateId()));

        // add holiday in state of service
        $inserted = $holidayTable->insert(array('date' => date('Y-m-d'), 'stateId' => $this->_service->getCity()->getStateId()));
        $this->assertGreaterThan(0, $inserted);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', strtotime('-10 minutes', $timeBuffer)),
                            'until' => date('H:i:s', strtotime('+10 minutes', $timeBuffer))
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen());
        $this->assertFalse($this->_service->getOpening()->isOpen(strtotime('+12 minutes', $timeBuffer)));
        $this->assertFalse($this->_service->getOpening()->isOpen(strtotime('-12 minutes', $timeBuffer)));
    }

    /**
     * timeslot 00:00:00 - 00:00:00 at day 10
     * - no opening at weekday
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function testIsNotOpenHoliday() {
        // remove holiday to avoid duplicate entries
        $holidayTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        $holidayTable->delete(sprintf('date = "%s" and stateId = %d', date('Y-m-d'), $this->_service->getCity()->getStateId()));

        // add holiday in state of service
        $inserted = $holidayTable->insert(array('date' => date('Y-m-d'), 'stateId' => $this->_service->getCity()->getStateId()));
        $this->assertGreaterThan(0, $inserted);

        $this->assertFalse($this->_service->getOpening()->isOpen());
    }

    /**
     * - holiday today
     * - no opening at weekday
     * - no opening at day 10
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsNotOpenNoHolidayOpening() {

        $name = "test";
        $date = date('Y-md');
        $stateId = $this->_service->getCity()->getStateId();


        $holiday = new Yourdelivery_Model_Servicetype_OpeningsHolidays();
        $holiday->setName($name);
        $holiday->setDate($date);
        $holiday->setStateId($stateId);
        $holiday->save();


        $this->assertEquals($holiday->getStateId(), $this->_service->getCity()->getStateId());

        $this->assertFalse($this->_service->getOpening()->isOpen(), $this->_service->getId());

        $holiday->getTable()->getCurrent()->delete();
    }

    /**
     * - special opening
     * - no normal opening
     * - no holiday opening
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsOpenSpecialOpeningToday() {
        $timeBuffer = time();
        $from = strtotime("-10 minutes", $timeBuffer);
        $until = strtotime("+10 minutes", $timeBuffer);


        $this->assertGreaterThan(0, $this->_service->getOpening()->addSpecialOpening(
                        array(
                            'specialDate' => date('Y-m-d'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until),
                            'closed' => 0,
                            'description' => 'testIsOpenSpecialOpeningToday'
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen());
    }

    /**
     * special opening
     * - normal opening in timeslot
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsOpenSpecialOpeningWithOpeningToday() {

        $timeBuffer = time();
        $from = strtotime("-10 minutes", $timeBuffer);
        $until = strtotime("+10 minutes", $timeBuffer);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));


        $this->assertGreaterThan(0, $this->_service->getOpening()->addSpecialOpening(
                        array(
                            'specialDate' => date('Y-m-d'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until),
                            'closed' => 0,
                            'description' => 'testIsOpenSpecialOpeningToday'
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 11.06.2012
     */
    public function testIsOpenHolidayAndSpecial() {
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => 10,
                            'from' => date('H:i:s', strtotime('+2 minutes')),
                            'until' => date('H:i:s', strtotime('+4 minutes'))
                )));

        // clear holidays for today and state of new service
        $db = $this->_getDbAdapter();
        $db->query(sprintf('DELETE FROM restaurant_openings_holidays WHERE stateId = %d AND date = "%s"', $this->_service->getCity()->getStateId(), date('Y-m-d')));

        $this->assertGreaterThan(0, $this->_service->getOpening()->addSpecialOpening(
                        array(
                            'specialDate' => date('Y-m-d'),
                            'from' => '00:00:00',
                            'until' => '24:00:00',
                            'closed' => 0
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen(), sprintf('service #%d is not open, but should be because of special opening', $this->_service->getId()));
    }

    /**
     * - special opening
     * - holiday opening at day 10 (not in timeslot)
     * - no normal opening
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function testIsOpenSpecialOpeningAtHoliday() {
        $timeBuffer = time();
        // remove holiday to avoid duplicate entries
        $holidayTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        $holidayTable->delete(sprintf('date = "%s" and stateId = %d', date('Y-m-d'), $this->_service->getCity()->getStateId()));

        // add holiday in state of service
        $inserted = $holidayTable->insert(array('date' => date('Y-m-d'), 'stateId' => $this->_service->getCity()->getStateId()));
        $this->assertGreaterThan(0, $inserted);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addHolidayOpening(
                        array(
                            'from' => date('H:i:s', strtotime('-10 minutes', $timeBuffer)),
                            'until' => date('H:i:s', strtotime('-5 minutes', $timeBuffer))
                )));

        $this->assertGreaterThan(0, $this->_service->getOpening()->addSpecialOpening(
                        array(
                            'specialDate' => date('Y-m-d'),
                            'from' => date('H:i:s', strtotime('-10 minutes', $timeBuffer)),
                            'until' => date('H:i:s', strtotime('+10 minutes', $timeBuffer)),
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen());
    }

    /**
     * - special opening
     * - holiday opening at weekday
     * - no normal opening
     *
     * @author Andre Ponert <ponert@lieferando.de>
     */
    public function testIsOpenSpecialOpeningAtHolidayNoOpening() {
        $timeBuffer = time();
        // preparing time statements
        $from = strtotime('-1 minute', $timeBuffer);
        $until = strtotime('+1 minute', $timeBuffer);

        // remove holiday to avoid duplicate entries
        $holidayTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        $holidayTable->delete(sprintf('date = "%s" and stateId = %d', date('Y-m-d'), $this->_service->getCity()->getStateId()));

        // add holiday in state of service
        $inserted = $holidayTable->insert(array('date' => date('Y-m-d'), 'stateId' => $this->_service->getCity()->getStateId()));
        $this->assertGreaterThan(0, $inserted);


        // Assert, that openings have been added correctly
        $this->assertGreaterThan(0, $this->_service->getOpening()->addHolidayOpening(
                        array(
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                ))
        );

        // preparing special opening
        $this->assertGreaterThan(0, $this->_service->getOpening()->addSpecialOpening(
                        array(
                            'specialDate' => date('Y-m-d'),
                            'from' => date('H:i:s', strtotime('+4 minutes', $timeBuffer)),
                            'until' => date('H:i:s', strtotime('+6 minutes', $timeBuffer))
                ))
        );

        $this->assertTrue($this->_service->getOpening()->isOpen(strtotime('+5 minutes', $timeBuffer)), $this->_service->getId());
    }

    /**
     * - normal opening overlapping midnight
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 31.05.2012
     */
    public function testIsOpenIfTimeslotOverlapsMidnight() {
        $currentTime = time();
        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => '23:00:00',
                            'until' => '24:00:00'
                )));

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w', strtotime('tomorrow', $currentTime)),
                            'from' => '00:00:00',
                            'until' => '01:00:00'
                )));

        $this->assertTrue($this->_service->getOpening()->isOpen(strtotime('24:00:00', $currentTime)), sprintf('service #%d is not open at "%s", althought it has to be opens', $this->_service->getId(), date('d.m.Y H:i', strtotime('24:00:00', $currentTime))));
        $this->assertTrue($this->_service->getOpening()->isOpen(strtotime('1 am', strtotime('tomorrow', $currentTime))), $this->_service->getId());
        $this->assertTrue($this->_service->getOpening()->isOpen(strtotime('24:00:00', $currentTime)), $this->_service->getId());
    }

    /**
     * -normal Opening
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.06.2012
     */
    public function testGetIntervalOfDayNormal() {

        $from = strtotime("-10 minutes");
        $until = strtotime("+10 minutes");

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));

        $now = time();
        $openings = $this->_service->getOpening()->getIntervalOfDay($now);

        $this->assertEquals(1, count($openings));
        #$this->assertEquals(2, count($openings[$now]), $this->_service->getId());
        $this->assertEquals(date('H:i', $from), $openings[$now][0]['from']);
        $this->assertEquals(date('H:i', $until), $openings[$now][0]['until']);
    }

    /**
     * -normal multiple Openings
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.06.2012
     */
    public function testGetIntervalOfDayNormalMultiple() {
        $timeBuffer = time();
        $from = strtotime("-5 minutes", $timeBuffer);
        $until = strtotime("+5 minutes", $timeBuffer);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));

        $from2 = strtotime("+1 minute", $timeBuffer);
        $until2 = strtotime("+2 minute", $timeBuffer);


        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from2),
                            'until' => date('H:i:s', $until2)
                )));

        $time = time();
        $openings = $this->_service->getOpening()->getIntervalOfDay($time);

        $this->assertEquals(1, count($openings));
        #$this->assertEquals(3, count($openings[$time]), $this->_service->getId());
        $this->assertEquals(date('H:i', $from), $openings[$time][0]['from'], $this->_service->getId());
        $this->assertEquals(date('H:i', $until), $openings[$time][0]['until'], $this->_service->getId());
        $this->assertEquals(date('H:i', $from2), $openings[$time][1]['from'], $this->_service->getId());
        $this->assertEquals(date('H:i', $until2), $openings[$time][1]['until'], $this->_service->getId());
    }

    /**
     * -normal Opening
     * -normal Special Opening
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.06.2012
     */
    public function testGetIntervalOfDaySpecial() {
        $timeBuffer = time();
        $from = strtotime("-5 minutes", $timeBuffer);
        $until = strtotime("+5 minutes", $timeBuffer);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                )));

        $fromSpecial = strtotime("-10 minutes", $timeBuffer);
        $untilSpecial = strtotime("+10 minutes", $timeBuffer);


        $this->assertGreaterThan(0, $this->_service->getOpening()->addSpecialOpening(
                        array(
                            'specialDate' => date('Y-m-d'),
                            'from' => date('H:i:s', $fromSpecial),
                            'until' => date('H:i:s', $untilSpecial),
                )));

        $now = time();
        $openings = $this->_service->getOpening()->getIntervalOfDay($now);

        $this->assertEquals(1, count($openings));
        #$this->assertEquals(2, count($openings[$now]));
        $this->assertEquals(date('H:i', $fromSpecial), $openings[$now][0]['from']);
        $this->assertEquals(date('H:i', $untilSpecial), $openings[$now][0]['until']);
    }

    /**
     * -normal Opening
     * - Holiday & HolidayOpening
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.06.2012
     */
    public function testGetIntervalOfDayHoliday() {

        $timeBuffer = time();
        $name = "test";
        $date = date('Y-m-d');
        $stateId = $this->_service->getCity()->getStateId();


        $holiday = new Yourdelivery_Model_Servicetype_OpeningsHolidays();
        $holidayTable = $holiday->getTable();
        $holidayTable->delete(sprintf('stateId = %d', $this->_service->getCity()->getStateId()));
        $holiday->setName($name);
        $holiday->setDate($date);
        $holiday->setStateId($stateId);
        $holiday->save();


        $this->assertEquals($holiday->getStateId(), $this->_service->getCity()->getStateId());

        $from = strtotime("-5 minutes", $timeBuffer);
        $until = strtotime("+5 minutes", $timeBuffer);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                        )
                )
        );

        $fromHoliday = strtotime('-10 minutes', $timeBuffer);
        $untilHoliday = strtotime('+10 minutes', $timeBuffer);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addHolidayOpening(
                        array(
                            'from' => date('H:i:s', $fromHoliday),
                            'until' => date('H:i:s', $untilHoliday)
                ))
        );

        $now = time();
        $openings = $this->_service->getOpening()->getIntervalOfDay(time());
        $this->_service->getOpening()->clearCache();
        
        
        $this->assertEquals(1, count($openings));
        $this->assertEquals(date('H:i', $fromHoliday), $openings[$now][0]['from'], sprintf('restaurantId #%s - openings: %s', $this->_service->getId(), print_r($openings, true)));
        $this->assertEquals(date('H:i', $untilHoliday), $openings[$now][0]['until'], sprintf('restaurantId #%s - openings: %s', $this->_service->getId(), print_r($openings, true)));

        $holiday->getTable()->getCurrent()->delete();
    }

    /**
     * -normal Opening
     * - Holiday & no HolidayOpening
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.06.2012
     */
    public function testGetIntervalOfDayHolidayClosed() {
        $timeBuffer = time();
        $name = "test";
        $date = date('Y-m-d');
        $now = time();
        $stateId = $this->_service->getCity()->getStateId();

        $holiday = new Yourdelivery_Model_Servicetype_OpeningsHolidays();
        $holiday->setName($name);
        $holiday->setDate($date);
        $holiday->setStateId($stateId);
        $holiday->save();

        $this->assertEquals($holiday->getStateId(), $this->_service->getCity()->getStateId());

        $from = strtotime("-5 minutes", $timeBuffer);
        $until = strtotime("+5 minutes", $timeBuffer);

        $this->assertGreaterThan(0, $this->_service->getOpening()->addNormalOpening(
                        array(
                            'day' => date('w'),
                            'from' => date('H:i:s', $from),
                            'until' => date('H:i:s', $until)
                        )
                )
        );

        $openings = $this->_service->getOpening()->getIntervalOfDay($now);
        // should be open cause there are no specialopening
        $this->assertTrue($this->_service->getOpening()->isOpen($now), $this->_service->getId());
        $this->assertEquals(1, count($openings), $this->_service->getId());

        $this->assertGreaterThan(0, $this->_service->getOpening()->addSpecialOpening(
                        array(
                            'specialDate' => $date,
                            'closed' => 1,
                            'description' => 'testGetIntervalOfDayHolidayClosed'
                        )
                )
        );

        $this->_service->getOpening()->clearCache();

        $this->assertFalse($this->_service->getOpening()->isOpen($now), $this->_service->getId());

        $holiday->getTable()->getCurrent()->delete();
    }

    /**
     * - normal Opening
     * - Holiday & Special Opening
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.06.2012
     */
    public function testGetIntervalOfDayHolidaySpecial() {
        $service = $this->_service;
        $timeBuffer = time();
        $name = "test";
        $date = date('Y-m-d');
        $stateId = $this->_service->getCity()->getStateId();

        $holiday = new Yourdelivery_Model_Servicetype_OpeningsHolidays();
        $holiday->setName($name);
        $holiday->setDate($date);
        $holiday->setStateId($stateId);
        $holiday->save();


        // create normal opening
        $service->getOpening()->addNormalOpening(
                array(
                    'day' => date('w', strtotime('-2 minutes')),
                    'from' => date('H:i:s', strtotime('-2 minutes')),
                    'until' => date('H:i:s', strtotime('+4 minutes'))
        ));


        $this->assertGreaterThan(0, $holiday->getId());

        $service->getOpening()->addHolidayOpening(
                array(
                    'from' => date('H:i:s', strtotime('-4 minutes', $timeBuffer)),
                    'until' => date('H:i:s', strtotime('+6 minutes', $timeBuffer))
        ));

        // add special opening
        $in8min = strtotime('+8 minutes', $timeBuffer);
        $in10min = strtotime('+10 minutes', $timeBuffer);
        $service->getOpening()->addSpecialOpening(
                array(
                    'specialDate' => date('Y-m-d', $in8min),
                    'from' => date('H:i:s', $in8min),
                    'until' => date('H:i:s', $in10min)
        ));

        $time = time();
        $interval = $service->getOpening()->getIntervalOfDay($time);
        $this->assertEquals(date('H:i', $in8min), $interval[$time][0]['from'], 'service #' . $service->getId());
        $this->assertEquals(date('H:i', $in10min), $interval[$time][0]['until'], 'service #' . $service->getId());

        $holiday->getTable()->getCurrent()->delete();
    }

    /**
     * @author Felix Haferkron <haferkorn@lieferando.de>
     * @since 08.06.2012
     */
    public function testIntervalOfDay() {
        $time = time();
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));

        $intervalOfDay = $this->_service->getOpening()->getIntervalOfDay($time);

        $firstInterval = array_pop($intervalOfDay);
        $this->assertEquals('07:00', $firstInterval[0]['from']);
        $this->assertEquals('10:00', $firstInterval[0]['until']);
    }

    /**
     * @author Felix Haferkron <haferkorn@lieferando.de>
     * @since 08.06.2012
     */
    public function testIntervalOfDayMultiple() {
        $time = time();
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '15:00:00'
        ));


        $this->_service->getOpening()->clearCache();

        $intervalOfDay = $this->_service->getOpening()->getIntervalOfDay($time);

        $firstInterval = array_pop($intervalOfDay);
        $this->assertEquals('07:00', $firstInterval[0]['from']);
        $this->assertEquals('10:00', $firstInterval[0]['until']);

        $this->assertEquals('11:00', $firstInterval[1]['from']);
        $this->assertEquals('15:00', $firstInterval[1]['until']);
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 15.06.2012
     */
    public function testViewHelperFormatOpeningsToday() {
        $service = $this->_service;
        $time = strtotime('11am');
        $service->getOpening()->addNormalOpening(array(
            'restaurantId' => $service->getId(),
            'day' => date('w', $time),
            'from' => '08:00:00',
            'until' => '12:00:00'
        ));

        $openings = $service->getOpening()->getIntervalOfDay(strtotime('today', $time));

        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpenings($openings);

        $expected = sprintf('%s', __('%s bis %s', '08:00', '12:00'));
        $this->assertEquals($expected, $merged);

        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpenings($openings, 'linebreak');

        $expected = null;
        $expected = sprintf('%s', __('%s bis %s', '08:00', '12:00'));
        $this->assertEquals($expected, $merged);

        $service->getOpening()->addNormalOpening(array(
            'restaurantId' => $service->getId(),
            'day' => date('w', $time),
            'from' => '14:00:00',
            'until' => '20:00:00'
        ));

        $service->getOpening()->clearCache();

        $openings = $service->getOpening()->getIntervalOfDay(strtotime('today', $time));

        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpenings($openings);

        $expected = null;
        $expected = sprintf('%s %s %s', __('%s bis %s', '08:00', '12:00'), __('und'), __('%s bis %s', '14:00', '20:00'));
        $this->assertEquals($expected, $merged, $service->getId());


        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpenings($openings, 'table');

        $expected = null;
        $expected = sprintf('%s %s %s', __('%s bis %s', '08:00', '12:00'), __('und'), __('%s bis %s', '14:00', '20:00'));
        $this->assertEquals($expected, $merged, $service->getId());
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 08.06.2012
     */
    public function testViewHelperFormatOpeningsMergedToday() {
        $service = $this->_service;
        $time = strtotime('11am');
        $service->getOpening()->addNormalOpening(array(
            'restaurantId' => $service->getId(),
            'day' => date('w', $time),
            'from' => '08:00:00',
            'until' => '12:00:00'
        ));

        $openings = $service->getOpening()->getIntervalOfDay(strtotime('today', $time));

        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpeningsMerged($openings);

        $expected = sprintf('%s:%s', strftime('%a', $time), '&nbsp;' . __('%s bis %s', '08:00', '12:00<br />'));
        $this->assertEquals($expected, $merged, $service->getId());

        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpeningsMerged($openings, 'linebreak');

        $expected = null;
        $expected = sprintf('%s:%s', strftime('%a', $time), ' ' . __('%s bis %s', '08:00', '12:00') . "\n");
        $this->assertEquals($expected, $merged, $service->getId());

        $service->getOpening()->addNormalOpening(array(
            'restaurantId' => $service->getId(),
            'day' => date('w', $time),
            'from' => '14:00:00',
            'until' => '20:00:00'
        ));

        $service->getOpening()->clearCache();

        $openings = $service->getOpening()->getIntervalOfDay(strtotime('today', $time));

        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpeningsMerged($openings);

        $expected = null;
        $expected = sprintf('%s:%s %s %s', strftime('%a', $time), __('&nbsp;%s bis %s', '08:00', '12:00'), __('und'), __('%s bis %s', '14:00', '20:00<br />'));
        $this->assertEquals($expected, $merged, $service->getId());


        $helper = new Default_View_Helper_Openings_Format();
        $merged = $helper->formatOpeningsMerged($openings, 'table');

        $expected = null;
        $expected = sprintf('%s%s %s %s', '<tr><td>' . strftime('%a', $time) . '</td><td>', __('%s bis %s', '08:00', '12:00'), __('und'), __('%s bis %s', '14:00', '20:00') . '</td></tr>');
        $this->assertEquals($expected, $merged, $service->getId());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.06.2012
     */
    public function testViewHelperFormatOpeningsMerged() {

        $time = strtotime('3am');

        // add three openings today
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '15:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '17:00:00',
            'until' => '24:00:00'
        ));

        $tomorrow = strtotime('tomorrow', $time);

        $view = new Default_View_Helper_Openings_Format();

        $mergedOpeningsHtml = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervals($time, $tomorrow));
        $expectedOpeningsHtml = sprintf('%s:&nbsp;%s %s %s %s %s<br />%s:&nbsp;%s', strftime('%a', $time), __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s', '11:00', '15:00'), __('und'), __('%s bis %s', '17:00', '24:00') , strftime('%a', $tomorrow) , __('geschlossen')  . '<br />');
        $this->assertEquals($expectedOpeningsHtml, $mergedOpeningsHtml, $this->_service->getId());

        $mergedOpeningsLinebreak = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervals($time, strtotime('tomorrow')), 'linebreak');
        $expectedOpeningsLinebreak = sprintf('%s: %s %s %s %s %s%s: %s', strftime('%a', $time), __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s', '11:00', '15:00'), __('und'), __('%s bis %s', '17:00', '24:00'). "\n", strftime('%a', $tomorrow),  __('geschlossen') . "\n");
        $this->assertEquals($expectedOpeningsLinebreak, $mergedOpeningsLinebreak, $this->_service->getId());

        $mergedOpeningsTable = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervals($time, strtotime('tomorrow')), 'table');
        $expectedOpeningsTable = sprintf('<tr><td>%s</td><td>%s %s %s %s %s</td></tr><tr><td>%s</td><td>%s</td></tr>', strftime('%a', $time), __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s', '11:00', '15:00'), __('und'), __('%s bis %s', '17:00', '24:00'), strftime('%a', $tomorrow),  __('geschlossen'));
        $this->assertEquals($expectedOpeningsTable, $mergedOpeningsTable, $this->_service->getId());
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 15.06.2012
     */
    public function testViewHelperFormatOpenings() {

        $time = strtotime('3am');

        // add three openings today
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '15:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '17:00:00',
            'until' => '24:00:00'
        ));

        $tomorrow = strtotime('+1day', $time);

        $view = new Default_View_Helper_Openings_Format();
        $mergedOpenings = $view->formatOpenings($this->_service->getOpening()->getIntervalOfDay($time));
        $this->assertEquals(sprintf('%s %s %s %s %s', __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s', '11:00', '15:00'), __('und'), __('%s bis %s', '17:00', '24:00')), $mergedOpenings, $this->_service->getId());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.06.2012
     */
    public function testViewHelperFormatOpeningsMergedWhichOverlapsMidnight() {

        $time = strtotime('3am');

        // add three openings today
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '24:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '00:00:00',
            'until' => '03:00:00'
        ));

        $view = new Default_View_Helper_Openings_Format();
        $mergedOpenings = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervalOfDay());
        $expected = sprintf('%s:&nbsp;%s %s %s<br />', strftime('%a', $time), __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s am Folgetag', '11:00', '03:00'));
        $this->assertEquals($expected, $mergedOpenings, sprintf('restaurantId #%s - openings: %s', $this->_service->getId(), print_r($mergedOpenings, true)));
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.06.2012
     */
    public function testViewHelperFormatOpeningsMergedOverlapsMidnightNotShownNextDay() {

        $time = strtotime('3am');

        // add three openings today
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '11:00:00',
            'until' => '24:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+2day', $time)),
            'from' => '00:00:00',
            'until' => '03:00:00'
        ));

        $view = new Default_View_Helper_Openings_Format();
        $mergedOpenings = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervals(strtotime('+1day', $time), strtotime('+2day', $time)));

        $expectedTomorrow = sprintf('%s:&nbsp;%s %s %s<br />', strftime('%a', strtotime('+1day', $time)), __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s am Folgetag', '11:00', '03:00'));
        $this->assertEquals($expectedTomorrow, $mergedOpenings, $this->_service->getId());


        // test with timeslot
        $this->_service = $this->createRestaurant();
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '24:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '00:00:00',
            'until' => '03:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '03:30:00',
            'until' => '05:00:00'
        ));

        $view = new Default_View_Helper_Openings_Format();
        // check openings today
        $mergedOpenings = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervalOfDay($time));
        $expectedToday = sprintf('%s:&nbsp;%s %s %s<br />', strftime('%a', $time), __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s am Folgetag', '11:00', '03:00'));
        $this->assertEquals($expectedToday, $mergedOpenings, $this->_service->getId());

        // check openings tomorrow
        $mergedOpenings = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervalOfDay(strtotime('+1day', $time)));
        $expectedTomorrow = sprintf('%s:&nbsp;%s<br />', strftime('%a', strtotime('+1day', $time)), __('%s bis %s', '03:30', '05:00'));
        $this->assertEquals($expectedTomorrow, $mergedOpenings, $this->_service->getId());
    }

    /**
     *
     * @author Allen Frank <frank@lieferando.de>
     * @since 15.06.2012
     */
    public function testViewHelperFormatOpeningsOverlapsMidnightNotShownNextDay() {

        $time = strtotime('3am');

        // add three openings today
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '11:00:00',
            'until' => '24:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+2day', $time)),
            'from' => '00:00:00',
            'until' => '03:00:00'
        ));

        $view = new Default_View_Helper_Openings_Format();
        $mergedOpenings = $view->formatOpenings($this->_service->getOpening()->getIntervals(strtotime('+1day', $time), strtotime('+2day', $time)));

        $expectedTomorrow = sprintf('%s %s %s', __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s am Folgetag', '11:00', '03:00'));
        $this->assertEquals($expectedTomorrow, $mergedOpenings, $this->_service->getId());


        // test with timeslot
        $this->_service = $this->createRestaurant();
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '24:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '00:00:00',
            'until' => '03:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '03:30:00',
            'until' => '05:00:00'
        ));

        $view = new Default_View_Helper_Openings_Format();
        // check openings today
        $mergedOpenings = $view->formatOpenings($this->_service->getOpening()->getIntervalOfDay($time));
        $expectedToday = sprintf('%s %s %s', __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s am Folgetag', '11:00', '03:00'));
        $this->assertEquals($expectedToday, $mergedOpenings, $this->_service->getId());

        // check openings tomorrow
        $mergedOpenings = $view->formatOpenings($this->_service->getOpening()->getIntervalOfDay(strtotime('+1day', $time)));
        $expectedTomorrow = sprintf('%s', __('%s bis %s', '03:30', '05:00'));
        $this->assertEquals($expectedTomorrow, $mergedOpenings, $this->_service->getId());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.06.2012
     */
    public function testViewHelperFormatOpeningsMergedOverlapsMidnightNotShownNextDayWithSpecialOpening() {

        $time = strtotime('3am');

        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '24:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '00:00:00',
            'until' => '03:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '03:30:00',
            'until' => '05:00:00'
        ));

        $this->_service->getOpening()->addSpecialOpening(
                array(
                    'specialDate' => date('Y-m-d', strtotime('+1day', $time)),
                    'from' => '00:00:00',
                    'until' => '00:00:00',
                    'closed' => true
        ));

        $view = new Default_View_Helper_Openings_Format();
        // check openings today
        $mergedOpenings = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervalOfDay($time));
        $expectedToday = sprintf('%s:&nbsp;%s %s %s<br />', strftime('%a', $time), __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s', '11:00', '24:00'));
        $this->assertEquals($expectedToday, $mergedOpenings, $this->_service->getId());

        // check openings tomorrow
        $mergedOpenings = $view->formatOpeningsMerged($this->_service->getOpening()->getIntervalOfDay(strtotime('+1day', $time)));
        $expectedTomorrow = sprintf('%s:&nbsp;%s<br />', strftime('%a', strtotime('+1day', $time)), __('geschlossen'));
        $this->assertEquals($expectedTomorrow, $mergedOpenings, $this->_service->getId());
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 15.06.2012
     */
    public function testViewHelperFormatOpeningsOverlapsMidnightNotShownNextDayWithSpecialOpening() {

        $time = strtotime('3am');

        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '07:00:00',
            'until' => '10:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', $time),
            'from' => '11:00:00',
            'until' => '24:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '00:00:00',
            'until' => '03:00:00'
        ));
        $this->_service->getOpening()->addNormalOpening(array(
            'day' => date('w', strtotime('+1day', $time)),
            'from' => '03:30:00',
            'until' => '05:00:00'
        ));

        $this->_service->getOpening()->addSpecialOpening(
                array(
                    'specialDate' => date('Y-m-d', strtotime('+1day', $time)),
                    'from' => '00:00:00',
                    'until' => '00:00:00',
                    'closed' => true
        ));

        $view = new Default_View_Helper_Openings_Format();
        // check openings today
        $mergedOpenings = $view->formatOpenings($this->_service->getOpening()->getIntervalOfDay($time));
        $expectedToday = sprintf('%s %s %s', __('%s bis %s', '07:00', '10:00'), __('und'), __('%s bis %s', '11:00', '24:00'));
        $this->assertEquals($expectedToday, $mergedOpenings, $this->_service->getId());

        // check openings tomorrow
        $mergedOpenings = $view->formatOpenings($this->_service->getOpening()->getIntervalOfDay(strtotime('+1day', $time)));
        $expectedTomorrow = __('geschlossen');
        $this->assertEquals($expectedTomorrow, $mergedOpenings, $this->_service->getId());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 08.06.2012
     */
    public function testGenerateDays() {

        $start = 1;
        $end = 6;

        $table = new Yourdelivery_Model_DbTable_Restaurant_Openings();

        $days = $table->generateDays($start, $end);

        $this->assertEquals(count($days), 6);
        $this->assertEquals($days[0], 1);
        $this->assertEquals($days[$end], 0);

        $start = 4;
        $end = 2;
        $days = $table->generateDays($start, $end);

        $this->assertEquals(count($days), 6);
        $this->assertEquals($days[0], 4);
        $this->assertEquals($days[(count($days) - 1 )], 2);
    }

    /**
     * test interval today without having opning today
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.08.2012
     */
    public function testOpeningNoNormalOpeningToday() {
        $service = $this->_service;

        $timeBuffer = time();
        // remove holiday to avoid duplicate entries
        $holidayTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        $holidayTable->delete(sprintf('date = "%s" and stateId = %d', date('Y-m-d'), $this->_service->getCity()->getStateId()));

        $service->getOpening()->addNormalOpening(
                array(
                    'day' => date('w', strtotime("+1 day", $timeBuffer)),
                    'from' => date('H:i:00', strtotime("-10 minutes", $timeBuffer)),
                    'until' => date('H:i:00', strtotime("+10 minutes", $timeBuffer))
                )
        );

        $service->getOpening()->clearCache();

        $openings = $service->getOpening()->getIntervalOfDay(time());
        $helper = new Default_View_Helper_Openings_Format();
        $this->assertEquals(__('geschlossen'), $helper->formatOpenings($openings));

        $this->assertFalse($service->getOpening()->isOpen());
    }

}
