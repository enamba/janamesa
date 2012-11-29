<?php
/**
 * @author Jens Naie <naie@lieferando.de>
 * @since 20.07.2012
 */
class Administration_DistrictController extends Default_Controller_AdministrationBase {

    /**
     * Table with all districts and seo texts
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function indexAction(){

        // build select
        $db = Zend_Registry::get('dbAdapter');

        $select = $db
            ->select()
            ->from(array('d' => 'districts'), array(
                'ID'                   => 'd.id',
                __b('Region')          => 'd.region3',
                __b('Bezirk')          => 'd.city',
                __b('URL Restaurant')  => 'd.restUrl',
                __b('URL Catering')    => 'd.caterUrl',
                __b('URL Großhandel')  => 'd.greatUrl',
                __b('SEO Text')        => "IF(ISNULL(d.seoText) OR d.seoText='', 0, 1)",
            ))
            ->order('d.id');
        
        // build grid
        $grid = Default_Helper::getTableGrid('districts');
        $grid->setExport(array());
        $grid->setPagination(50);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('SEO Text'), array('class' => 'seo-pupup-link', 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{' . __b('SEO Text') . '}}'))));


        // add filters
        $filters = new Bvb_Grid_Filters();
        
        $filters->addFilter(__b('Bezirk'))
                ->addFilter(__b('Region'))
                ->addFilter(__b('SEO Text'), array('values' => array('' => __b('Alle'), '1' => __b('Ja'), '0' => __b('Nein'))));
        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }
}