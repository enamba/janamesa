<?php

/**
 * escape all slahses from output
 * @author mlaug
 * @since 27.08.2010
 * @param string $string
 * @return string
 */
function yourdelivery_output_filter($string) {
    
    return stripcslashes($string);
}

/**
 * Use Smarty as a view replacement for Zend_View
 * @author mlaug
 */
class Default_View_Smarty extends Zend_View {

    /**
     * @var Zend_Config
     */
    protected $_config = null;
    
    /**
     * Smarty object
     * @var Smarty
     */
    protected $_smarty = null;
    
    /**
     * layout directory
     * @var String
     */
    protected $_layout = "default";
    
    /**
     * list of ids which will be disabled using javascript
     * @var array
     */
    protected $_disabledElements = array();
    
    /**
     * Is caching allowed
     * @var boolean
     */
    protected $_smartyCache = false;
    protected $_cache = false;
    
    /**
     * name of current template, may be overwritten
     * @var string
     */
    protected $_name = null;
    
    /**
     * name of current template dir, may be overwritten
     * @var string
     */
    protected $_dir = null;
    
    /**
     * @var Zend_Log
     */
    protected $_logger = null;
    
    /**
     * Constructor
     * @author mlaug
     * @param Zend_Config $config
     */
    public function __construct($config) {

        $this->_config = $config;

        // smarty
        $this->_smarty = new Smarty();
        
        // smarty settings
        $this->_smartyCache = $config->smarty->caching;
        $this->_smarty->caching = $config->smarty->caching;
        $this->_smarty->cache_lifetime = $config->smarty->cache_lifetime;
        // allow domain overriding only for default layout
        $this->_smarty->template_dir = array(
            $config->smarty->template_dir . "/" . $config->domain->base,
            $config->smarty->template_dir . "/" . $this->_layout,
        );
        $this->_smarty->compile_dir = $config->smarty->compile_dir . "/" . $this->_layout;
        $this->_smarty->config_dir = $config->smarty->config_dir;
        $this->_smarty->cache_dir = $config->smarty->cache_dir;

        // append some filters
        $this->_smarty->registerFilter('output', 'yourdelivery_output_filter');

        // do not check compiled templates in productive mode
        $this->_smarty->compile_check = true;
        if (IS_PRODUCTION) {
            $this->_smarty->compile_check = false;
        }

        $this->_logger = Zend_Registry::get('logger');

        $this->assign('this', $this);
        
        // load the default zend view as well
        parent::__construct($config);
    }

    /**
     * append any javascript to header
     * @author mlaug
     * @param string $script
     */
    public function addHeaderScript($script) {
        $this->assign('headerScript', $script);
    }

    /**
     * set current directory to search for templates
     * @param string $templateDir
     * @return boolean
     */
    public function setTemplateDir($templateDir) {
        
        if (!is_dir($templateDir)) {
            $this->_logger->crit(sprintf('SMARTY: provided path %s is not a dir', $templateDir));
            return false;
        }
        
        $this->_smarty->template_dir = $templateDir;
        return true;
    }

    /**
     * get current directory to search for templates
     * @return array|string
     */
    public function getTemplateDir() {
        
        return $this->_smarty->template_dir;
    }
    
    /**
     * Return the template engine object
     * @author mlaug
     * @return Smarty
     */
    public function getEngine() {
        return $this->_smarty;
    }

    /**
     * get our current layout
     * default is "default"
     * @author mlaug
     * @return string
     */
    public function getLayout() {
        return $this->_layout;
    }

    /**
     * set layout to use
     * @author mlaug
     * @param string $layout
     */
    public function setLayout($layout) {
        $this->_layout = $layout;
        
        // rewrite those variables
        $this->_smarty->template_dir = $this->_config->smarty->template_dir . "/" . $this->_layout;
        $this->_smarty->compile_dir = $this->_config->smarty->compile_dir . "/" . $this->_layout;
    }

    /**
     * Check if the template is cached
     * @author vpriem
     * @param string $template
     * @param string $cacheId
     */
    public function isSmartyCached($template, $cacheId = null) {
        return $this->_smarty->isCached($template, $cacheId);
    }

    /**
     * Enable smarty cache
     * @author vpriem
     * @param int $lifetime
     */
    public function enableSmartyCache($lifetime = null) {
        if ($lifetime !== null) {
            $this->_smarty->cache_lifetime = $lifetime;
        }
        if ($this->_smartyCache) {
            $this->_smarty->caching = 1;
        }
    }

    /**
     * Disable smarty cache
     * @author vpriem
     */
    public function disableSmartyCache() {
        $this->_smarty->caching = 0;
    }

    /**
     * Enable cache
     * @author vpriem
     * @since 07.07.2011
     */
    public function enableCache() {      
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && !count($_GET)) {
            $this->_cache = true;
            $this->_smarty->load_filter('output','trimwhitespace'); 
        }
    }

    /**
     * Disable cache
     * @author vpriem
     * @since 07.07.2011
     */
    public function disableCache() {
        $this->_cache = false;
    }
    
    /**
     * Fetch template
     * @param string $template
     * @param string $cacheId
     * @author vpriem
     */
    public function fetch($template, $cacheId = null) {
        $this->_logger->debug(sprintf('SMARTY: fetching template %s with cachetag %s', $template, $cacheId));
        return $this->_smarty->fetch($template, $cacheId);
    }

    /**
     * overwrite default template name
     * @author mlaug
     * @param string $name
     */
    public function setName($name) {
        $this->_name = $name;
    }

    /**
     * overwrite default template dir
     * @author mlaug
     * @param string $dir
     */
    public function setDir($dir) {
        $this->_dir = $dir;
    }

    /**
     * Set the path to the templates
     * @author mlaug
     * @param string $path The directory to set as the path.
     * @return void
     */
    public function setScriptPath($path) {
        
        if ($path === null) {
            return;
        }
        
        if (is_readable($path)) {
            $this->_smarty->template_dir = $path;
            return;
        }
        
        $this->_logger->crit(sprintf('SMARTY: provided path %s is not readable', $path));
        throw new Exception(sprintf('Invalid path %s provided, not readable', $path));
    }

    /**
     * Retrieve the current template directory
     * @author mlaug
     * @return array
     */
    public function getScriptPaths() {
        
        return (array) $this->_smarty->template_dir;
    }

    /**
     * Alias for setScriptPath
     * @author mlaug
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function setBasePath($path, $prefix = 'Zend_View') {
        return $this->setScriptPath($path);
    }

    /**
     * Alias for setScriptPath
     * @author mlaug
     * @param string $path
     * @param string $prefix Unused
     * @return boolean
     */
    public function addBasePath($path, $prefix = 'Zend_View') {
        return $this->setScriptPath($path);
    }

    /**
     * Assign a variable to the template
     * @author mlaug
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val) {
        $this->_smarty->assign($key, $val);
    }

    /**
     * Get a assigned variable
     * @author vpriem
     * @since 28.09.2011
     * @param string $key The variable name.
     * @return mixed
     */
    public function __get($key) {
        
        return $this->_smarty->get_template_vars($key);
    }
    
    /**
     * Allows testing with empty() and isset() to work
     * @author mlaug
     * @param string $key
     * @return boolean
     */
    public function __isset($key) {
        return (null !== $this->_smarty->get_template_vars($key));
    }

    /**
     * Allows unset() on object properties to work
     * @author mlaug
     * @param string $key
     * @return void
     */
    public function __unset($key) {
        $this->_smarty->clear_assign($key);
    }

    /**
     * Assign variables to the template
     * Allows setting a specific key to the specified value, OR passing
     * an array of key => value pairs to set en masse.
     * @author mlaug
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or
     * array of key => value pairs)
     * @param mixed $value (Optional) If assigning a named variable,
     * use this as the value.
     * @return void
     */
    public function assign($spec, $value = null) {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
            return;
        }

        $this->_smarty->assign($spec, $value);
    }

    /**
     * Assign variables globally to the template
     * Allows setting a specific key to the specified value, OR passing
     * an array of key => value pairs to set en masse.
     * @deprecated should be deprecated with smarty 3.x
     * @author mlaug
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or
     * array of key => value pairs)
     * @param mixed $value (Optional) If assigning a named variable,
     * use this as the value.
     * @return void
     */
    public function assign_global($spec, $value = null) {
        if (is_array($spec)) {
            $this->_smarty->assign_global($spec);
        }
        $this->_smarty->assign_global($spec, $value);
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via
     * {@link assign()} or property overloading
     * ({@link __get()}/{@link __set()}).
     * @author mlaug
     * @return void
     */
    public function clearVars() {
        $this->_smarty->clear_all_assign();
    }

    /**
     * Processes a template and returns the output.
     * @author mlaug
     * @param string $name The template to process.
     * @return string The output.
     */
    public function render($name = null) {
        
        if ($name === null && $this->_name === null) {
            throw Exception('No template bound to smarty');
        }
        
        if ($this->_dir === null) {
            $dir = dirname($name);
        }
        else {
            $dir = $this->_dir;
        }
        
        if ($this->_name === null) {
            $name = basename($name);
        }
        else {
            $name = $this->_name;
        }
        
        $template = $dir . "/" . $name;

        $this->_logger->debug(sprintf('SMARTY: rendering template %s', $template));
        try {
            $render = $this->_smarty->fetch($template);
            
            // cache
            if ($this->_cache && isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']) {
                $filename = @parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if ($filename !== false && $filename !== null) {
                    if (substr($filename, -1) == "/") {
                        $filename .= "index";
                    }

                    $filename = APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . $filename . ".html";
                    if (!file_exists($filename)) {
                        $this->_logger->debug(sprintf('SMARTY: caching %s', $filename));

                        $dirname = dirname($filename);
                        if (!is_dir($dirname) && !is_file($dirname)) {
                            @mkdir($dirname, 0777, true);
                        }

                        if (@file_put_contents($filename, $render) === false) {
                            $this->_logger->crit(sprintf('SMARTY: could not cache "%s" into "%s"', $template, $filename));
                        }
                    }
                }
            }
            
            return $render;
        }
        catch (Exception $e) {
            
            // check if any error during template generation
            if (IS_PRODUCTION) {
                $errors = array();
                $errors[] = $e->getMessage();
                $errors[] = $e->getTraceAsString();
                if (function_exists("get_error_source")) {
                    $errors = array_merge($errors, get_error_source());
                }
                Yourdelivery_Sender_Email::error(implode("\n", $errors), true);

                return $this->_smarty->fetch('error/throw.htm');
            }
            
            return $e->getMessage() . $e->getTraceAsString();
        }
    }

    /**
     * Add the id of an element to the array of elements to be disabled / hidden
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @return boolean
     */
    public function disableElement($elementId) {
        if (!in_array($elementId, $this->_disabledElements)) {
            if (array_push($this->_disabledElements, $elementId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove an ElementId from arary of disabledElements
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @param String $elementId
     * @return boolean
     */
    public function enableElement($elementId) {
        $key = array_search($elementId, $this->_disabledElements);
        if ($key) {
            unset($this->_disabledElements[$key]);
            return true;
        }
        return false;
    }

    /**
     * Return array of disabled Elements
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @return array
     */
    public function getDisabledElements() {
        return $this->_disabledElements;
    }

}

