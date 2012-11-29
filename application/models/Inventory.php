<?php
/**
 * @author vait
 */
class Yourdelivery_Model_Inventory extends Default_Model_Base {
    
    /**
     * add a comment to an item
     * @author mlaug
     * @since 20.04.2011
     * @param string $type
     * @param string $comment
     * @param string $status
     * @param integer $adminId
     * @return float 
     */
    public function addComment($type, $comment, $status, $adminId) {
        if ($this->getId() === null) {
            return false;
        }

        if (strlen($status) <= 1) {
            return true;
        }

        $table = new Yourdelivery_Model_DbTable_Inventory_Status();
        $table->createRow(array(
            'inventoryId' => $this->getId(),
            'type' => $type,
            'status' => $status,
            'comment' => $comment,
            'adminId' => $adminId,
        ))->save();
        
        return true;
    }

    /**
     * get all comments of a given type
     * @author mlaug
     * @since 20.04.2011
     * @param string $type
     * @return array
     */
    public function getComments($type) {
        $table = new Yourdelivery_Model_DbTable_Inventory_Status();
        return $table->getAllStates($this->getId(), $type);
    }
    
    /**
     * get value of goods
     * @author mlaug
     * @since 14.06.2011
     * @return integer
     */
    public function getValueOfGoods(){
        
        return $this->getCostCanton2626() 
               + $this->getCostCanton2828() 
               + $this->getCostCanton3232()
               + $this->getCostServicing()
               + $this->getCostBags()
               + $this->getCostSticks()
        ;
    }

    /**
     * @author vpriem
     * @since 15.06.2011
     * @return integer
     */
    public function getCostCanton2626(){
        
        $specialCost = $this->getSpecialCostCanton2626();
        $specialCost = $specialCost > 0 ? $specialCost : 4900;
        
        return $specialCost * $this->getCountCanton2626(); // 700
        
    }
    
    public function getCostCanton2828(){
        
        $specialCost = $this->getSpecialCostCanton2828();
        $specialCost = $specialCost > 0 ? $specialCost : 6300;
        
        return $specialCost * $this->getCountCanton2828(); // 700
        
    }
    
    public function getCostCanton3232(){
        
        $specialCost = $this->getSpecialCostCanton3232();
        $specialCost = $specialCost > 0 ? $specialCost : 7000;
        
        return $specialCost * $this->getCountCanton3232(); // 700
        
    }
    
    public function getCostServicing(){

        return 1500 * $this->getCountServicing(); // 1000
        
    }
    
    public function getCostBags(){

        return 1250 * $this->getCountBags(); // 2500
        
    }
    
    public function getCostSticks(){

        return 2100 * $this->getCountSticks(); // 3000
        
    }
    
    public function getCostTerminal(){

        if ($this->getTerminal() == 1) {
            return $this->getTerminalBail() * 100;
        }
        return 0;
    }
    
    /**
     * Get related table
     * @author vpriem
     * @since 15.06.2011
     * @return Yourdelivery_Model_DbTable_Inventory
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Inventory();
        }
        return $this->_table;
        
    }

    /**
     * Get vull txt
     * ISO-8859-1 string encoded
     * @author vpriem
     * @since 20.06.2011
     * @return string
     */
    public function getAsTxt($billingNr) {
        
        $billingNr = preg_replace("/[^0-9]/", "", $billingNr);
        $lines = array();
        
        // head
        $service = $this->getService();
        $city = $service->getCity();
        $lines[] = array(
            'Kennung' => "K", 
            'Kundennr' => "YD", 
            'Name' => $service->getName(), 
            'Zusatzname1' => "", 
            'Zusatzname2' => "", 
            'Laenderkennzeichen' => "", 
            'Plz' => $service->getPlz(), 
            'Ort' => $city->getCity(), 
            'Strasse' => $service->getStreet() . " " . $service->getHausnr(), 
            'Bestelldatum' => date("d.m.Y"), 
            'Belegdatum' => date("d.m.Y"),  
            'Bestellzeichen' => "", 
            'Belegnr' => $billingNr, 
            'Belegart' => "L",
            'Auftragsnr' => $billingNr, 
            'Belegtext' => "", 
            'Nachnahme' => 0, 
            'USt' => "J", 
            'Gewicht' => 0, 
            'Lieferweg' => 288, 
            'Priorität' => 1, 
            'Mandantnr' => 41, 
            'Erstauslieferung' => N, 
            'Labeldruck' => 0, 
            'Zusatzname3' => "", 
            'E-Mail Adresse' => "", 
            'Intern1' => "",
            'Intern2' => "",
        );
        
        // position
        $p = 0;
        if ($this->getCountCanton2626() > 0) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Pizzakarton Größe: 26x26x4",
                'Druckdaten2' => "",
                'DruckArtikelnummer' => "PK26",
                'DipaArtikelnummer' => "PK26",
                'Menge' => $this->getCountCanton2626(),
                'Partie' => "0",
                'Meldenr' => "0",
                'Netto' => "N",
                'BruttoPreis' => "0",
                'Rabatt' => "0",
                'BruttoAbgabePreis' => "0",
                'NettoAbgabePreis' => "0",
                'BruttoUmsatz' => "0",
                'NettoUmsatz' => "",
                'UstKennzeichen' => "2",
            );
        }
        if ($this->getCountCanton2828()) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Pizzakarton Größe: 28x28x4",
                'Druckdaten2' => "",
                'DruckArtikelnummer' => "PK28",
                'DipaArtikelnummer' => "PK28",
                'Menge' => $this->getCountCanton2828(),
                'Partie' => "0",
                'Meldenr' => "0",
                'Netto' => "N",
                'BruttoPreis' => "0",
                'Rabatt' => "0",
                'BruttoAbgabePreis' => "0",
                'NettoAbgabePreis' => "0",
                'BruttoUmsatz' => "0",
                'NettoUmsatz' => "",
                'UstKennzeichen' => "2",
            );
        }
        if ($this->getCountCanton3232()) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Pizzakarton Größe: 32x32x4",
                'Druckdaten2' => "",
                'DruckArtikelnummer' => "PK32",
                'DipaArtikelnummer' => "PK32",
                'Menge' => $this->getCountCanton3232(),
                'Partie' => "0",
                'Meldenr' => "0",
                'Netto' => "N",
                'BruttoPreis' => "0",
                'Rabatt' => "0",
                'BruttoAbgabePreis' => "0",
                'NettoAbgabePreis' => "0",
                'BruttoUmsatz' => "0",
                'NettoUmsatz' => "",
                'UstKennzeichen' => "2",
            );
        }
        if ($this->getCountServicing()) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Servietten 2lagig, 33x33",
                'Druckdaten2' => "",
                'DruckArtikelnummer' => "SE",
                'DipaArtikelnummer' => "SE",
                'Menge' => $this->getCountServicing(),
                'Partie' => "0",
                'Meldenr' => "0",
                'Netto' => "N",
                'BruttoPreis' => "0",
                'Rabatt' => "0",
                'BruttoAbgabePreis' => "0",
                'NettoAbgabePreis' => "0",
                'BruttoUmsatz' => "0",
                'NettoUmsatz' => "",
                'UstKennzeichen' => "2",
            );
        }
        if ($this->getCountBags()) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Plastiktüten",
                'Druckdaten2' => "",
                'DruckArtikelnummer' => "PT",
                'DipaArtikelnummer' => "PT",
                'Menge' => $this->getCountBags(),
                'Partie' => "0",
                'Meldenr' => "0",
                'Netto' => "N",
                'BruttoPreis' => "0",
                'Rabatt' => "0",
                'BruttoAbgabePreis' => "0",
                'NettoAbgabePreis' => "0",
                'BruttoUmsatz' => "0",
                'NettoUmsatz' => "",
                'UstKennzeichen' => "2",
            );
        }
        if ($this->getCountSticks()) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Chopsticks",
                'Druckdaten2' => "",
                'DruckArtikelnummer' => "CS",
                'DipaArtikelnummer' => "CS",
                'Menge' => $this->getCountSticks(),
                'Partie' => "0",
                'Meldenr' => "0",
                'Netto' => "N",
                'BruttoPreis' => "0",
                'Rabatt' => "0",
                'BruttoAbgabePreis' => "0",
                'NettoAbgabePreis' => "0",
                'BruttoUmsatz' => "0",
                'NettoUmsatz' => "",
                'UstKennzeichen' => "2",
            );
        }
        
        // foot
        $lines[] = array(
            'Kennung' => "F",
            'Lieferwegtext' => "",
            'Umsatzgitter' => "N",
            'Porto USt1 Netto' => "",
            'Porto USt1 USt' => "",
            'Porto USt1 Brutto' => "",
            'Porto USt2 Netto' => "",
            'Porto USt2 USt' => "",
            'Porto USt2 Brutto' => "",
            'Ware USt1 Netto' => "",
            'Ware USt1 USt' => "",
            'Ware USt1 Brutto' => "",
            'Ware USt2 Netto' => "",
            'Ware USt2 USt' => "",
            'Ware USt2 Brutto' => "",
            'SteuerlichesEntgelt' => "",
            'USt1' => "",
            'USt2' => "",
            'BruttoPorto' => "",
            'Rechnungsbetrag' => "",
            'Fälligkeit Nettokasse' => "",
            'Fälligkeit Skonto' => "",
            'Zahlart' => "",
        );
        
        $_lines = array();
        foreach ($lines as $line) {
            $_lines[] = implode("|", $line);
        }
        return utf8_decode(implode(CRLF, $_lines));
    }
    
}
