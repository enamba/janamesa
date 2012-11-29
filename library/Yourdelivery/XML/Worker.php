<?php


class Yourdelivery_XML_Worker {

    /**
     * @author mlaug
     * @param string $buffer
     * @param int $id
     * @return string
     */
    public function array2xml($buffer, $id) {
        $xml = "";
        $xml .= "<element>\n";

        foreach ($buffer as $key => $value) {
            $xml .= "<{$key}>".utf8_encode($value)."</{$key}>\n";
        }

        $xml .= "</element>\n";

        return $xml;
    }

}

?>