<?php

/**
 * @author mlaug
 */
class Default_Helpers_Xml {

    /**
     * @author mlaug
     * @since 29.10.2010
     * @param DOMDocument $doc
     * @param string $name
     * @param mixed $value
     * @return DOMElement
     */
    static public function createNode(DOMDocument $doc, $name, $value, $attributeName = null, $attributeValue = null) {
        $elem = $doc->createElement($name);

        if (!is_null($attributeName) && !is_null($attributeValue)) {
            $elem->setAttribute($attributeName, $attributeValue);
        }

        $elem->appendChild($doc->createTextNode($value));
        return $elem;
        
    }

}
