<?php
/**
 * @author Jens Naie <naie@lieferando.de>
 * @since 20.07.2012
 */
class Administration_RegionController extends Default_Controller_AdministrationBase {

    /**
     * Table with all regions and seo texts
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function indexAction(){

        // build select
        $db = Zend_Registry::get('dbAdapter');

        $select = $db
            ->select()
            ->from(array('r' => 'regions'), array(
                'ID'                   => 'r.id',
                __b('Land')            => 'r.region1',
                __b('Regierungsbezirk')=> 'r.region2',
                __b('Region')          => 'r.region3',
                __b('URL Restaurant')  => 'r.restUrl',
                __b('URL Catering')    => 'r.caterUrl',
                __b('URL Großhandel')  => 'r.greatUrl',
                __b('SEO Text')        => "IF(ISNULL(r.seoText) OR r.seoText='', 0, 1)",
            ))
            ->order('r.id');
        
        // build grid
        $grid = Default_Helper::getTableGrid('regions');
        $grid->setExport(array());
        $grid->setPagination(50);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('SEO Text'), array('class' => 'seo-pupup-link', 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{' . __b('SEO Text') . '}}'))));


        // add filters
        $filters = new Bvb_Grid_Filters();
        
        $filters->addFilter(__b('Land'))
                ->addFilter(__b('Regierungsbezirk'))
                ->addFilter(__b('Region'))
                ->addFilter(__b('SEO Text'), array('values' => array('' => __b('Alle'), '1' => __b('Ja'), '0' => __b('Nein'))));
        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }
}