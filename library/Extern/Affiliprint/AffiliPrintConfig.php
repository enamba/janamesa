<?php
/**
 * AffiliPrintCom
 *
 * Klasse zur Kommunikation mit der Affiliprint GmbH
 * 
 * Erste Version erstellt am 13.09.2010
 * 
 * @see example.php für ein einfaches Beispiel, wie die Klasse verwendet wird
 * @package AffiliPRINT
 * @author Nils Peters, n.peters@affiliprint.de
 * @version 1.1
 * @copyright AffiliPRINT Projekt - 2010
 */

class AffiliPrintConfig
{
	// Your Affiliate-ID. 
	// For testing purposes you can leave the ID "TestOnlyEnterYourCodeHere" here.
	// Important: As long as you are not using your valid UID, 
	// The results of your actions are (of course) NOT affected by / affecting your data.
	// The ID "AlwaysValid" will always return VALID	
	// The ID "AlwaysWrong" will always return INVALID	

	PUBLIC 	$AFFILIATEUID = "KEY";
	
	// For Testing-Use:
//	PUBLIC $AFFILIATEUID = "AlwaysValid";
//	PUBLIC $AFFILIATEUID = "AlwaysWrong";
	
	// The URL, PORT and SCRIPT the script communicates with 
	/*
	PUBLIC	$COMURL = "127.0.0.1";
	PUBLIC	$COMPORT = 80;
	PUBLIC	$COMPATH = "/ap/interface/1.1/remote.php";
	PUBLIC 	$COMUSESSL = false;
	*/
	
	PUBLIC	$COMURL = "www.affiliprint.com";
	PUBLIC	$COMPORT = 443;
	PUBLIC	$COMPATH = "/interface/1.1/remote.php";
	PUBLIC 	$COMUSESSL = true;
	
	
	// Language of the messages returned - according to ISO 3166
	PUBLIC 	$LANGUAGE = "DE";
}
?>