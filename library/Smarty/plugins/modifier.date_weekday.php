<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.date_weekday.php
 * Type:     modifier
 * -------------------------------------------------------------
 */

function smarty_modifier_date_weekday($time)
{
	$days = array('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag');
    return $days[$time];
}
?>