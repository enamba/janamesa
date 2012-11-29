<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Admin_Access_Rights.
 *
 * @copyright   Yourdelivery
 * @author	Jan Oliver Oelerich
*/

class Yourdelivery_Model_DbTable_Admin_Access_Rights extends Default_Model_DbTable_Base
{

    protected $_referenceMap    = array(

        'Groups' => array(
            'columns'           => 'groupId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Admin_Access_Groups',
            'refColumns'        => 'id'
        ),
        'Resources' => array(
            'columns'           => 'resourceId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Admin_Access_Resources',
            'refColumns'        => 'id'
        ),
    );
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'admin_access_rights';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

}