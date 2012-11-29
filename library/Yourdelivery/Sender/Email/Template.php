<?php
/**
 * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
 */
class Yourdelivery_Sender_Email_Template extends Yourdelivery_Sender_Email_Abstract{

    /**
     * Template name
     * @var string
     */
    private $_templateName = null;

    /**
     * The smarty object
     * @var Smarty
     */
    private $_smarty = null;

    /**
     * Constructor
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @param string $templateName
     */
    public function __construct($templateName = null) {

        parent::__construct();

        if ($templateName !== null) {
            $this->setTemplateName($templateName);
        }

        $this->_smarty = new Smarty();
        $this->_smarty->template_dir = array(
            $this->_config->sender->email->template_dir . "/" . $this->_config->domain->base,
            $this->_config->sender->email->template_dir . "/default",
        );
        $this->_smarty->compile_dir = $this->_config->smarty->compile_dir . "/email";
        $this->_smarty->config_dir = $this->_config->smarty->config_dir;
        $this->_smarty->cache_dir = $this->_config->smarty->cache_dir;
        $this->_smarty->caching = false;
        
        $this->_smarty->assign('root', $this->_config->hostname . '/');
        $this->_smarty->assign('taxes', $this->_config->tax->types->toArray());
        $this->_smarty->assign('config', $this->_config);
    }

    /**
     * Get template name
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @return string
     */
    public function getTemplateName() {

        return $this->_templateName;
    }

    /**
     * Set template name
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @param string $name
     * @return Yourdelivery_Sender_Email_Template
     */
    public function setTemplateName($name) {

        if (strpos($name, ".") === false) {
            $name .= ".htm";
        }
        
        $this->_templateName = $name;
        return $this;
    }

    /**
     * Assign a variable
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @param mixed string|array $spec
     * @param string $value
     * @return Yourdelivery_Sender_Email_Template
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
     * Sends the email
     * @throws Yourdelivery_Sender_Email_Template_Exception
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 25.07.2012
     * @return boolean
     */
    public function send() {

        $templateName = $this->getTemplateName();
        
        try {
            $body = $this->_smarty->fetch($templateName);
        } catch (SmartyException $e) {
            throw new Yourdelivery_Sender_Email_Template_Exception($e->getMessage());
        }

        $ext = strtolower(pathinfo($templateName, PATHINFO_EXTENSION));
        switch ($ext) {
             case 'txt':
                $this->setBodyText($body);               
                break;           
            
            case 'html': 
            case 'htm': 
            default:    
                $this->setBodyHtml($body);                
                break;
        }
        
        return parent::send();
    }

}