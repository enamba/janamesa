<?php

/**
 * @package helper
 * @author vpriem
 * @since 24.08.2010
 */
class Default_Helpers_Date {

    /**
     * Get days
     * @author vpriem
     * @since 14.04.2011
     * @param boolean $short
     * @return array
     */
    public static function getDays($short = false) {

        if ($short) {
            return array(
                __("So"),
                __("Mo"),
                __("Di"),
                __("Mi"),
                __("Do"),
                __("Fr"),
                __("Sa"),
                __("So"),
                '',
                '',
                __('FT')
            );
        }

        return array(
            __('Sonntag'),
            __('Montag'),
            __('Dienstag'),
            __('Mittwoch'),
            __('Donnerstag'),
            __('Freitag'),
            __('Samstag'),
            __('Sonntag'),
            '',
            '',
            __('Feiertag')
        );
    }

    /**
     * Get week days
     * @author vpriem
     * @since 24.08.2010
     * @param int|string $timestamp
     * @return array
     */
    public static function getWeekDays($timestamp = null) {

        if ($timestamp === null) {
            $timestamp = mktime(0, 0, 0);
        } else {
            if (!is_numeric($timestamp)) {
                $timestamp = strtotime($timestamp);
                if ($timestamp === false) {
                    $timestamp = time();
                }
            }
            $timestamp = mktime(0, 0, 0, date("n", $timestamp), date("j", $timestamp), date("Y", $timestamp));
        }

        while (date("N", $timestamp) > 1) {
            $timestamp -= 24 * 60 * 60;
        }

        $days = array();
        for ($i = 1; $i < 8; $i++) {
            $days[$i] = $timestamp;
            $timestamp += 24 * 60 * 60;
        }
        $days[0] = $days[7];
        unset($days[7]);
        ksort($days);

        return $days;
    }

    /**
     * Check german date format and validate it
     *
     * @author vpriem
     * @param string $date (dd.mm.yyyy)
     * @return array|boolean
     */
    public static function isDate($date) {

        if (preg_match("/^([0-9]{1,2})[\.\-\/]([0-9]{1,2})[\.\-\/]([0-9]{4})$/", $date, $matches)) {
            if (checkdate($matches[2], $matches[1], $matches[3])) {
                return array_combine(array('date', 'd', 'm', 'y'), $matches);
            }
        }

        if (preg_match("/^([0-9]{4})[\.\-\/]([0-9]{1,2})[\.\-\/]([0-9]{1,2})$/", $date, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return array_combine(array('date', 'y', 'm', 'd'), $matches);
            }
        }

        return false;
    }

    public static function getWorkingWeekDays($timestamp = null) {

        if ($timestamp === null) {
            $timestamp = mktime(0, 0, 0);
        } else {
            if (!is_numeric($timestamp)) {
                $timestamp = strtotime($timestamp);
                if ($timestamp === false) {
                    $timestamp = time();
                }
            }
            $timestamp = mktime(0, 0, 0, date("n", $timestamp), date("j", $timestamp), date("Y", $timestamp));
        }

        while (date("N", $timestamp) > 1) {
            $timestamp -= 24 * 60 * 60;
        }

        $days = array();
        for ($i = 1; $i < 6; $i++) {
            $days[$i] = $timestamp;
            $timestamp += 24 * 60 * 60;
        }
        ksort($days);

        return $days;
    }

    public static function getWeekDay($timestamp) {
        return date("N", $timestamp);
    }

    /**
     * try to approximate a timezone which is nearest to the
     * desired time. keep in mind, we only may change time by
     * hourse not minutes!
     * @param string $desiredTime
     * @return boolean
     */
    static public function setTimezoneByDesiredTime($desiredTime) {

        //calculate offset (approximation)
        $current = time();
        $wanted = strtotime($desiredTime);
        $offset = (integer) round(($wanted - $current) / 60 / 60);

        //get offset
        $testTimestamp = time();
        date_default_timezone_set('Europe/Berlin');
        $testLocaltime = localtime($testTimestamp, true);
        $testHour = $testLocaltime['tm_hour'];

        //try to find nearest timezone
        $abbrarray = timezone_abbreviations_list();
        foreach ($abbrarray as $abbr) {
            foreach ($abbr as $city) {
                date_default_timezone_set($city['timezone_id']);
                $testLocaltime = localtime($testTimestamp, true);
                $hour = $testLocaltime['tm_hour'];
                $testOffset = $hour - $testHour;
                if ($testOffset == $offset) {
                    return true;
                }
            }
        }

        //nothing found
        date_default_timezone_set('Europe/Berlin');
        return false;
    }

    /**
     * reset current timezone to default
     * @author mlaug
     * @since 24.10.2010
     */
    public static function resetTimezone() {
        date_default_timezone_set('Europe/Berlin');
    }

    /**
     * check if we have a holiday
     * @author mlaug
     * @return boolean
     */
    public static function ohHappyDay() {
        return false;
    }

    /**
     * Get the first monday before or on the start of the month
     * @author alex
     * @since 06.12.2010
     * @param int $month
     * @return timestamp
     */
    public static function getLastMontayBeforeStartOfMonth($month = null) {
        if ($month === null) {
            $timestamp = mktime(0, 0, 0, date("n"), 1);
        } else {
            $timestamp = mktime(0, 0, 0, $month, 1);
        }

        while (date("N", $timestamp) > 1) {
            $timestamp -= 24 * 60 * 60;
        }

        return $timestamp;
    }

    /**
     * give integer, get day of week (long or short)
     * @author mlaug
     * @param int day
     * @param boolean short
     * @return string
     */
    public static function int2day($int, $short = false) {
        if (!is_null($int)) {
            switch ($int) {
                case 0: return !$short ? __('Sonntag') : __('So');
                case 1: return !$short ? __('Montag') : __('Mo');
                case 2: return !$short ? __('Dienstag') : __('Di');
                case 3: return !$short ? __('Mittwoch') : __('Mi');
                case 4: return !$short ? __('Donnerstag') : __('Do');
                case 5: return !$short ? __('Freitag') : __('Fr');
                case 6: return !$short ? __('Samstag') : __('Sa');
                case 10: return !$short ? __('Feiertag') : __('FT');
                default: return null;
            }
        }
        return null;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 10.04.2012 
     */
    public static function getMonthNames() {
        return array(
            __('Januar'),
            __('Februar'),
            __('März'),
            __('April'),
            __('Mai'),
            __('Juni'),
            __('Juli'),
            __('August'),
            __('September'),
            __('Oktober'),
            __('November'),
            __('Dezember')
        );
    }

}
