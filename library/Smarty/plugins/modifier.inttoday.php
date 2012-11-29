<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.intoprice.php
 * Type:     modifier
 * -------------------------------------------------------------
 */

function smarty_modifier_inttoday($int)
{
    return Default_Helpers_Web::int2day($int);
}
