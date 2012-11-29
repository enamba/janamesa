<?php
/**
 * Backlink Db Table
 * 
 * @author Vincent Priem <priem@lieferando.de>
 * @since  20.09.2010
 */
class Yourdelivery_Model_DbTable_Backlink extends Zend_Db_Table_Abstract {

    protected $_defaultSource = self::DEFAULT_DB;
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Backlink';

    /**
     * Table name
     */
    protected $_name = 'backlinks';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * Find row
     * 
     * @param int $id id of row to find
     * 
     * @return Zend_Db_Table_Row_Abstract
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since  20.09.2010
     */
    public function findRow($id) {

        if ($id !== null) {
            $rows = $this->find($id);
            if ($rows->count()) {
                return $rows->current();
            }
        }

        return false;
    }

    /**
     * Get all backlinks for the grid
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since  20.09.2010
     * 
     * @return Zend_Db_Select
     */
    public static function getGrid() {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        return $db->select()
                        ->from("backlinks", array("id", "domain", "url", 'Status' => "state", 'Fehler' => "error"))
                        ->order("url");
    }

}

/**
 * Backlink Db Table Row
 * 
 * @author Vincent Priem <priem@lieferando.de>
 * @since  20.09.2010
 */
class Yourdelivery_Model_DbTableRow_Backlink extends Zend_Db_Table_Row_Abstract {

    /**
     * Pre-update
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since  18.11.2010
     * 
     * @return void
     */
    protected function _update() {

        $this->updated = date("Y-m-d H:i:s");
    }

    /**
     * Check
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since  22.09.2010
     * 
     * @return boolean
     */
    public function check() {

        $this->result = "";
        $this->error = "";
        $this->state = -1;

        // get headers
        $headers = @get_headers("http://" . $this->url);
        if (is_array($headers) && ($headers[0] == "HTTP/1.1 200 OK" || $headers[0] == "HTTP/1.0 200 OK")) {
            // get html
            $html = @file_get_contents("http://" . $this->url);
            if ($html) {
                // get body content
                if (preg_match('`<body[^>]*>(.+)</body>`s', $html, $matches)) {
                    // get anchor
                    if (preg_match('`<a([^>]*)href="http://' . $this->domain . '/?[^"]*"([^>]*)>(?:.+)</a>`siU', $matches[1], $matches)) {
                        $this->result = $matches[0];
                        $this->state = 1;

                        // get anchor extras
                        if (isset($matches[1]) || isset($matches[2])) {
                            if (preg_match_all('`([a-z]+)\s*=\s*"([^"]+)"`i', $matches[1] . $matches[2], $matches, PREG_SET_ORDER)) {
                                foreach ($matches as $match) {
                                    switch ($match[1]) {
                                        case "title":
                                        case "class":
                                        case "onclick":
                                            break;

                                        case "target":
                                            if ($match[2] != "_blank") {
                                                $this->state = -1;
                                            }
                                            break;

                                        case "rel":
                                            if ($match[2] == "nofollow") {
                                                $this->state = -1;
                                            }
                                            break;

                                        default:
                                            $this->state = -1;
                                            break;
                                    }
                                }
                            }
                            if ($this->state < 0) {
                                $this->error = "Bad link attributes";
                            }
                        }
                    } else {
                        $this->error = "Link not found";
                    }
                } else {
                    $this->error = "Missing body tags";
                }
            } else {
                $this->error = "Bad content";
            }
        } else {
            $this->error = "Bad header";
        }

        $this->save();
        return $this->state > 0 ? true : false;
    }

}