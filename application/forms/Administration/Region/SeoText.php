<?php
/**
 * @author Jens Naie <naieqlieferando.de>
 * @since 20.07.2012
 */
class Yourdelivery_Form_Administration_Region_SeoText extends Default_Forms_Base {
    /**
     * Jens Naie <naieqlieferando.de>
     * @since 20.07.2012
     * @return void
     */
    public function init() {
        $this->addElement('text', 'seoHeadline', array(
            'required'   => false
        ));
        $this->addElement('text', 'seoText', array(
            'required'   => false
        ));
        $this->addElement('checkbox', 'important', array(
            'required'   => false
        ));
    }
}