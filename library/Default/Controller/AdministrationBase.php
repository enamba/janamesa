<?php

/**
 * AdminBase Controller Class
 *
 * @package core
 * @subpackage controller
 * @author Jan Oliver Oelerich
 */
class Default_Controller_AdministrationBase extends Default_Controller_Auth {

    /**
     *
     * @var Zend_Auth_Adapter_DbTable
     */
    protected $adminAuth = null;

    /**
     * session namespace of admin area
     * @var Zend_Session_Namespace
     */
    protected $_session_admin = null;

    /**
     * @author mlaug
     */
    public function init() {
        parent::init();
        
        defined('SUPPORTER') ? define('SUPPORTER', '# SUPPORTER: ' . $this->session_admin->admin->getId()) : null;

        // Language overriding works only for controllers where _isLocaleFrozen returns false
        $this->view->currentLocale = $this->_overrideLocale();
        $this->view->locales = (!is_null($this->view->currentLocale))
            ? Default_Helpers_Locale::getList()
            : array();
    }

    /**
     * Getter
     * @author mlaug
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        switch ($name) {
            case 'session':
                if ($this->_session_admin == null) {
                    // start new Session or get previous from namespace
                    $this->_session_admin = new Zend_Session_Namespace('Administration');
                }
                return $this->_session_admin;

            case 'session_front':
                return parent::__get('session');

            default:
                return parent::__get($name);
        }
    }

    /**
     * Sets the auth of the admin
     * @return Zend_Auth_Adapter_DbTable
     */
    public function setAdminAuth() {
        $registry = Zend_Registry::get('dbAdapter');
        $auth = new Zend_Auth_Adapter_DbTable($registry);

        $auth->setTableName('admin_access_users')
                ->setIdentityColumn('email')
                ->setCredentialColumn('password')
                ->setCredentialTreatment('MD5(?)');

        return $auth;
    }

    /**
     * work some stuff before action is called
     */
    public function preDispatch() {
        parent::preDispatch();

        $request = $this->getRequest();

        if ($request->getActionName() != "required") {

            $this->adminAuth = $this->setAdminAuth();
            $this->view->assign("request", $this->getRequest());
            $this->view->assign("loggedIn", is_null($this->session->admin) ? false : true);

            // check if admin has access
            $request = $this->getRequest();

            if ($request->getActionName() != 'login' && is_null($this->session->admin)) {
                $this->_redirect('/administration/login');
            }

            $resource = $request->getControllerName() . '_' . $request->getActionName();

            $admin = $this->session->admin;
            if ($request->getActionName() != 'error' && $request->getActionName() != 'login' && $request->getActionName() != 'logout' && !($admin instanceof Yourdelivery_Model_Admin && $admin->hasAccessToResource($resource))) {
                $this->_redirect('/administration/error/');
            }
        }
    }

    public function postDispatch() {
        parent::postDispatch();
        $session = new Zend_Session_NameSpace('Default');
        $this->view->assign('notifications', $session->notification);
        $session->notification = null;

        $this->setAdminLinks();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 30.09.2011
     * @param string $action
     * @param string $modelType
     * @param int $id
     */
    protected function _trackUserMove($action, $modelType = false, $id = false) {
        $dbTable = new Yourdelivery_Model_DbTable_Admin_Access_Tracking();

        $insert = array(
            'action' => $action,
            'adminId' => $this->session_admin->admin->getId(),
        );
        if ($modelType && $id) {
            $insert['modelType'] = $modelType;
            $insert['modelId'] = $id;
        }

        if ($action) {
            $dbTable->insert($insert);
        }
    }

    /**
     * @author Alex Vait <vaitn@lieferando.de>
     * @since 16.01.2012
     * define links urls and names for admin backend
     */
    private function setAdminLinks() {
        $menuLinks = array(
            __b('Dashboard') => array(
                'url' => '/administration/dashboard',
                'icon_class' => 'icon00',
                'children' => array()
            ),
            __b('Statistiken') => array(
                'url' => '#',
                'icon_class' => 'icon01',
                'children' => array(
                    __b('Umsätze') => '/administration_stats_sales',
                    __b('Dienstleister') => '/administration_stats_services',
                    __b('Firmen') => '/administration_stats_companies',
                    __b('Benutzer') => '/administration_stats_customers/',
                    __b('Vertrieb') => '/administration_stats_salespersons',
                    __b('Verwendete Gutscheine') => '/administration_stats_discounts',
                    __b('Grafische Darstellung') => '/administration_stats_graphics',
                    __b('Marketing Report') => '/administration_stats_marketing',
                    __b('Access Log Auswertung') => '/administration_stats_accesslog',
                    __b('Support') => '/administration_stats_support'
                )
            ),
            __b('Bestellungen') => array(
                'url' => '#',
                'extra' => '<small id="yd-order-grid-search">X</small>',
                'icon_class' => 'icon02 icon_search',
                'children' => array(
                    __b('Suche') => '/administration_order/gridsearch',
                    __b('Übersicht für heute') => '/administration_order?type=view_grid_orders_this_day',
                    __b('Übersicht der letzten 7 Tage') => '/administration_order?type=view_grid_orders_last_seven',
                    __b('Übersicht aller Bestellungen') => '/administration_order?type=view_grid_orders',
                    __b('Support-Ticketsystem') => '/administration_ticketsystem/index',
                    __b('Prefix-Suche') => '/administration_order/search',
                    __b('Massen-Stornierung') => '/administration_order/massstorno'
                )
            ),
            __b('Benutzer') => array(
                'url' => '#',
                'icon_class' => 'icon03',
                'children' => array(
                    __b('Übersicht') => '/administration/users',
                    __b('Erstellen') => '/administration_user/create'
                )
            ),
            __b('Kontakte') => array(
                'url' => '#',
                'icon_class' => 'icon04',
                'children' => array(
                    __b('Übersicht') => '/administration/contacts',
                    __b('Erstellen') => '/administration_contact/create',
                    __b('Kontakte ohne Zuordnungen') => '/administration/contactsunused'
                )
            ),
            __b('Firmen') => array(
                'url' => '#',
                'icon_class' => 'icon05',
                'children' => array(
                    __b('Übersicht') => '/administration/companys',
                    __b('Erstellen') => '/administration_company/create'
                )
            ),
            __b('Dienstleister') => array(
                'url' => '#',
                'icon_class' => 'icon06',
                'children' => array(
                    __b('Übersicht') => '/administration/services',
                    __b('Dienstleister erstellen') => '/administration_service/create',
                    __b('Bewertungen') => '/administration_service_ratings',
                    __b('Restaurant Kategorien') => '/administration_service_category',
                    __b('Bilder Kategorien') => '/administration_service_category_picture/list',
                    __b('Bilder für Speisen') => '/administration_service_meals/picturesbatchupload',
                    __b('Kategorisierung der Speisen') => '/administration_service_meals/types',
                    __b('Restaurants ohne Bilderkategorien') => '/administration_service_category_picture/missingcatpics',
                    __b('Speisekarte kopieren') => '/administration_service/copy',
                    __b('Suchen und Ersetzen') => '/administration_service/searchreplace',
                    __b('Gesetzliche Feiertage') => '/administration_service_holidays',
                    __b('Postleitzahlen') => '/administration_city',
                    __b('Bezirke') => '/administration_district',
                    __b('Regionen') => '/administration_region',
                    __b('GPRS Drucker') => '/administration_service_printer',
                    __b('DL-Drucker Zuordnungen') => '/administration_service_printer/restaurants',
                )
            ),
            __b('Kurierdienste') => array(
                'url' => '#',
                'icon_class' => 'icon07',
                'children' => array(
                    __b('Übersicht') => '/administration/couriers',
                    __b('Erstellen') => '/administration_courier/create'
                )
            ),
            'Relatorios BE'=> array(
                'url' => '#',
                'icon_class' => 'icon01',
                'children' => array(
                    'PDS e Horarios' => '/administration/relatorioservicesall',
                    'Total Cardapio e Entrega' => '/administration/relatoriosomasplzemeals',
                    'Pedidos e Detalhes' => '/administration/relatorioordersfull',
                    'Cadastro 1 ano Delivery Gratis' => '/administration/relatoriofacebook1anodeliverygratisfull'
                )
            ),
            __b('Rechnungen') => array(
                'url' => '#',
                'icon_class' => 'icon08',
                'children' => array(
                    __b('Informationen') => '/administration_billing/information',
                    __b('Firmen') => '/administration_billing/company',
                    __b('Dienstleister') => '/administration_billing/service',
                    __b('Kurierdienste') => '/administration_billing/courier',
                    __b('Rechnungsposten Übersicht') => '/administration/billingassets',
                    __b('Rechnungsposten erstellen') => '/administration_billingasset/create',
                    __b('CSV Abgleich') => '/administration_billing/csvcompare'
                )
            ),
            __b('Rabattaktionen') => array(
                'url' => '#',
                'icon_class' => 'icon09',
                'children' => array(
                    __b('Übersicht') => '/administration/discounts',
                    __b('Erstellen') => '/administration_discount/create',
                    __b('Neukundengutschein prüfen') => '/administration_discount/checkregistrationdiscountcode',
                    __b('Rabattcode prüfen') => '/administration_discount/checkdiscountcode',
                    __b('Rabattaktion prüfen') => '/administration_discount/checkdiscount',
                    __b('Registrierungscode prüfen') => '/administration_discount/checkregistrationcode',
                    __b('Rabattcode deaktivieren') => '/administration_discount/deactivatediscountcode'
                )
            ),
            __b('Emails') => array(
                'url' => '#',
                'icon_class' => 'icon10',
                'children' => array(
                    __b('Übersicht') => '/administration/emails',
                    __b('Mailingaktionen') => '/administration_mailing'
                )
            ),
            __b('SEO') => array(
                'url' => '#',
                'icon_class' => 'icon11',
                'children' => array(
                    __b('Auswertungen') => '/administration_seo/trackingsummary',
                    __b('Satelliten') => '/administration_satellite/',
                    __b('Interne Verlinkung') => '/administration_seo_links/',
                    __b('Sitemaps') => '/administration_seo_sitemaps/',
                    __b('Backlinks') => '/administration_seo_backlinks/',
                    __b('SEM') => '/administration_seo_sem/'
                )
            ),
            __b('Zugangsrechte') => array(
                'url' => '#',
                'icon_class' => 'icon12',
                'children' => array(
                    __b('Übersicht') => '/administration/admins',
                    __b('Gruppen') => '/administration/admingroups'
                )
            ),
            __b('Support') => array(
                'url' => '#',
                'icon_class' => 'icon13',
                'children' => array(
                    __b('Übersicht') => '/administration/support',
                    __b('Blacklist') => '/administration_blacklist/keywords',
                    __b('Definitionen') => '/administration/definitions'
                )
            ),
            __b('Vertrieb') => array(
                'url' => '#',
                'icon_class' => 'icon14',
                'children' => array(
                    __b('Übersicht') => '/administration/salespersons'
                )
            ),
            __b('Warenwirtschaft') => array(
                'url' => '#',
                'icon_class' => 'icon15',
                'children' => array(
                    __b('Neuer Posten') => '/administration_inventory/create',
                    __b('Übersicht Produkte') => '/administration_inventory/overview',
                    __b('Übersicht Flyer') => '/administration_inventory/overviewflyer',
                    __b('Übersicht Druckkosten') => '/administration_inventory/overviewprinter',
                    __b('Übersicht Website') => '/administration_inventory/overviewwebsite',
                    __b('Übersicht SMS Terminal') => '/administration_inventory/overviewterminal'
                )
            ),
            __b('Upselling') => array(
                'url' => '#',
                'icon_class' => 'icon16',
                'children' => array(
                    __b('Produkte') => '/administration_upselling_goods'
                )
            ),
            __b('Einstellungen') => array(
                'url' => '#',
                'icon_class' => 'icon17',
                'children' => array(
                    __b('Registriert/geändert Spalten') => '/administration/settings'
                )
            )
        );

        $admin = $this->session_admin->admin;
        if (!is_null($admin)) {
            $accessParentLinks = array(
                'administration_stats_index' => 'Statistiken',
                'administration_orders_gridsearch' => 'Bestellungen',
                'administration_users' => 'Benutzer',
                'administration_contacts' => 'Kontakte',
                'administration_companys' => 'Firmen',
                'administration_services' => 'Dienstleister',
                'administration_couriers' => 'Kurierdienste',
                'administration_billing_company' => 'Rechnungen',
                'administration_discounts' => 'Rabattaktionen',
                'administration_emails' => 'Emails',
                'administration_satellite' => 'SEO',
                'administration_admins' => 'Zugangsrechte',
                'administration_support' => 'Support',
                'administration_salespersons' => 'Vertrieb',
                'administration_inventory' => 'Warenwirtschaft',
                'administration_upselling' => 'Upselling',
                'administration_settings' => 'Einstellungen'
            );

            foreach ($accessParentLinks as $resource => $parentLink) {
                if (!$admin->hasAccessToResource($resource)) {
                    unset($menuLinks[$parentLink]);
                }
            }

            $this->view->assign('requestUri', $_SERVER["REQUEST_URI"]);
            foreach ($menuLinks as $parent => $container) {
                if (in_array($_SERVER["REQUEST_URI"], array_values($container['children']))) {
                    $this->view->assign('parentMenu', $parent);
                }
            }
            $this->view->assign('menuLinks', $menuLinks);
        }
    }

    /**
     * Locale cookie name for backend
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return string
     */
    protected function _getLocaleCookieName() {
        return 'yd-be-locale';
    }

    /**
     * Allows language changing in backend
     * Warning! For some administration controllers it should be overriden!
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return boolean
     */
    protected function _isLocaleFrozen() {
        return false;
    }
}
