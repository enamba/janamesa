<?php

/**
 * Description of Openings
 *
 * @author mlaug
 */
class Default_View_Helper_Openings_Format extends Zend_View_Helper_Abstract {

    /**
     * format the openings and make sure the openings of the next
     * day are included as well, only if openings are overlapping midnight
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.05.2012
     * @param array $openings
     * @param boolean $next
     * @return string
     */
    public function formatOpenings(array $openings) {
        $stringOpenings = array();
        foreach ($openings as $openingsOfDay) {

            foreach ($openingsOfDay as $ind => $opening) {

                if ($opening['closed']) {
                    $stringOpenings[] = __('geschlossen');
                    continue;
                }

                if (strcmp($ind, 'next') == 0) {
                    continue;
                }
                $stringOpenings[] = __('%s bis %s', $opening['from'], $opening['until']);
                if ($this->hasOpeningFromMidnight($opening)) {
                    array_pop($stringOpenings);
                }
            }

            $lastIntervalOfDay = $openingsOfDay[count($openingsOfDay) - 2];
            if ($this->isDayExceedingMidnight($openingsOfDay)) {
                $stringOpenings = array_slice($stringOpenings, 0, -1); //remove last entry, because we have overlapping times from this to the next day
                $stringOpenings[] = __('%s bis %s am Folgetag', $lastIntervalOfDay['from'], $openingsOfDay['next'][0]['until']);
            }
        }
        return implode(' ' . __('und') . ' ', $stringOpenings);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param array $openings
     */
    public function formatOpeningsMerged(array $openings, $format = null) {
        $merged = array();

        //preorder by weekdays
        uksort($openings, function($a, $b) {
                    $first = date('w', $a);
                    $second = date('w', $b);
                    
                    //first argument is sunday, make it highest
                    if ( $first%7 == 0 ){
                        return 10;
                    }
                    
                    //second argument is sunday, make it lowest
                    if ( $second%7 == 0 ){
                        return -10;
                    }
                    
                    //otherwise compare
                    return $first - $second;
                });

        foreach ($openings as $timestamp => $openingsOfDay) {

            $ind = $this->formatOpenings(array($openingsOfDay));

            if (!is_array($merged[$ind])) {
                $merged[$ind] = array();
            }

            if (strlen($ind) > 0) {
                $merged[$ind][] = strftime('%a', $timestamp);
            }
        }

        $mergedString = '';
        foreach ($merged as $interval => $merge) {

            if (strlen($interval) <= 0) {
                continue;
            }
            switch ($format) {
                default:
                    // html
                    $mergedString .= implode(',', $merge) . ':&nbsp;' . $interval . '<br />';
                    break;

                case 'linebreak':
                    $mergedString .= implode(',', $merge) . ': ' . $interval . "\n";
                    break;

                case 'table':
                    $mergedString .= '<tr><td>' . implode(',', $merge) . '</td><td>' . $interval . '</td></tr>';
                    break;
            }
        }

        return $mergedString;
    }

    /**
     * create an array and convert to json for javascript
     * caluclations
     *
     * @param array $openings
     * @return string
     */
    public function formatOpeningsAsJson(array $openings) {
        $intervals = array_pop($openings);
        $processedOpenings = array();
        if (is_array($intervals)) {
            foreach ($intervals as $i => $interval) {
                if (($i !== 'next') && !$interval['closed']) {
                    $processedOpenings[] = array(
                        $interval['timestamp_from'],
                        $interval['timestamp_until'] - 1, // hack for 24:00 timestamp
                    );
                }
            }
        }

        return json_encode($processedOpenings);
    }

    /**
     * create an option list from the given openings array
     *
     * @param array $openings
     * @param string $mode
     * @param int $serviceDeliverTime
     */
    public function formatOpeningsAsSelect($htmlStart, array $openings, $mode = "rest", $serviceDeliverTime = 0) {

        $now = time();
        if ($mode != "rest") {
            $now += (integer) $serviceDeliverTime;
        }

        $openings = array_pop($openings); //we only use the first index, since we do not show multple days here
        unset($openings['next']); //do not need that one

        $openingsSelect = '';
        foreach ($openings as $opening) {
            if ($opening['closed']) {
                continue;
            }

            $from = $opening['timestamp_from'];
            $until = $opening['timestamp_until'];

            while ($from <= $until) {

                // only allow times in future
                if ($from < $now) {
                    if ($until > $now && $mode == "rest" && $openingsSelect == '' && date('dmY', $from) == date('dmY')) {
                        $openingsSelect .= sprintf('<option value="%s">%s</option>', __('sofort'), __('sofort'));
                    }

                    $from += 60 * 15; //plus 15 minutes
                    continue;
                }

                $openingsSelect .= sprintf('<option value="%s">%s</option>', date('H:i', $from), date('H:i', $from));
                $from += 60 * 15; //plus 15 minutes
            }
        }

        // create select here
        if (strlen($openingsSelect)) {
            return $htmlStart . $openingsSelect . '</select>';
        }

        // return default closed info box
        return '<span class="yd-datepicker-info">' . __('An diesem Tag hat der Lieferservice geschlossen. Bitte wähle einen anderen Tag.') . '</span>';
    }

    /**
     * Checks if the current day exceeds midnight and the following day also starts at midnight
     * @author Allen Frank <frank@lieferando.de>
     * @param type $openingsOfDay
     * @return boolean
     */
    private function isDayExceedingMidnight($openingsOfDay) {
        $lastIntervalOfDay = $openingsOfDay[count($openingsOfDay) - 2];
        if (isset($openingsOfDay['next']) && //next must be set
                $openingsOfDay['next'][0]['closed'] == false &&
                $openingsOfDay['next'][0]['from'] == '00:00' && //next day openings must start at 00:00
                in_array($lastIntervalOfDay['until'], array('23:45', '24:00'))) {// last day openings must end at 24:00 or 23:45
            return true;
        }
        return false;
    }

    /**
     * Check if this opening begins ar Midnight and ends before 5 am
     * @author Allen Frank <frank@lieferando.de>
     * @param current day
     * @return boolean
     */
    private function hasOpeningFromMidnight($openings) {
        if ($openings['from'] == '00:00' && $openings['until'] <= '05:00') {
            return true;
        }

        return false;
    }

}
