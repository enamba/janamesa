<?php

/**
 * Autocomplete model
 * @author vpriem
 */
class Yourdelivery_Model_Autocomplete {

    /**
     * get plz
     * @author priem
     * @param int $service
     * @param int beginning of plz - for many plzs
     * @modified jnaie 20.06.2012
     * @return array
     */
    public static function plz($service = 0, $plzBeginning = null) {

        // get read only adapter db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $table = new Yourdelivery_Model_DbTable_City();
        $select =
            $table->getAdapter()->select()->from(array('c' => 'city'), array(
                'id' => 'c.id', 'plz' => 'c.plz', 'city' => 'c.city',
                'value' => 'IF(c.parentCityId = 0, CONCAT(c.plz, \' \', c.city), CONCAT(c.plz, \' \', parent.city, \' (\', c.city, \')\'))',
                'restUrl' => 'c.restUrl'))->joinLeft(array('parent' => 'city'), 'c.parentCityId > 0 AND c.parentCityId = parent.id', array());

        //get only those plzs within the restaurant range
        if ($service > 0) {
            $select->join(array('rp' => 'restaurant_plz'), 'c.id = rp.cityId', array())->where('rp.restaurantId = ?', $service);
        }
        // get only parts of the plz table
        if ($plzBeginning !== null) {
            $select->where('c.plz like ?', $plzBeginning . '%');
        }

        return $select->query()->fetchAll();
    }

    /**
     * get all street of a city
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     * @param string $city
     * @param integer $service
     * @return array
     */
    public static function street($city, $service = 0) {
        $table = new Yourdelivery_Model_DbTable_City_Verbose();
        $select =
            $table->getAdapter()->select()->from(array('cv' => 'city_verbose'), array(
                'value' => 'cv.street', 'cityId' => 'cv.cityId',
                'vId' => 'cv.id',
                'number' => new Zend_Db_Expr('group_concat(concat(cv.number,":",cv.cityId) SEPARATOR "#")'),
                'count' => new Zend_Db_Expr('count(street)')))->join(array(
                'c' => 'city'), 'c.id=cv.cityId', array('plz' => 'c.plz'))->where('cv.city=?', $city)->group('street');

        //get only those streets within the restaurant range
        if ($service > 0) {
            $select->join(array('rp' => 'restaurant_plz'), 'c.id=rp.cityId', array())->where('rp.restaurantId=?', $service);
        }

        return $select->query()->fetchAll();
    }

    /**
     * get all cities
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.04.2012
     * @param string $city
     * @param integer $service
     * @return array
     */
    public static function city($service = 0) {
        $table = new Yourdelivery_Model_DbTable_City();

        //those cities which have verbose information in the city_verbose table
        $selectWithVerboseInformation =
            $table->getAdapter()->select()->from(array('c' => 'city'), array(
                'cityId' => new Zend_Db_Expr('0'), //no cityId here, needs to be redefined with verbose data (street, hausnr)
                'plz' => 'c.plz', 'value' => 'c.city',
                'count' => new Zend_Db_Expr('(select count(*) from city_verbose cv where cv.city=c.city)')
            ))->where('exists(select * from city_verbose cv where cv.city=c.city)')->group('c.city')->order('c.city');

        //get only those streets within the restaurant range
        if ($service > 0) {
            $selectWithVerboseInformation->where(
                'exists(select * from restaurant_plz rp where rp.restaurantId=? and c.id=rp.cityId)', $service
            );
        }

        //those cities which have NO verbose information in the city_verbose table
        $selectWithoutVerboseInformation =
            $table->getAdapter()->select()->from(array('c' => 'city'), array(
                'cityId' => 'c.id', 'plz' => 'c.plz',
                'value' => new Zend_Db_Expr('CONCAT(c.city," (",c.plz, ")")'),
                'count' => new Zend_Db_Expr('0')
            ))->where('not exists(select * from city_verbose cv where cv.city=c.city)')->order('value');

        //get only those streets within the restaurant range
        if ($service > 0) {
            $selectWithoutVerboseInformation->where(
                'exists(select * from restaurant_plz rp where rp.restaurantId=? and c.id=rp.cityId)', $service
            );
        }

        // A workaround of Zend_DB problem with UNION + MySQL:
        // (SELECT * FROM a ORDER BY x) UNION (SELECT * FROM b ORDER BY y) -> works
        // SELECT * FROM a ORDER BY x UNION SELECT * FROM b ORDER BY y -> causes MySQL error
        // @see http://framework.zend.com/issues/browse/ZF-11392
        $union = $table->getAdapter()->select()->union(array(
            "($selectWithVerboseInformation)",
            "($selectWithoutVerboseInformation)"
        ));

        return $union->query()->fetchAll();
    }

    /**
     * crm object, depending on type
     * @author alex
     * @since 21.06.2011
     * @return array
     */
    public static function crm($type) {

        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        //get all crm objects
        switch ($type) {
            case 'company':
                $crmObjects =
                    $db->fetchAll("SELECT `id`, `name`, `customerNr`, CONCAT(`id`, ' ', `name`, ' (nr.' , `customerNr`, ')') `value` FROM `companys` where deleted=0");
                break;
            case 'user':
                $crmObjects =
                    $db->fetchAll("SELECT `id`, CONCAT(`prename`, ' ', `name`) as `name`, CONCAT(`id`, ' ', `prename`, ' ', `name`) `value` FROM `customers` where deleted=0");
                break;
            case 'staff':
                $crmObjects =
                    $db->fetchAll("SELECT id, name, email, CONCAT(`id`, ' ', `name`, ' (' , `email`, ')') `value` FROM admin_access_users");
                break;
            case 'service':
            default:
                $crmObjects =
                    $db->fetchAll("SELECT `id`, `name`, `customerNr`, CONCAT(`id`, ' ', `name`, ' (nr.' , `customerNr`, ')') `value` FROM `restaurants` where deleted=0");
                break;
        }

        return $crmObjects;
    }

    /**
     * Plz limited
     * @author vpriem
     * @since 27.01.2011
     * @param string $plz
     * @param int $limit
     * @return array
     */
    public static function plzLimited($plz, $limit) {

        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->fetchAll("SELECT `id`, `plz`, `city`, CONCAT(`plz`, ' ', `city`) `value`, 0 `parentId`, `restUrl`, `caterUrl`, `greatUrl`
                FROM `city`
                WHERE `parentCityId` = 0 AND `plz` LIKE ?
                    UNION
                SELECT `child`.`id`, `child`.`plz`, `child`.`city`, CONCAT(`child`.`plz`, ' ', `parent`.`city`, ' (', `child`.`city`, ')') `value`, `child`.`parentCityId` `parentId`, `child`.`restUrl`, `child`.`caterUrl`, `child`.`greatUrl`
                         FROM `city` child
                         INNER JOIN `city` parent ON `child`.`parentCityId` = parent.id
                         WHERE child.parentCityId > 0 AND `child`.`plz` LIKE ?
                order by id  limit " . ((integer) $limit), array($plz . "%",
            $plz . "%"));
    }

    /**
     * Employees email
     * @author vpriem
     * @param int $companyId
     * @param string $email
     * @return array
     */
    public static function employees($companyId, $email) {

        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->fetchAll("SELECT c.email, CONCAT(c.email, ' | ', c.name, ' ', c.prename) `value`
            FROM `customers` c
            INNER JOIN `customer_company` cc on c.id = cc.customerId
                AND cc.companyId = ?
                AND c.deleted = 0
            WHERE c.email LIKE ?
            ORDER BY c.email
            LIMIT 5", array($companyId, $email . "%"));
    }

    /**
     * Employees email
     * @author mlaug
     * @since 14.08.2010
     * @param int $companyId
     * @param string $email
     * @return array
     */
    public static function budgetgroup($budgetId, $email) {

        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->fetchAll("SELECT c.email, CONCAT(c.email, ' | ', c.name, ' ', c.prename) `value`
            FROM `customers` c
            INNER JOIN `customer_company` cc on c.id = cc.customerId
                AND cc.budgetId = ?
                AND c.deleted = 0
            WHERE c.email LIKE ?
            ORDER BY c.email
            LIMIT 5", array($budgetId, $email . "%"));
    }

    /**
     * Projectnumbers
     * @author vpriem
     * @param int $companyId
     * @param string $nr
     * @return array
     */
    public static function projectnumbers($companyId, $nr) {

        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->fetchAll("SELECT `number`, CONCAT(`number`, ' ', IFNULL(`comment`, '')) `value`
            FROM `projectnumbers`
            WHERE `deleted` = 0
                AND `companyId` = ?
                AND `number` LIKE ?
            ORDER BY `number` ASC
            LIMIT 7", array($companyId, $nr . "%"));
    }

    /**
     * Distinct meals names
     * @author alex
     * @param string $name
     * @return array
     */
    public static function meals($meal) {

        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->fetchAll("SELECT `name` `value`
            FROM `meals`
            WHERE status = 1 AND `name` LIKE ? group by `name` order by count(`name`) desc
            LIMIT 5", $meal . "%");
    }

    /**
     * Distinct meals descriptions
     * @author alex
     * @param string $name
     * @return array
     */
    public static function mealdescriptions($description) {

        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->fetchAll("SELECT `description` `value`
            FROM `meals`
            WHERE status = 1 AND `description` LIKE ? group by `description` order by count(`description`) desc
            LIMIT 5", $description . "%");
    }
    
    public static function getPlzFromServiceAndcity($cityId, $serviceId){
        // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');

        return $db->fetchAll("select plz FROM restaurant_plz WHERE restaurantId = ? AND cityId = ?", array($serviceId, $cityId));
        ;
    }

}
