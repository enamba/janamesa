<?php

/**
 * eBanking payment refund request
 * Build the request
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.01.2012
 */
class Yourdelivery_Payment_Ebanking_Refund_Request {
    
    /**
     * @var string 
     */
    private $_transaction;
    
    /**
     * @var int 
     */
    private $_amount;
    
    /**
     * @var string 
     */
    private $_comment;

    /**
     * @var DOMDocument 
     */
    private $_doc;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @param string $transaction
     * @param int $amount
     * @param string $comment
     */
    public function __construct($transaction, $amount, $comment = null) {
        
        $this->_transaction = $transaction;
        $this->_amount = $amount;
        $this->_comment = $comment;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return DOMDocument
     */
    public function build() {
        
        if ($this->_doc instanceof DOMDocument) {
            return $this->_doc;
        }
        
        // create xml document
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // create refunds
        $refunds = $doc->createElement('refunds');
        $doc->appendChild($refunds);
        
        // create refund
        $refund = $doc->createElement('refund');
        $refunds->appendChild($refund);
        
        // transaction
        $transaction = $doc->createElement('transaction');
        $transaction->appendChild($doc->createTextNode($this->_transaction));
        $refund->appendChild($transaction);
        
        // amount
        $amount = $doc->createElement('amount');
        $amount->appendChild($doc->createTextNode($this->_amount));
        $refund->appendChild($amount);
        
        if ($this->_comment !== null) {
            // comment
            $comment = $doc->createElement('comment');
            $comment->appendChild($doc->createTextNode($this->_comment));
            $refund->appendChild($comment);
        }
        
        return $this->_doc = $doc;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return string
     */
    public function __toString() {
        
        return $this->build()
                    ->saveXML();
    }
}
