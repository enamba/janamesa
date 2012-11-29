<?php
/**
 * @author vpriem
 * @since 27.06.2011
 */
class Yourdelivery_Model_Upselling_Goods extends Default_Model_Base {
    
    const CANTON2626 = 200;
    const CANTON2626N = 200;
    const CANTON2626D = 200;
    const CANTON2626S = 200;
    const CANTON2626H = 200;
    const CANTON2828 = 180;
    const CANTON3232 = 180;
    const SERVICING = 2000;
    const BAGS = 2000;
    const CHOPSTICKS = 3000;
    
    const CANTON2626_PRICE = 600;
    const CANTON2626_WISH_PRICE = 1600;
    const CANTON2626N_PRICE = 600;
    const CANTON2626N_WISH_PRICE = 1600;
    const CANTON2626D_PRICE = 600;
    const CANTON2626D_WISH_PRICE = 1600;
    const CANTON2626S_PRICE = 600;
    const CANTON2626S_WISH_PRICE = 1600;
    const CANTON2626H_PRICE = 600;
    const CANTON2626H_WISH_PRICE = 1600;
    const CANTON2828_PRICE = 1440;
    const CANTON2828_WISH_PRICE = 2000;
    const CANTON3232_PRICE = 1620;
    const CANTON3232_WISH_PRICE = 2200;
    const SERVICING_PRICE = 1500;
    const BAGS_PRICE = 1000;
    const CHOPSTICKS_PRICE = 2100;
    
    /**
     * Billing object
     * @var Yourdelivery_Model_Billing_Upselling_Goods
     */
    private $_billing;
    
    /**
     * @author vpriem
     * @since 28.06.2011
     * @return Yourdelivery_Model_Billing_Upselling_Goods|null
     */
    public function getBilling(){
        
        if (!$this->getId()) {
            return null;
        }
        
        if ($this->_billing !== null) {
            return $this->_billing;
        }
        
        $row = Yourdelivery_Model_DbTable_Billing::findByRefIdAndMode($this->getId(), 'upselling_goods');
        if ($row) {
            return $this->_billing = new Yourdelivery_Model_Billing_Upselling_Goods($row['id']);
        }
        return null;
    }
    
    /**
     * Get value of goods
     * @author vpriem
     * @since 14.06.2011
     * @return int
     */
    public function calculateNetto() {
        
        return $this->getCountCanton2626() * $this->getCostCanton2626()
               + $this->getCountCanton2626N() * $this->getCostCanton2626N()
               + $this->getCountCanton2626D() * $this->getCostCanton2626D()
               + $this->getCountCanton2626S() * $this->getCostCanton2626S()
               + $this->getCountCanton2626H() * $this->getCostCanton2626H()
               + $this->getCountCanton2828() * $this->getCostCanton2828()
               + $this->getCountCanton3232() * $this->getCostCanton3232()
               + $this->getCountServicing() * $this->getCostServicing()
               + $this->getCountBags() * $this->getCostBags()
               + $this->getCountSticks() * $this->getCostSticks()
        ;
    }
    
    /**
     * @author vpriem
     * @since 15.06.2011
     * @return int
     */
    public function calculateTax() {
        
        return $this->calculateNetto() * 0.19;
    }

    /**
     * @author vpriem
     * @since 15.06.2011
     * @return int
     */
    public function calculateBrutto() {
        
        return $this->calculateNetto() + $this->calculateTax();
    }
    
    /**
     * Get related table
     * @author vpriem
     * @since 15.06.2011
     * @return Yourdelivery_Model_DbTable_Inventory
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Upselling_Goods();
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
        if ($this->getCountCanton2626N() > 0) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Pizzakarton Größe: 26x26x4",
                'Druckdaten2' => "Notebooksbilliger",
                'DruckArtikelnummer' => "PK26N",
                'DipaArtikelnummer' => "PK26N",
                'Menge' => $this->getCountCanton2626N(),
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
        if ($this->getCountCanton2626D() > 0) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Pizzakarton Größe: 26x26x4",
                'Druckdaten2' => "Discotel",
                'DruckArtikelnummer' => "PK26D",
                'DipaArtikelnummer' => "PK26D",
                'Menge' => $this->getCountCanton2626D(),
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
        if ($this->getCountCanton2626S() > 0) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Pizzakarton Größe: 26x26x4",
                'Druckdaten2' => "DeutschlandSIM",
                'DruckArtikelnummer' => "PK26S",
                'DipaArtikelnummer' => "PK26S",
                'Menge' => $this->getCountCanton2626S(),
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
        if ($this->getCountCanton2626H() > 0) {
            $lines[] = array(
                'Kennung' => "P",
                'Nr' => ++$p,
                'Art' => "N",
                'Druckdaten1' => "Pizzakarton Größe: 26x26x4",
                'Druckdaten2' => "Hannover",
                'DruckArtikelnummer' => "PK26HANPUSH",
                'DipaArtikelnummer' => "PK26HANPUSH",
                'Menge' => $this->getCountCanton2626H(),
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
