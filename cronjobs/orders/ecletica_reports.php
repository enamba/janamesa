<?php
/**
 * process reports from each restaurant set to eclectica
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 14.03.2012
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));

$ecl = new Janamesa_Api_Ecletica();
$ecl->processReports();