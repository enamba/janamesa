<?php

/**
 * Smarty plugin to execute PHP code
 * 
 * @package Smarty
 * @subpackage PluginsBlock
 * @author Matthias Laug 
 */

/**
 * Smarty {obfuscator}{/obfuscator} block plugin
 * 
 * @param string $content contents of the block
 * @param object $template template object
 * @param boolean $ &$repeat repeat flag
 * @return string content re-formatted
 */
function smarty_block_obfuscator($params, $content, $template, &$repeat) {
    if (!$repeat) {
        if (isset($content)) {
            return $content;
            return "var serviceData = $.base64.decode('" . base64_encode($content) . "');eval(serviceData);";
        }
    }
}

?>