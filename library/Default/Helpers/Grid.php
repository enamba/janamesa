<?php

/**
 * @package helper
 * @author mlaug
 */
class Default_Helpers_Grid {

    /**
     * generate an order grid
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.06.2012
     * @param string $type
     * @param integer $discount
     * @return Bvb_Grid
     */
    public static function generateOrderGrid($type, array $filters = null) {

        // build query
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $desc = array();

        try {
            $desc = array_keys($db->describeTable($type));
        } catch (Exception $e) {
            return null;
        }

        $select = $db->select()->from(array('o' => 'orders'), array(
                    'ID' => 'id',
                    'Nummer' => 'nr',
                    'Eingang' => new Zend_Db_Expr("DATE_FORMAT(o.time, '%d.%m.%Y %H:%i')"),
                    'Lieferzeit' => new Zend_Db_Expr("DATE_FORMAT(o.deliverTime, '%d.%m.%Y %H:%i')"),
                    'Status' => 'o.state',
                    'Payment' => 'o.payment',
                    'Detalhe Pagamento' => 'o.paymentAddition',
                    'Typ' => 'o.mode',
                    'Kundentyp' => 'o.kind',
                    'Preis' => new Zend_Db_Expr("o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount"),
                    'Gutschein' => new Zend_Db_Expr("IF(o.rabattCodeId > 0, 1, 0)"),
                    'o.rabattCodeId',
                    'o.restaurantId',
                    'o.sendBy',
                    'Dienstleister' => new Zend_Db_Expr("CONCAT(r.name, ' (', r.customerNr , ')', ' ', '#', r.id)"),
                    'r.franchiseTypeId',
                    'r.notifyPayed',
                    'TelNr' => 'r.tel',
                    'o.customerId',
                    'o.ipAddr',
                    'Name' => new Zend_Db_Expr("CONCAT(oc.prename, ' ', oc.name, IF(o.customerId is not NULL, CONCAT(' #', o.customerId), ''))"),
                    'Email' => 'oc.email',
                    'Adresse' => new Zend_Db_Expr("CONCAT(l.street, ' ', l.hausnr, ' ', CONVERT(l.plz USING utf8))"),
                    'Stadt' => new Zend_Db_Expr("if (c.parentCityId > 0, CONCAT(cp.city, ' (', c.city, ')'), c.city)"),
                    'Firma' => new Zend_Db_Expr("CONCAT(l.companyName, ' ', IF(o.companyId is not NULL, CONCAT(' #', o.companyId), ''))"),
                    'l.tel',
                    'o.uuid',
                    'o.companyId',
                ))
                ->joinLeft(array('r' => 'restaurants'), 'o.restaurantId = r.id', array())
                ->join(array('oc' => 'orders_customer'), 'o.id = oc.orderId', array())
                ->join(array('l' => 'orders_location'), 'o.id = l.orderId', array())
                ->joinLeft(array('c' => 'city'), 'c.id = l.cityId', array())
                ->joinLeft(array('cp' => 'city'), 'cp.id = c.parentCityId', array())
                ->group('o.id')
                ->order('o.id DESC');


        if (strcmp($type, 'view_grid_orders_this_day') == 0) {
            $select->where('o.time > DATE(NOW())');
        } else if (strcmp($type, 'view_grid_orders_last_seven') == 0) {
            $select->where('o.time > DATE_SUB(NOW(), INTERVAL 7 DAY)');
        }

        //list only certian discount codes
        foreach ($filters as $column => $value) {
            if (strlen($value) == 0) {
                continue;
            }
            switch ($column) {
                default:
                    if (in_array($column, $desc)) {
                        $select->where(sprintf('%s=?', $column), $value);
                    }
                    break;
                
                case 'customername':
                    $select->where('(oc.id=?', $value)
                        ->orWhere('oc.prename LIKE ?', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_CONTAINS)
                        ->orWhere('oc.prename LIKE ?', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_ENDS_WITH)
                        ->orWhere('oc.prename LIKE ?', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_STARTS_WITH)
                        ->orWhere('oc.name LIKE ?', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_CONTAINS)
                        ->orWhere('oc.name LIKE ?', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_ENDS_WITH)
                        ->orWhere('oc.name LIKE ?)', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_STARTS_WITH);
                    break;
                    
                case 'service':
                    $select->where('(r.customerNr=?', $value)
                        ->orWhere('r.id=?', $value)
                        ->orWhere('r.name LIKE ?', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_CONTAINS)
                        ->orWhere('r.name LIKE ?', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_ENDS_WITH)
                        ->orWhere('r.name LIKE ?)', Default_Helpers_Db::like($value), Default_Helpers_Db::LIKE_TYPE_STARTS_WITH);
                    break;

                case 'customertel':
                    $select->where('l.tel=?', $value);
                    break;

                case 'resttel':
                    $select->where('r.tel=?', $value);
                    break;

                case 'rabattId':
                    $select->join(array('rc' => "rabatt_codes"), "o.rabattCodeId = rc.id", array())
                        ->where("rc.rabattId = ?", $value);
                    break;

                case 'payerId':
                    $paypalTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();
                    $orders = $paypalTable->select()->where('payerId=? and orderId>0', $value)->query()->fetchAll();
                    $orders = array_map(function($elem) {
                                return $elem['orderId'];
                            }, $orders);

                    if ($orders === null) {
                        $orders = array(0);
                    }
                    $select->where('o.payment="paypal" and o.id in (?)', $orders);
                    break;
            }
        }

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        //hide some columns just for callback reference usage
        $grid->updateColumn('restaurantId', array('hidden' => 1));
        $grid->updateColumn('rabattCodeId', array('hidden' => 1));
        $grid->updateColumn('customerId', array('hidden' => 1));
        $grid->updateColumn('uuid', array('hidden' => 1));
        $grid->updateColumn('notifyPayed', array('hidden' => 1));
        $grid->updateColumn('sendBy', array('hidden' => 1));
        $grid->updateColumn('tel', array('hidden' => 1));
        $grid->updateColumn('companyId', array('hidden' => 1));
        $grid->updateColumn('payerId', array('hidden' => 1));

        //update all columns accordingly
        $grid->updateColumn('ID', array('searchType' => 'equal', 'decorator' => '#<span class="yd-parepare-to-copy">{{ID}}</span>'));
        $grid->updateColumn('franchiseTypeId', array('searchType' => 'equal', 'title' => __b('Franchise'), 'callback' => array('function' => 'getFranchise', 'params' => array('{{franchiseTypeId}}'))));
        $grid->updateColumn('Email', array('title' => __b('Email'), 'callback' => array('function' => 'emailinfo', 'params' => array('{{Email}}', '{{ID}}'))));
        $grid->updateColumn('Preis', array('title' => __b('Preis'), 'callback' => array('function' => 'intToPrice', 'params' => array('{{Preis}}'))));
        $grid->updateColumn('Gutschein', array('title' => __b('Gutschein'), 'callback' => array('function' => 'discountInfo', 'params' => array('{{rabattCodeId}}', '{{ID}}', '{{Gutschein}}'))));
        $grid->updateColumn('Status', array('title' => __b('Status'), 'searchType' => 'equal', 'class' => 'status', 'callback' => array('function' => 'intToStatusOrders', 'params' => array('{{Status}}', '{{Typ}}'))));
        $grid->updateColumn('Payment', array('title' => __b('Payment'), 'callback' => array('function' => 'Default_Helpers_Grid_Order::payment', 'params' => array('{{Payment}}', '{{ID}}'))));
        $grid->updateColumn('Typ', array('title' => __b('Typ'), 'callback' => array('function' => 'modeToReadable', 'params' => array('{{Typ}}'))));
        $grid->updateColumn('Kundentyp', array('title' => __b('Kundentyp'), 'callback' => array('function' => 'kindToReadable', 'params' => array('{{Kundentyp}}'))));
        $grid->updateColumn('Dienstleister', array('title' => __b('Dienstleister'), 'nativeFilter' => false, 'callback' => array('function' => 'getFranchiseImage', 'params' => array('{{restaurantId}}', '{{franchiseTypeId}}', '{{Dienstleister}}'))));
        $grid->updateColumn('Name', array('title' => __b('Name'), 'nativeFilter' => false, 'callback' => array('function' => 'getRegisteredCustomerLink', 'params' => array('{{Name}}', '{{Email}}', '{{customerId}}', '{{uuid}}', '{{ID}}'))));
        $grid->updateColumn('TelNr', array('title' => __b('TelNr'), 'callback' => array('function' => 'telinfo', 'params' => array('{{tel}}', '{{TelNr}}', '{{ID}}'))));
        $grid->updateColumn('Nummer', array('title' => __b('Nummer')));
        $grid->updateColumn('Eingang', array('title' => __b('Eingang')));
        $grid->updateColumn('Lieferzeit', array('title' => __b('Lieferzeit')));
        $grid->updateColumn('Adresse', array('title' => __b('Adresse'), 'callback' => array('function' => 'addressinfo', 'params' => array('{{Adresse}}', '{{Stadt}}', '{{ID}}'))));
        $grid->updateColumn('Stadt', array('title' => __b('Stadt')));
        $grid->updateColumn('Firma', array('title' => __b('Firma'), 'callback' => array('function' => 'companyinfo', 'params' => array('{{Firma}}', '{{companyId}}', '{{ID}}'))));
        $grid->updateColumn('ipAddr', array('title' => __b('Ip Addr/UUID'), 'callback' => array('function' => 'ipinfo', 'params' => array('{{ipAddr}}', '{{uuid}}', '{{ID}}'))));
        $grid->updateColumn('Detalhe Pagamento', array('title' => 'Detalhe Pagamento', 'callback' => array('function' => 'Default_Helpers_Grid_Order::payment', 'params' => array('{{Detalhe Pagamento}}', '{{ID}}'))));

        // translate stati
        $statis = array(
            '-8' => __b('Pending payment'),
            '-7' => __b('Storno Discount'),
            '-6' => __b('Blacklist'),
            '-5' => __b('Prepayment'),
            '-4' => __b('Not affirmed on billing'),
            '-3' => __b('Fake'),
            '-2' => __b('Storno'),
            '-1' => __b('Error'),
            '-15' => __b('Fax error'),
            '-22' => __b('Refected'),
            '0' => __b('Not affirmed'),
            '1' => __b('Affirmed'),
            '2' => __b('Delivered'),
            '' => __b('Alle')
        );

        // translate payment
        $payments = array(
            'debit' => __b('Lastschrift'),
            'credit' => __b('Kreditkarte'),
            'paypal' => __b('PayPal'),
            'bar' => __b('Barzahlung'),
            'bill' => __b('Rechnung'),
            'ebanking' => __b('Überweisung'),
            '' => __b('Alle')
        );

        // translate payment addition
        $paymentAddition = array(
            'ec' => "Cartão de Débito (Aparelho em domicílio)",
            'creditCardAtHome' => "Cartão de Crédito (Aparelho em domicílio)",
            'vr' => __("Vale Refeição"),
            'cheque' => __("Cheque"),
            'ticketRestaurant' => __("Ticket Restaurante"),
            '' => __b('Alle')
        );

        // translate mode
        $modes = array(
            'rest' => __b('Restaurant'),
            'cater' => __b('Catering'),
            'fruit' => __b('Obst'),
            'great' => __b('Großhandel'),
            'canteen' => __b('Kantine'),
            '' => __b('Alle')
        );

        // translate customer type
        $kinds = array(
            'priv' => __b('Privat'),
            'comp' => __b('Firma'),
            '' => __b('Alle')
        );

        $ok = array(
            '0' => __b('Nein'),
            '1' => __b('Ja'),
            '' => __b('Alle')
        );

        $franchises = Yourdelivery_Model_Servicetype_Franchise::all();
        $franchiseType = array('' => __b('Alle'));
        foreach ($franchises as $franchise) {
            $franchiseType[$franchise['id']] = $franchise['name'];
        }

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('ID')
                ->addFilter('Nummer')
                ->addFilter('Eingang')
                ->addFilter('Lieferzeit')
                ->addFilter('Status', array('values' => $statis))
                ->addFilter('Payment', array('values' => $payments))
                ->addFilter('Detalhe Pagamento', array('values' => $paymentAddition))
                ->addFilter('Typ', array('values' => $modes))
                ->addFilter('Kundentyp', array('values' => $kinds))
                ->addFilter('Gutschein', array('values' => $ok))
                ->addFilter('Dienstleister')
                ->addFilter('franchiseTypeId', array('values' => $franchiseType))
                ->addFilter('ipAddr')
                ->addFilter('Name')
                ->addFilter('Email')
                ->addFilter('Adresse')
                ->addFilter('Stadt')
                ->addFilter('Firma');
        $grid->addFilters($filters);

        // option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('left')
                ->name('')
                ->callback(array('function' => 'optionsForOrders', 'params' => array('{{ID}}')));

        // option row
        $phoneoption = new Bvb_Grid_Extra_Column();
        $phoneoption
                ->position('left')
                ->name('')
                ->callback(array('function' => 'notifyPayedForOrders', 'params' => array('{{ID}}', '{{notifyPayed}}', '{{Payment}}', '{{franchiseTypeId}}', '{{sendBy}}')));

        $grid->addExtraColumns($phoneoption, $option);

        return $grid;
    }

    /**
     * get translated attributes of meal
     * @param string $attrEngl
     *
     * @return string
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.07.2012
     */
    public static function getAtrributeReadable($attrEngl) {
        $allAttributes = Yourdelivery_Model_Meals::getAllAttributes();

        $attrAray = explode(",", $attrEngl);

        $result = array();
        foreach ($attrAray as $a) {
            $result[] = $allAttributes[trim($a)];
        }

        return implode(", ", $result);
    }

}
