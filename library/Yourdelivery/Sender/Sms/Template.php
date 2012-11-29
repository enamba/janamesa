<?php
/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 11.05.2012
 */
class Yourdelivery_Sender_Sms_Template{

    /**
     * Text
     * @var string
     */
    private $_text = null;

    /**
     * Template filename
     * @var string
     */
    private $_templateName = null;

    /**
     * The smarty object
     * @var Smarty
     */
    private $_smarty = null;

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     */
    public function __construct($templateName = null) {

        $config = Zend_Registry::get('configuration');

        if ($templateName !== null) {
            $this->setTemplateName($templateName);
        }

        $this->_smarty = new Smarty();
        $this->_smarty->template_dir = APPLICATION_PATH . "/templates/sms";
        $this->_smarty->compile_dir  = $config->smarty->compile_dir . '/sms/';
        $this->_smarty->config_dir   = $config->smarty->config_dir;
        $this->_smarty->cache_dir    = $config->smarty->cache_dir;
        $this->_smarty->caching      = false;
        
        $this->_smarty->assign('config', $config);
    }

    /**
     * Get template filename
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @return string
     */
    public function getTemplateName() {

        return $this->_templateName;
    }

    /**
     * Get compiled template text
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @return string
     */
    public function getText() {
        
        return $this->_text;
    }
    
    /**
     * Set template filename
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @param string $templateName
     * @return Yourdelivery_Sender_Sms_Template
     */
    public function setTemplateName($templateName) {

        $this->_templateName = $templateName;
        return $this;
    }

    /**
     * Assign a variable
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @param mixed string|array $spec
     * @param string $value
     * @return Yourdelivery_Sender_Sms_Template
     */
    public function assign($spec, $value = null) {

        if (is_array($spec)) {
            $this->_smarty->assign($spec);
        }
        else {
            $this->_smarty->assign($spec, $value);
        }
        return $this;
    }

    /**
     * Sends thie email
     * @throws Yourdelivery_Sender_Sms_Exception
     * @author Vincent Priem <priem@lieferando.de>
     * @since 11.05.2012
     * @param string $to
     * @return boolean
     */
    public function send($to) {

        $templateName = $this->getTemplateName();
        if ($templateName === null) {
            throw new Yourdelivery_Sender_Sms_Exception('No template defined');
        }

        $templateDir = $this->_smarty->template_dir;

        if (!is_file($templateDir . '/'. $templateName)) {
            $templateName = $templateName . ".txt";
        }
        if (!is_file($templateDir . '/'. $templateName)) {
            throw new Yourdelivery_Sender_Sms_Exception('Template (' . $templateName . ') not existant');
        }

        $this->_text = $this->_smarty->fetch($templateName);

        if (!IS_PRODUCTION) {
            $email = new Yourdelivery_Sender_Email();
            $email->addTo("samson@tiffy.de"); // will be override
            $email->setSubject('SMS TEST');
            $email->setBodyText($this->_text);
            return $email->send();
        }
        
        $sms = new Yourdelivery_Sender_Sms();
        return $sms->send($to, $this->_text);
    }

}