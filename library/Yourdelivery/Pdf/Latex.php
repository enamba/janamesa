<?php

/**
 * Description of Latex to PDF generator
 * @package pdf
 * @subpackage latex
 * @author mlaug
 */
class Yourdelivery_Pdf_Latex extends Default_Model_Base {

    /**
     * path to template directory
     * @var string
     */
    private $_templateDir = null;
    /**
     * path to store generated pdfs
     * @var string
     */
    private $_compileDir = null;
    /**
     * name or type of latex file
     * @var string
     */
    private $_type = null;
    /**
     * tempalte
     * @var string
     */
    private $_tpl = null;
    /**
     * the smarty object
     * @var Smarty
     */
    private $_smarty = null;

    /**
     * stores some data in session/cache
     * @author mlaug
     * @return array
     */
    public function __sleep() {
        $this->_table = null;
        return array('_data', '_id', '_tpl', '_type', '_compileDir', '_templateDir', '_smarty');
    }

    /**
     *
     * @author mlaug
     * @param array $data
     */
    public function __construct($data = array()) {

        $config = Zend_Registry::get('configuration');
        $this->setTemplateDir($config->latex->template_dir);
        $this->setCompileDir($config->latex->compile_dir);

        $this->_smarty = new Smarty();
        $this->_smarty->config_dir = $config->smarty->config_dir;
        $this->_smarty->cache_dir = $config->smarty->cache_dir;
        $this->_smarty->caching = false;

        // assign some variables
        $this->_smarty->assign('config', $config);
        $this->_smarty->assign('DOMAIN_BASE', $config->domain->base);
        $this->_smarty->assign('taxes', $config->tax->types->toArray());
        
        //change delimiter to avoid problems with latex syntax
        $this->_smarty->left_delimiter = "<<";
        $this->_smarty->right_delimiter = ">>";
    }

    /**
     * set type
     * @author mlaug
     * @param string $type
     */
    public function setType($type) {
        $this->_type = $type;
    }

    /**
     *
     * @author mlaug
     * @param mixed string|array $spec
     * @param string $value
     * @return null
     */
    public function assign($spec, $value = null) {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
            return;
        }

        $this->_smarty->assign($spec, $value);
    }

    /**
     * get type or name of latex file
     * @author mlaug
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * set template to use
     * @author mlaug
     * @param string $tpl
     */
    public function setTpl($tpl) {
        $this->_tpl = $tpl;
    }

    /**
     * get current template
     * @author mlaug
     * @return string
     */
    public function getTpl() {
        return $this->_tpl;
    }

    /**
     * get temaplte directory
     * @author mlaug
     * @return string
     */
    public function getTemplateDir() {
        return $this->_templateDir;
    }

    /**
     * set template directory
     * @author mlaug
     * @param string $tpl
     */
    public function setTemplateDir($tpl) {
        $this->_templateDir = $tpl;
    }

    /**
     * set compile directory
     * @author mlaug
     * @param string $cpl
     */
    public function setCompileDir($cpl) {
        $this->_compileDir = $cpl;
    }

    /**
     * get compile directory
     * @author mlaug
     * @return string
     */
    public function getCompileDir() {
        return $this->_compileDir;
    }

    /**
     * delete compile directory
     * @author mlaug
     */
    public function cleanUp() {
        $d = $this->getCompileDir();
        $fd = opendir($d);
        while ($file = readdir($fd)) {
            if ($file != "." && $file != ".." && $file != ".svn") {
                if (strstr($file, 'pdf')) {
                    continue;
                }
                unlink($d . '/' . $file);
            }
        }
    }

    /**
     * compile latex file
     * we need all directories set before and the latex file must exists
     * @author mlaug
     * @return string
     */
    public function compile($cleanup = false, $removeLastPage = false) {

        $this->_smarty->template_dir = $this->getTemplateDir();
        $this->_smarty->compile_dir = $this->getCompileDir();

        //if any syntax error occurse, we want to be informed
        try {
            $output = $this->_smarty->fetch($this->getTpl() . ".tpl");
        } catch (Exception $e) {
            $error = "Syntax Error in latex file:\n" . $e->getMessage();
            $this->logger->err($error);
            if (APPLICATION_ENV == "production") {
                Yourdelivery_Sender_Email::error($error);
            } else {
                die($error);
            }
            return null;
        }

        $texfile = $this->getCompileDir()
                . '/compile_'
                . date('U')
                . '_'
                . Default_Helper::generateRandomString()
                . '.tex';

        $fp = fopen($texfile, "a+");
        fputs($fp, $output);
        fclose($fp);

        if (!file_exists('/usr/bin/pdflatex')) {
            if (APPLICATION_ENV == "production") {
                $error = "Latex Binary missing";
                $this->logger->err($error);
                Yourdelivery_Sender_Email::error($error, true);
                return false;
            } else {
                return APPLICATION_PATH . '/templates/fax/testfax/ydtestfax.pdf';
            }
        }

        $command = sprintf("/usr/bin/pdflatex %s -output-directory=%s &", $texfile, $this->getCompileDir());
        chdir($this->getCompileDir());

        //run three times
        for ($i = 1; $i < 3; $i++) {
            $cmdret = null;
            ob_start();
            passthru($command, $cmdret);
            ob_get_contents();
            ob_end_clean();
        }

        //get pdf file and return this one
        $pdf = substr($texfile, 0, -4) . ".pdf";

        if (file_exists($pdf)) {
            if ($cleanup) {
                //do not cleanup for the moment
                //$this->cleanUp();
            }
            
            //this should be a fix, for the landscape page break problem
            if ( $removeLastPage ){
                $pdfEdit = Zend_Pdf::load($pdf);
                $lastPage = count($pdfEdit->pages)-1;
                unset($pdfEdit->pages[$lastPage]);
                $pdfEdit->save($pdf);              
            }
            
        } else {
            $err = sprintf('Konnte PDF nicht erstellt werden! Texfile korrupt: %s', $texfile);
            $this->logger->err($err);
            Yourdelivery_Sender_Email::error($err, true);
            return false;
        }

        return $pdf;
    }

    public function getTable() {
        return null;
    }

}

?>
