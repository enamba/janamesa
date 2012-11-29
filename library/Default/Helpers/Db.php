<?php
/**
 * Helper class for DB operations
 * 
 * @author Marek Hejduk <m.hejduk@pyszne.pl>
 * @since 19.07.2012
 */
class Default_Helpers_Db {
    /**
     * LIKE matching types, treated as sprintf patterns
     * @var string
     */
    const LIKE_TYPE_STARTS_WITH = '%s%%',
          LIKE_TYPE_ENDS_WITH = '%%%s',
          LIKE_TYPE_CONTAINS = '%%%s%%';

    /**
     * An escaper for SQL-LIKE based searching
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 19.07.2012
     *
     * @param string $value
     * @param string $type
     * @return string
     */
    public static function like($value, $type=self::LIKE_TYPE_CONTAINS) {
        return sprintf($type, str_replace(array('_', '%'), array('\\_', '\\%'), $value));
    }
}