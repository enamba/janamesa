<?php
/**
 * Interkep API
 * 
 * @author Vincent Priem <priem@lieferando.de>
 * @since 22.12.2011
 */
class Yourdelivery_Api_Interkep {
    
    /**
     * @var Yourdelivery_Model_Order 
     */
    private $_order;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param Yourdelivery_Model_Order $order 
     */
    public function __construct(Yourdelivery_Model_Order $order) {
    
        $this->_order = $order;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @param int $pickupTimestamp
     * @return boolean
     */
    public function writeImportFile() {
        
        if (file_exists($this->getImportFile())) {
            return false;
        }
        
        $order = $this->_order;
        $customer = $order->getCustomer();
        $location = $order->getLocation();
        $service = $order->getService();
        $courier = $service->getCourier();
        
        $pickupTimestamp = $order->computePickUpTime();
        $data = array(
            '01 Datensatz-Kennung' => "S",
            '02 Versandart' => "25",
            '03 Versandatum' => date("d.m.Y", $order->getTime()),
            '04 Anzahl' => "1",
            '05 Gewicht' => "1",
            '06 Versand-Nr.' => "",
            '07 Empfänger-Nr.' => "",
            '08 Empfänger-Name1' => $customer->getFullname(),
            '09 Empfänger-Name2' => ($location->getCompanyName() ? "Firma: " . $location->getCompanyName() : ""),
            '10 Empfänger-Ansprechpartner' => "",
            '11 Empfänger-Adresse1' => $location->getStreet(),
            '12 Empfänger-Hausnummer' => $location->getHausnr(),
            '13 Empfänger-Adresse2' => ($location->getEtage() ? "Stockwerk: " . $location->getEtage() : ""),
            '14 Empfänger-Land' => "D",
            '15 Empfänger-PLZ' => $location->getPlz(),
            '16 Empfänger-Ort' => $location->getCity()->getCity(),
            '17 Empfänger-Telefon' => $location->getTel(),
            '18 Auftraggeber-Nr.' => 51870,
            '19 Auftraggeber-Name1' => "",
            '20 Auftraggeber-Name2' => "",
            '21 Auftraggeber-Ansprechpartner' => "",
            '22 Auftraggeber-Adresse1' => "",
            '23 Auftraggeber-Hausnummer' => "",
            '24 Auftraggeber-Adresse2' => "",
            '25 Auftraggeber-Land' => "",
            '26 Auftraggeber-PLZ' => "",
            '27 Auftraggeber-Ort' => "",
            '28 Auftraggeber-Telefon' => "",
            '29 Referenz1' => "Kdnr " . $service->getCustomerNr(),
            '30 Referenz2' => "",
            '31 Termin-Datum' => date("d.m.Y", $pickupTimestamp),
            '32 Termin von Zeit' => date("H:i", $pickupTimestamp),
            '33 Termin bis Zeit' => date("H:i", $pickupTimestamp + $courier->getDeliverTime($location->getCityId())),
            '34 Nachnahme Betrag' => "",
            '35 Nachname Zahlart' => "",
            '36 Zustellbenach-richtigung' => "",
            '37 Zusatzversicherung' => "",
            '38 Persönliche Zustellung' => "",
            '39 KEP: BestSchick' => "",
            '40 Rechnungsempfänger' => "A",
            '41 RE-Kd.Nummer' => "",
            '42 RE-Name1' => "",
            '43 RE-Name2' => "",
            '44 RE-Ansprechpartner' => "",
            '45 RE-Adresse1' => "",
            '46 RE-Hausnummer' => "",
            '47 RE-Adresse2' => "",
            '48 RE-Land' => "",
            '49 RE-PLZ' => "",
            '50 RE-Ort' => "",
            '51 RE-Telefon' => "",
            '52 PersZustOpt' => "",
            '53 Absender-Nr.' => "",
            '54 Absender-Name1' => $service->getName(),
            '55 Absender-Name2' => "",
            '56 Absender-Ansprechpartner' => "",
            '57 Absender-Adresse1' => $service->getStreet(),
            '58 Absender-Hausnummer' => $service->getHausnr(),
            '59 Absender-Adresse2' => "",
            '60 Absender-Land' => "D",
            '61 Absender-PLZ' => $service->getPlz(),
            '62 Absender-Ort' => $service->getCity()->getCity(),
            '63 Absender-Telefon' => $service->getTel(),
            '64 Abhol-Datum' => date("d.m.Y", $pickupTimestamp),
            '65 Abhol von Zeit' => date("H:i", $pickupTimestamp),
            '66 Abhol bis Zeit' => date("H:i", $pickupTimestamp),
            '67 Warenwert Betrag' => "",
            '68 Zustell-Info' => $location->getComment() ? "Lieferanweisung: " . $location->getComment() : "",
        );
        
        // sanitize
        $data = array_map(function($value){
            return str_replace(";", "", $value);
        }, $data);
        
        $storage = new Default_File_Storage();
        $storage->setStorage(APPLICATION_PATH . '/../storage/');
        $storage->setSubFolder('orders/');
        $storage->store(basename($this->getImportFile()), utf8_decode(implode(";", $data)) . "\r\n");
        
        return true;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 30.01.2012
     * @return string
     */
    public function getImportFile() {
        
        return APPLICATION_PATH . '/../storage/orders/' . $this->_order->getId() . "-interkep.txt";
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @return string
     */
    public function getImportFileName() {
        
        return $this->_order->getNr() . ".imp";
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 22.12.2011
     * @throws Yourdelivery_Api_Interkep_Exception
     * @return boolean 
     */
    public function send() {
        
        // check if already send
        if (!$this->writeImportFile()) {
            return false;
        }
        
        if (IS_PRODUCTION) {
            $ftp = new Default_Ftp();
            if (!$ftp->connect("ftp.interkep.de")) {
                throw new Yourdelivery_Api_Interkep_Exception("Could not connect to Interkep FTP");
            }

            if (!$ftp->login("USER", "PASS")) {
                throw new Yourdelivery_Api_Interkep_Exception("Could not login to Interkep FTP");
            }
            
            $ftp->pasv(true);
            
            return $ftp->put($this->getImportFileName(), $this->getImportFile());
        }
        
        $mail = new Yourdelivery_Sender_Email();
        $mail->setSubject("Interkep Import File")
             ->setBodyText('INTERKEP')
             ->addTo("samson@tiffy.de") // trigger testing email
             ->attachTxt($this->getImportFile(), $this->getImportFileName())
             ->send();
        
        return true;
    }
}
