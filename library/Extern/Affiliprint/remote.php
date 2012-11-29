<?php
/**
 * Remote
 *
 * Klasse zur Kommunikation mit Affiliprint GmbH
 * 
 * Erste Version erstellt am 04.05.2010
 * 
 * @package AffiliPRINT
 * @author Nils Peters, n.peters@affiliprint.de
 * @version 0.1
 * @copyright AffiliPRINT Projekt - 2010
 */

$SHOWDEBUG = true;
require_once("../../config.php");
require_once("../../functions.php");

GLOBAL $my_filedir;
$my_filedir = "../..";

define("MAXSTRINGLENGTH", "10000");

$gatekeeper = new Remote;
$gatekeeper->handleClientRequest();

class Remote
{	
	PRIVATE	$myVersion = "1.1";
	
	PRIVATE	$myClientVersion = false;
	PRIVATE	$mySafeId = false;
	PRIVATE	$myAffiliateUid = false;
	PRIVATE	$myBonuscode = false;
	PRIVATE	$myLanguage = false;
	PRIVATE	$myRedeem = false;
	PRIVATE	$myAction = false;
	PRIVATE $myBasketValue = 0;
	PRIVATE $myCampaignUid = false;
	PRIVATE $myIncentive = false;
	PRIVATE $myOrderUid = false;
	PRIVATE $myOrderInfo = false;
	PRIVATE $myTax = false;
	PRIVATE $myResponsetype = "xml";
	
	PRIVATE $debugAlwaysInvalid = false;
	PRIVATE $debugAlwaysValid = false;
	
	PRIVATE $responseArray = Array();
	
	PRIVATE	$defaultXmlString = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><affiliprint><version>##VERSION##</version><response>##XMLTAGS##</response></affiliprint>";
	PRIVATE	$defaultXmlTag = "<##TAGNAME##>##TAGCONTENT##</##TAGNAME##>";
	
	public function handleClientRequest()
	{
		$this->connectToDb();
		$this->parseClientRequestPost();
		
		// zalando-fix ...
		//todo: entfernen, wenn zalando weg ist
		if (trim($this->myBonuscode) != "TEST123")
		{
			$this->checkForDebugUid();
			$this->logDebugInfos();
			$this->performActions();
			$this->chooseResponseAndDoIt();
			$this->logDebugInfos();
		}
	}
	
	/**
	 * Choose the type of response according to
	 * the choosen responsetype. Default is XML
	 * @return unknown_type
	 */
	private function chooseResponseAndDoIt()
	{
		switch (strtolower($this->myResponsetype))
		{
			// GIF-Code schicken
			case "gif":
				$this->responseWithGif();
				break;
			
			// Mit XML antworten
			case "xml":
			default:
				$this->responseWithXml();
				break;
		}
	}
	
	private function addResponseTag($tagName, $tagContent, $urlEncode=true)
	{
		if ($urlEncode == true)
		{		
			$this->responseArray[$tagName] = urlencode($tagContent);		
		} else
		{
			$this->responseArray[$tagName] = $tagContent;		
		}
	}
	
	
	// unused so far
	private function setDefaultResponseTags()
	{
		addResponseTag("version", $this->myVersion);
	}
	
	private function composeXmlString($tagArray, $urlEncode=true)
	{
		foreach ($tagArray as $xmlKeys => $xmlValues)
		{
			if ($urlEncode == true)
			{
				$xmlValues = urlencode($xmlValues);
			}
			$xmlTag = str_replace("##TAGNAME##", $xmlKeys, $this->defaultXmlTag);
			$xmlTag = str_replace("##TAGCONTENT##", $xmlValues, $xmlTag);
			$xmlTags.=$xmlTag;
		}		
		return $xmlTags;
	}
	
	private function responseWithXml()
	{
		header("content-type: application/xml");
		$xmlResponse = str_replace("##VERSION##", $this->myVersion, $this->defaultXmlString);
		$xmlResponse = str_replace("##XMLTAGS##", $this->composeXmlString($this->responseArray, false), $xmlResponse);
		
		echo $xmlResponse;
	}
	
	/**
	 * Hier wird der Quelltext einens leeren, transparenten
	 * 1x1 GIFs zurückgegeben
	 * 
	 * @return none
	 */
	private function responseWithGif()
	{
		// Ablaufdatum der Cookies
		// seconds, minutes, hours, days
		$expires = 60*60*24*14;
		
		dmsg("creating cookie with order-uid:".$this->myOrderUid);
		header("Pragma: public");
		header("Content-type: image/gif");
		header("Content-length: 43");
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Set-Cookie: aporderid=".$this->myOrderUid."; expires=" . gmdate("D, d-M-Y H:i:s", time()+$expires) . " GMT; path=/");

		// Die Grafikdaten der GIF-Datei senden
		$fp = fopen("php://output","wb");
		fwrite($fp,"GIF89a\x01\x00\x01\x00\x80\x00\x00\xFF\xFF",15);
		fwrite($fp,"\xFF\x00\x00\x00\x21\xF9\x04\x01\x00\x00\x00\x00",12);
		fwrite($fp,"\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02",12);
		fwrite($fp,"\x44\x01\x00\x3B",4);
		fclose($fp);		
	}
	
	private function connectToDb()
	{
		global $DB_USER;
	    global $DB_PASSWORD;
	    global $DB_NAME;
		global $verbindung;
	    
		/* if ($_SERVER['REMOTE_ADDR'] = "127.0.0.1")
		{
		  	$DB_NAME = "testcopy";
		} */
		
		$verbindung = mysql_connect("localhost", $DB_USER, $DB_PASSWORD);
		mysql_select_db( $DB_NAME);
	  	if (!$verbindung) {
	    	echo "<html><body><center><br><b>FEHLER:</b><br><br>Verbindung zur Datenbank konnte nicht hergestellt werden!<br><br>Fehler: " . mysql_error() . "</center></body><html>";    
	    	exit;
	  	}		
		// Umstellen auf UTF-( für Verbindung zur Datenbank
		$query = "set names 'utf8';";
		$erg = mysql_query( $query);
	}
	
	private function actionListCampaigns()
	{
	    
		// damit nicht irgendjemand alles ausliest ...
		if (strlen($this->myAffiliateUid) > 8)
		{
			$query = "SELECT uid,campaignname,begin,end,incentive,campaigntext FROM campaign WHERE advertiser_uid='".$this->myAffiliateUid."'";
			// echo $query;
			$campaigns = safeQuery($query);	
			if ($campaigns != false)
			{
//				$xmlStringCampaigns = "";
				while($row = mysql_fetch_assoc($campaigns))
				{
					// $row['uid'] = $row(['uid']);
					// print_r($row);
					$xmlStringCampaigns.="<campaign>".$this->composeXmlString($row,false)."</campaign>";
				}
				// echo "<hr>a";
				// print_r($xmlStringCampaigns);
				// echo "b";
				$this->addResponseTag("campaigns", $xmlStringCampaigns, false);
				$this->setResponseValues(
					1,
					"Einlesen der Campagnen erfolgreich (eine oder mehrere Kampagnen vorhanden).",
					"", 
					"",
					"",
					"");
			} else 
			{
				// $this->addResponseTag("dberror", "JA");
				$this->setResponseValues(
					1,
					"Einlesen der Campagnen erfolgreich (jedoch keine Kampagnen vorhanden).",
					"", 
					"",
					"",
					"");
			};
		} else 
		{
			$this->setResponseValues(
				0,
				"Keine oder falsche Affiliate-UID angegeben.",
				"", 
				"",
				"",
				"");			
		}
	}
	
	private function performActions()
	{
		if (strlen($this->myAction)>1)
		{
			if ($this->myAction == "getcampaigns")
			{
				$this->actionListCampaigns();
			}
			if ($this->myAction == "addorderinfo")
			{
				$this->addOrderInfo();
			}
			
		} 
		else if ($this->myRedeem)
		{
			$this->performRedeem();	
			// wenn ein gif bzw. trackingpixel verwendet wird,
			// soll die orderuid auf 0 gesetzt werden, damit sie
			// den nächsten einkauf nicht behindert
			if (trim(strtolower($this->myResponsetype)) == "gif") 
			{
				$this->myOrderUid = 0;	
			}
		}
		else
		{
			$this->performValidation();
		}
	}

	private function logDebugInfos()
	{
		$host = $_SERVER['REMOTE_HOST'];
		if ($host == "") $host ="(UNRESOLVED)";
		dmsg("Logversion:2 - LOGSTART");
		dresult("Server AP-Version    	 :".$this->myVersion);
		dresult("Server Time             :".mktime());
		dresult("Client AP-Version    	 :".$this->myClientVersion);
		dresult("Client IP            	 :".$_SERVER['REMOTE_ADDR'].":".$_SERVER['REMOTE_PORT']);
		dresult("Client Info          	 :".$host.":".$_SERVER['REMOTE_PORT']."(".$_SERVER['REMOTE_PORT'].")");
		dresult("Client AffiliateUID    :".$this->myAffiliateUid);
		dresult("Client Language      	 :".$this->myLanguage);
		dresult("Client Redeem-Flag   	 :".$this->myRedeem);
		dresult("Client Bonuscode       :".$this->myBonuscode);
		dresult("Client Basketvalue     :".$this->myBasketValue);
		dresult("Client Responsetype    :".$this->myResponsetype);
		dresult("Client Tax    			:".$this->myTax);
		dresult("Client Campaign        :".$this->myCampaignUid);
		dresult("Client Incentive       :".$this->myIncentive);
		dresult("Client OrderUid        :".$this->myOrderUid);
		dresult("Client OrderInfo       :".$this->myOrderInfo);
//		dresult("Raw Request:".print_r($_REQUEST));
		foreach ($_REQUEST as $requestKey => $requestValue)
		{
			
		dresult("Client Request         :".$requestKey."=".$requestValue);
			$logClientRequest .= urlencode($requestKey)."=".urlencode($requestValue). "&";
			
		}
		dresult("Client Querystring     :".$_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI'])."?".$logClientRequest;	
		dresult("Client Request	     :".$logClientRequest);
		dresult("Server Reply           :".$this->composeXmlString($this->responseArray, false));
		dresult("LOGEND");
		
		
		writelog("remote", "request", $this->myVersion.":".$logClientRequest, "../../");
		writelog("remote", "answer", $this->myVersion.":".$this->composeXmlString($this->responseArray, false), "../../");
		
	}
	
	private function logRequest($action)
	{
		$info = $this->myBonuscode."//".$this->myVersion."//".$this->myAffiliateUid."//".$this->myLanguage;
		writelog("remote", $action, $info, "../../");
	}
	
	private function performValidation()
	{
		$validBonuscode = localValidateBonuscode($this->myBonuscode);
		
		dmsg("validating bonuscode:".$this->myBonuscode);
		if ($validBonuscode == false)
		{
			dresult("validating unsuccessful.");
		}	else
		{
			dresult("validating successful.");
			
		}
		
		if ($this->myCampaignUid == false)
		{
			dresult("no matching campaign found");
		}	else
		{
			dresult("matching campaign was:". $this->myCampaignUid);
		}
		
		if ((($validBonuscode && ($this->myCampaignUid != false)) || $this->debugAlwaysValid) && !$this->debugAlwaysInvalid)
		{
			if ($this->debugAlwaysValid)
			{
				$this->setResponseValues(
					1,
					"Gutscheincode ok (debug).",
					"", // 12345123451234512345",
					"5 EURO Wert",
					"ORDERID",
					"ORDERINFO");
			}	else
			{
				dmsg ("checking bonuscode-honor_pkey:". $this->myOrderUid==false?"FALSE":$this->myOrderUid);
				
				$doCheck = checkBonuscode($this->myCampaignUid, $this->myBonuscode, $this->myOrderUid, false, $this->myOrderInfo, $this->myBasketValue, $this->myAffiliateUid);
				dmsg ("got bonuscode-honor_pkey:". $doCheck[2]==false?"FALSE":$doCheck[2]);
				if ($doCheck[0]==true) 
				{
					$success = 1;	
					$this->logRequest("validate-successful");
				}	else
				{
					$success = 0;
					$this->logRequest("validate-failed");
				}
				$this->setResponseValues(
					$success,
					$doCheck[1],
					$this->myCampaignUid,
					$this->myIncentive,
					$doCheck[2],
					$doCheck[4]);
					
				$this->myOrderUid = $doCheck[2];
			}
			return true;
		}	else
		{
			$this->setResponseValues(
				0,
				"Gutscheincode nicht gueltig.",
				"",
				"",
				"",
				"");
			$this->logRequest("validate-failed");
			return false;
		}	
	}
	
	private function performRedeem()
	{
		global $honorwaitstatus;
		if (
		((localValidateBonuscode($this->myBonuscode) && ($this->myCampaignUid != false)) || $this->debugAlwaysValid)
		 && !$this->debugAlwaysInvalid)
		{
			if ($this->debugAlwaysValid)
			{
				$this->setResponseValues(
					1,
					"Einloesen erfolgreich (debug).",
					"", // 12345123451234512345",
					"5 EURO Wert",
					"ORDERID",
					"ORDERINFO");
			}	else
			{
				$doRedeem = checkBonuscode($this->myCampaignUid, $this->myBonuscode, $this->myOrderUid  , $honorwaitstatus, $this->myOrderInfo, $this->myBasketValue, $this->myAffiliateUid );
				if ($doRedeem[0]==true) 
				{
					$success = 1;	
				}	else
				{
					$success = 0;
				}
					
				$this->setResponseValues(
					$success,
					$doRedeem[1],
					$this->myCampaignUid,
					$this->myIncentive,
					$doRedeem[2],
					$doRedeem[4]);			}
			$this->logRequest("redeem-successful");
			return true;
		}	else
		{
			$this->setResponseValues(
				0,
				"Einloesen nicht erfolgreich.",
				"",
				"",
				"0",
				"");
			$this->logRequest("redeem-failed");
			return false;
		}	
	}
	
	private function setResponseValues($newStatus, $newMessage, $newCampaignUid, $newCoupontext, $newOrderUid, $newOrderInfo)
	{
		$this->addResponseTag("status", $newStatus);
		$this->addResponseTag("message", $newMessage, true);
	
		
		$this->addResponseTag("campaignuid", $newCampaignUid);
		$this->addResponseTag("coupontext", $newCoupontext);
		$this->addResponseTag("orderuid", $newOrderUid);
		$this->addResponseTag("orderinfo", $newOrderInfo, true);
	}
		
	private function parseClientRequestPost()
	{
		$this->myResponsetype = $this->avoidAttackingStrings($_REQUEST['response']);
		$this->myTax = $this->avoidAttackingStrings($_REQUEST['tax']);
		
		// the safeid - sid. new due to tracking pixel
		$this->mySafeId = $this->avoidAttackingStrings($_REQUEST['sid']);
		
		// the safeid overrides the affiliate-uid afid 
		if ((strlen($this->mySafeId) == 32))
		{
			// die sid ist gesetzt worden, also diese verwenden
			$this->myAffiliateUid = getUidFromSid($this->mySafeId);		
		}	else
		{
			// keine sid - also einfache id benutzen
			$this->myAffiliateUid = $this->avoidAttackingStrings($_REQUEST['uid']);
		}
		
		
		$this->myLanguage = $this->avoidAttackingStrings($_REQUEST['language']);
		$this->myRedeem = $this->isItOne($this->avoidAttackingStrings($_REQUEST['redeem']));
		$this->myBonuscode = $this->avoidAttackingStrings($_REQUEST['bonuscode']);
		$this->myAction = $this->avoidAttackingStrings($_REQUEST['action']);

		// should any tax be added to this one
		if ($this->myTax > 0)
		{
			// add a tax
			$taxes = 1+($this->myTax / 100);
		}	else
		{
			// add no tax
			$taxes = 1;	
		}
		// apply taxes directly
		
		$basket = $_REQUEST['basketvalue'];
		$firstPoint = strpos($basket, ".");
		if ($firstPoint > 0) 
		{
			$basket = substr($basket, 0, $firstPoint + 2);
		}
		
		$this->myBasketValue = floatval(str_replace(",", ".", $basket)) * $taxes;
		
		// wenn eine order-uid übergeben wurde, soll diese verwendet werden
		// ansonsten wird versucht, die orderuid aus dem cookie zu lesen.
		// sollte nichts zu finden sein, MUSS die orderid false bleiben
		// damit nachher eine generiert wird - in checkbonuscode
		
		$tempOrderUid = $this->avoidAttackingStrings($_REQUEST['orderuid']);
		if ($tempOrderUid < 1) 
		{
			$idFromCookie = $this->avoidAttackingStrings($_COOKIE["aporderid"]);
			if ($idFromCookie > 0) 
			{
				dmsg("detected existing cookie with orderuid:".$idFromCookie);
				$this->myOrderUid = $idFromCookie;	
			}	else
			{
				dmsg("cookie present but no content available");
			}
		}	else
		{
			$this->myOrderUid = $tempOrderUid;
		}		
		
		$this->myOrderInfo = $_REQUEST['orderinfo'];
		if ($this->myBonuscode != false)
			$this->myCampaignUid = $this->getCampaignForCode($this->myBonuscode);
		if ($this->myCampaignUid!=false) 
			$this->myIncentive = $this->getIncentiveForCampaign($this->myCampaignUid);
			
		// TODO:	wieder rausnehmen falls posterXXL wieder weg ist
		
		// Workaround für PosterXXL:
		if ($this->myCampaignUid == "jhgligl")
		{
			// posterxxl gibt 2 EUR Provision plus 10 Prozent des Warenkorbwertes.
			// abgesprochen mit Sönke: Hier einfach 20 EUR auf den Warenkorb addieren,
			// dadurch das richtige Ergebnis bei der Provision
			$this->myBasketValue = $this->myBasketValue + 20;
		}
			

			
	}

	private function getCampaignForCode ( $bonuscode) {
		$found = false;
		if ( strlen( $bonuscode) > 10) {
			$bonuscode = substr( $bonuscode,0 ,4);
		}
		
		// Existiert der Bonuscode?
	    $query = "SELECT campaign_uid FROM bs_flyer WHERE bonuscode='" . $bonuscode . "'";
	  	$campaigns = safeQuery( $query);
	    if ( $campaigns!=false) {
	  		$row = mysql_fetch_assoc($campaigns);
	  		$found = $row['campaign_uid'];
	  	} else {
		  	$query = "SELECT campaign_uid FROM p_flyer WHERE bonuscode='" . $bonuscode . "'";
	  		$campaigns = safeQuery( $query);
		  	if ( $campaigns!=false) {
		  		$row = mysql_fetch_assoc( $campaigns);
		  		$found = $row['campaign_uid'];
		  	}
	  	}
	  	return $found;
	}
	
	private function getIncentiveForCampaign($campaignUid)
	{
		$query = "SELECT incentive FROM campaign WHERE uid='" . $campaignUid . "'";
	  	$campaigns = safeQuery($query);
		if ($campaigns!=false) 
	  	{
		  	$row = mysql_fetch_assoc($campaigns);
		  	$found = $row['incentive'];	  
		  	return $found;		
	  	} else 
	  	{
	  		return false;	
	  	}
	}
	  	
	private function checkForDebugUid()
	{
		// For testing purposes you can leave the ID "TestOnlyEnterYourCodeHere" here.
		// The ID "AlwaysValid" will always return VALID	
		// The ID "AlwaysWrong" will always return INVALID	
		
		if ($this->myAffiliateUid == "AlwaysValid")
		{
			$this->debugAlwaysValid = true;
		}	
		else
		{
			$this->debugAlwaysValid = false;
		}	
		
		
		if ($this->myAffiliateUid == "AlwaysWrong")
		{
			$this->debugAlwaysInvalid = true;
		}
		else
		{
			$this->debugAlwaysInvalid = false;
		}
	}
	
	private function avoidAttackingStrings($myString)
	{
		return $this->stripNonAlphaNumeric($this->preventOverflow($myString));
	}
	
	private function isItOne($myString)
	{
		if (trim($myString)=="1")
		{
			return true;
		}	
		else
		{
			return false;
		}
	}
	
	private function preventOverflow($myString)
	{
		if (strlen($myString)>MAXSTRINGLENGTH) 
		{
			return "";
		} 
		else
		{
			return $myString;
		}
	}
	
	private function stripNonAlphaNumeric($string)
	{
		return preg_replace("/[^a-zA-Z0-9]/", "", $string);
	}
	
}