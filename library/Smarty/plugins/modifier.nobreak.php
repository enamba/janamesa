<?php

/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * @author mlaug
 * @since 20.05.2011
 * @param string $text
 * @return string
 */
function smarty_modifier_nobreak($text) {
    return str_replace(' ', '&nbsp;', $text);
}

?>
