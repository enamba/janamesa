<?php

/**
 *
 * Form for submitting an app feedback
 *
 * @author Andre Ponert <ponert@lieferando.de>
 * @since 09.07.2012
 */
class Yourdelivery_Form_Api_Feedback extends Default_Forms_Base {

    const COMMENT_MIN_LENGTH = 25;
    const COMMENT_MAX_LENGTH = 1000;

    /**
     * OVERRIDES Default_Forms_Base::init()
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function init() {
        parent::init();
        $this->initName(false, true);
        $this->initPrename(true, true);
        $this->initEmail(true, true);
        $this->initTel(false, true);

        $comment = new Zend_Form_Element_Text('comment');
        $comment->setAttrib('maxlength', self::COMMENT_MAX_LENGTH);
        $comment->addValidators(array(
                    new Zend_Validate_StringLength(array('min' => self::COMMENT_MIN_LENGTH, 'max' => self::COMMENT_MAX_LENGTH)),
                    new Zend_Validate_NotEmpty()
                ))
                ->addFilters(array(
                    new Zend_Filter_StripTags(),
                    new Zend_Filter_StringTrim(),
                ))
                ->setRequired(true);

        $this->addElement($comment);

        $this->getElement('comment')->getValidator('NotEmpty')
                        ->setMessage(__('Bitte gib eine Nachricht ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('comment')->getValidator('StringLength')
                ->setMessage(__('Die Nachricht ist zu kurz. (mind. %d Zeichen)', self::COMMENT_MIN_LENGTH), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('comment')->getValidator('StringLength')
                ->setMessage(__('Die Nachricht ist zu lang. (max. %d Zeichen)', self::COMMENT_MAX_LENGTH), Zend_Validate_StringLength::TOO_LONG);
    }

}