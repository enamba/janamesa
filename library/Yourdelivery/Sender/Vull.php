<?php
/**
 * Description of Yourdelivery_Sender_Email_Vull
 * @package sender
 * @subpackage email
 * @author vpriem
 * @since 14.06.20111
 */
class Yourdelivery_Sender_Vull{

    /**
     * Belegnr
     * @var string
     */
    private $_nr;
    
    /**
     * Pdf
     * @var string
     */
    private $_pdf;
    
    /**
     * Txt
     * @var string
     */
    private $_txt;
    
    /**
     * Constructor
     * @author vpriem
     * @since 14.06.20111
     * @param string $nr
     * @param string $pdf
     * @param string $txt
     */
    public function __construct ($nr = null, $pdf = null, $txt = null) {

        if ($nr !== null) {
            $this->setNr($nr);
        }
        if ($pdf !== null) {
            $this->setPdf($pdf);
        }
        if ($txt !== null) {
            $this->setTxt($txt);
        }

    }

    /**
     * Set nr
     * @author vpriem
     * @since 14.06.20111
     * @param string $nr
     * @return Yourdelivery_Sender_Email_Vull
     */
    public function setNr ($nr) {

        $this->_nr = preg_replace("/[^0-9]/", "", $nr);
        return $this;

    }
    
    /**
     * Get nr
     * @author vpriem
     * @since 14.06.20111
     * @return string
     */
    public function getNr() {

        return $this->_nr;

    }
    
    /**
     * Set pdf
     * @author vpriem
     * @since 14.06.20111
     * @param string $pdf
     * @return Yourdelivery_Sender_Email_Vull
     */
    public function setPdf ($pdf) {

        $this->_pdf = $pdf;
        return $this;

    }
    
    /**
     * Get pdf
     * @author vpriem
     * @since 14.06.20111
     * @return string
     */
    public function getPdf() {

        return $this->_pdf;

    }
  
    /**
     * Set txt
     * @author vpriem
     * @since 14.06.20111
     * @param string $txt
     * @return Yourdelivery_Sender_Email_Vull
     */
    public function setTxt ($txt) {

        $this->_txt = $txt;
        return $this;

    }
    
    /**
     * Get txt
     * @author vpriem
     * @since 14.06.20111
     * @return string
     */
    public function getTxt() {

        return $this->_txt;

    }
    
    /**
     * Sends thie email
     * @author vpriem
     * @since 14.06.20111
     * @return boolean
     */
    public function send() {

        
        $nr = $this->_nr;
        if (empty($nr)) {
            throw new Zend_Exception('Nr cannot be empty');
        }
        if (strlen($nr) > 16) {
            throw new Zend_Exception('Nr longer than 16 chars');
        }
        if (!file_exists($this->_txt)) {
            throw new Zend_Exception('TXT file not found');
        }
        if (!file_exists($this->_pdf)) {
            throw new Zend_Exception('PDF file not found');
        }
        
        $email = new Yourdelivery_Sender_Email();
        return $email->setSubject("41 DATEN L" . $this->_nr)
             ->addTo("EMAIL")
             ->addCc("EMAIL")
             ->attachTxt($this->_txt, "L" . $this->_nr . ".TXT")
             ->attachPdf($this->_pdf, "L" . $this->_nr . ".PDF")
             ->setBodyText($this->_nr)
             ->send();

    }

}