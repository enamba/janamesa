<?php
/**
 * Prompt Nr
 * @author vpriem
 */
class Yourdelivery_Model_DbTable_Prompt_Nr extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'prompt_nr';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Get ids by order
     * @author vpriem
     * @since 29.09.2010
     * @param int $orderId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findByOrder ($orderId) {

        return $this->fetchAll(
            $this->select()
                 ->where("`orderId` = ?", $orderId)
        );

    }

    /**
     * Test if a nr exists
     * @author vpriem
     * @since 09.12.2010
     * @param string $nr
     * @return boolean
     */
    public function nrExists ($nr) {

        return $this->fetchAll(
            $this->select()
                 ->where("`nr` = ?", $nr)
        )->count() > 0;

    }

    /**
     * Get random nr
     * @author vpriem
     * @since 09.12.2010
     * @return string
     */
    public static function getRandomNr ($length = 3) {

        $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numbers = "0123456789";

        $string = $letters{rand (0 , strlen($letters) - 1)};
        for ($i = 1; $i < $length; $i++) {
            $string .= $numbers{rand (0 , strlen($numbers) - 1)};
        }
        return $string;

    }

    /**
     * Get nr
     * @author vpriem
     * @since 09.12.2010
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return string
     */
    public static function getNr (Yourdelivery_Model_Order_Abstract $order) {

        $dbTable = new Yourdelivery_Model_DbTable_Prompt_Nr();

        // search
        $rows = $dbTable->findByOrder($order->getId());
        foreach ($rows as $row) {
            return $row->nr;
        }

        // insert
        $i = 0;
        $nr = self::getRandomNr();
        while ($dbTable->nrExists($nr)) {
            // increase the random string length after 20 tries
            $nr = self::getRandomNr(2 + ceil(++$i / 20));
        }
        $dbTable->createRow(array(
            'nr'      => $nr,
            'orderId' => $order->getId(),
        ))->save();
        
        return $nr;

    }

}
