<?php
/**
 * Default Helpers Filter
 * @package helper
 * @author vpriem
 * @since 27.08.2010
 */
class Default_Helpers_Filter{

    /**
     * @var array
     */
    private static $_filters = array();

    /**
     * Apply filter
     * @author vpriem
     * @since 27.08.2010
     * @return string
     */
    public static function apply ($name, $subject, $type = null) {

        // retrieve filters from cache
        $filterId = $name . $type;
        if (array_key_exists($filterId, self::$_filters)) {
            $dbTableRowset = self::$_filters[$filterId];
        } else {
            $dbTable = new Yourdelivery_Model_DbTable_Filters();
            $dbTableRowset = self::$_filters[$filterId] = $dbTable->findByName($name, $type);
        }

        // apply filter
        foreach ($dbTableRowset as $dbTableRow) {
            $subject = $dbTableRow->filter($subject);
        }
        return $subject;

    }

}
