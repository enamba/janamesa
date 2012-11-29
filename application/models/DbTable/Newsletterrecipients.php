<?php

class Yourdelivery_Model_DbTable_Newsletterrecipients extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'newsletter_recipients';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * get a row matching given emailaddress
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     * @param string $email
     * @return Zend_Db_Table_Rowset
     */
    public static function findByEmail($email) {
        $email = trim($email);
        if (empty($email) || !Default_Helper::email_validate($email)) {
            return false;
        }
        
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("l" => "newsletter_recipients"))
                ->where("l.email = ?", $email);

        return $db->fetchRow($query);
    }

    /**
     * find customer by hash of email
     *
     * @param string $hash hash of emailaddress of customer to search for
     *
     * @return Zend_DbTable_Row
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.04.2011
     */
    public static function findByEmailHash($hash) {

        $table = new Yourdelivery_Model_DbTable_Newsletterrecipients();
        return $table->select()
                ->where(sprintf('md5(CONCAT("%s",email,"%s"))=?', SALT, SALT), $hash)
                ->query()
                ->fetch();
    }

    /**
     * migrate email adress for newsletter recipient
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.02.2011
     *
     * @param string $oldEmail
     * @param string $newEmail
     */
    public static function migrateEmail($oldEmail, $newEmail) {
        
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('UPDATE newsletter_recipients SET email = "%s" WHERE email = "%s"', $newEmail, $oldEmail);
        $db->query($sql);
    }

}
