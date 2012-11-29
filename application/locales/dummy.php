<?php

/*
 * Dummy Datei für Status Texte aus der DB
 * and open the template in the editor.
 */
echo __("Send out order to service via fax");
echo __("Send out order to service via fax by Supporter");
echo __("Maybe reception of fax: NO_TRAIN");

/**
 * dummy texts for fidelity points
 * @author mlaug
 * @see Yourdelivery_Model_Customer_Fidelity::getTransactionsVerbose
 */
echo __("fidelity_order %s %s %s");
echo __("fidelity_order %s"); // Bestellung über API - Für Deine BEstellung erhältst du XX Treuepunkte
echo __("fidelity_rate_low %s %s"); //Bewertung bei Dienstleister XXX am YYY
echo __("fidelity_rate_high %s %s"); //Bewertung bei Dienstleister XXX am YYY
echo __("fidelity_register %s"); //Registrierung am XXX
echo __("fidelity_registeraftersale %s"); //Registrierung am XXX
echo __("fidelity_usage %s %s %s"); //Treuepunkte bei YYY eingelöst am XXX
echo __("fidelity_accountimage %s"); //Bild XXX hochgeladen am YY
echo __("fidelity_facebookconnect %s %s"); //Facebook

/**
 * dummy texts for franchise types
 * @author jnaie
 */
echo __b("Normal");
echo __b("NoContract");
echo __b("Premium"); 
echo __b("OfflinePayment"); // Jánamesa franchise type mit Funktion wie noContract
