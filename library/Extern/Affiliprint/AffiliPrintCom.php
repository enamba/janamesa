<?php
/**
 * AffiliPrintCom
 * 
 * Communications Class of the AffiliPRINT GmbH
 * 
 * ---------------------------------------------------------------------------
 *
 * Klasse zur Kommunikation mit der Affiliprint GmbH
 * 
 * First version created on 04.05.2010
 * Translated 			 on 30.11.2010
 * Current version		 on 17.01.2011
 * 
 * @see example-v1-1.php for a simple example on how to use this class
 * @package AffiliPRINT
 * @author Nils Peters, n.peters@affiliprint.de
 * @version remote: 1.1 affiliprintcom: 1.2
 * @copyright AffiliPRINT Projekt - 2010
 * @example see example-vX-X-simple.php 
 */

// Using the configuration file you can change your AffiliateUID
// And the way the script communicates (e.g. if you are using a proxy)
require_once("AffiliPrintConfig.php");

// LGPL XML Parser Library by Adam A. Flynn used for php4 compatibility.
require_once("XMLParser.php");

class AffiliPrintCom
{	
	PRIVATE $myConfig = NULL;
	
	// Except if you are interested in the functionality there is
	// no need to read this php script any further
	
	// Version of the protocol used
	PRIVATE $MYVERSION = "1.1";
	
	PRIVATE $myStatus = "";
	PRIVATE $myMessage = "";
	PRIVATE $myOrderUid = "";
	PRIVATE $myOrderInfo = "";
	PRIVATE $myCoupontext = "";
	PRIVATE $myCampaignUid = "";
	PRIVATE $myCampaigns = "";
	PRIVATE $myResponse = "";
	PRIVATE $myXMLResponse = "";
	
	PRIVATE $SENDENCODED = array("basketvalue", "orderinfo");
	
	/**
 	* Check if Bonuscode has the right format
 	* Expected format: APXXXXXXCC where
 	* XXXXXX is a 6 digit number where each digit is between 2 and 9 and
 	* CC is a two digit checksum
 	* 
 	* or (if it is an unique-code
 	* WWWW-XXXX-XXCC
 	* 
 	* @example: localValidateBonuscode("AP223456XP") returns true
 	* @param bonuscode	bonuscode to validate
 	* @return boolean 	determines if validation was successful
 	*/
	public function localValidateBonuscode($bonuscode) 
	{
		return true;		
		$bonuscode = strtoupper($bonuscode);
		if (strlen($bonuscode) >= 12) return $this->checkApBlock($bonuscode);
		if (!preg_match("/^AP[2-9]{6}[a-hj-np-zA-HJ-NP-Z]{2}$/", $bonuscode)) 
		{
			return false;
		}
		// check if Bonuscode has correct Checksum
		else 
		{
			return	(substr(str_ireplace(
					array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F"), 
					array("G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X"),
					md5(substr($bonuscode, 2, 6))),-2,2) == substr($bonuscode, -2, 2));
		}
	}
		
	/**
 	* Let Affiliprint check if the bonuscode is valid
 	* 
 	* @example: remoteValidateBonuscode("AP223456XP") 
 	* @param bonuscode	bonuscode to validate
 	* @param orderUid  optional - if you want to refer to an already remotely tested order
 	* @param orderInfo  optional - custom data (a max. 40 chars string) you can add to this order 
 	* @return boolean 	determines if validation was successful
 	*/	
	public function remoteValidateBonuscode($bonuscode, $basketValue="0", $orderUid = "0", $orderInfo = "") 
	{
		$bonuscode = $this->stripNonAlphaNumeric($bonuscode);
		if ($this->localValidateBonuscode($bonuscode))
		{
			return $this->runRemoteQuery(array(
					'uid'=>$this->myConfig->AFFILIATEUID,
					'language'=>$this->myConfig->LANGUAGE,
					'bonuscode'=>$bonuscode,
					'basketvalue'=>$basketValue,
					'orderuid'=>$orderUid,
					'orderinfo'=>$orderInfo
			));			
		}
	}

	/**
 	* Let Affiliprint redeem a bonuscode
 	* 
 	* @example: remoteRedeemBonuscode("AP223456XP") 
 	* @param bonuscode	bonuscode to redeem
 	* @param basketValue  value of the shopping basket - needed for percentual revenue.
 	* 		 May contain ONE decimal point or comma. 1.000,00 is wrong. 1000,00 is right.
 	* @param orderUid  optional - if you want to refer to an already remotely tested order
 	* @param orderInfo  optional - custom data (a max. 40 chars string) you can add to this order 
 	* @return boolean 	determines if redeem was successful
 	*/	
	public function remoteRedeemBonuscode($bonuscode, $basketValue="0", $orderUid = "0", $orderInfo = "") 
	{
		$bonuscode = $this->stripNonAlphaNumeric($bonuscode);
		if ($this->localValidateBonuscode($bonuscode))
		{
			return $this->runRemoteQuery(array(
					'uid'=>$this->myConfig->AFFILIATEUID,
					'language'=>$this->myConfig->LANGUAGE,
					'bonuscode'=>$bonuscode,
					'basketvalue'=>$basketValue,
					'orderuid'=>$orderUid,
					'orderinfo'=>$orderInfo,
					'redeem'=>"1"
				));			
		}
	}
	
	/**
 	* Return an Array of the running campaigns
 	* 
 	* @example: remoteValidateBonuscode("AP223456XP") 
 	* @return boolean 	determines if validation was successful
 	*/	
	public function remoteRunningCampaigns() 
	{
		return $this->runRemoteQuery(array(
				'uid'=>$this->myConfig->AFFILIATEUID,
				'language'=>$this->myConfig->LANGUAGE,
				'action'=>"getcampaigns"
			));			
	}

	/**
	 * Convert an AffiliPRINT checksum to a HEX-checksum
	 * 
	 * @param $checksum checksum in AP-checksum-format
	 * @return checksum in HEX
	 */ 
	function convertApcsToCs($apchecksum)
	{
		$toBeConverted  = array("G","H","J","K","L","M","N","P","Q","R","S","T","V","W","X","Z");
		$whatsItGonnaBe = array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F");
		return str_ireplace($toBeConverted, $whatsItGonnaBe, $apchecksum);
	}
  	
	/**
	 * Pre-check an AffiliPRINT unique voucher code
	 * Format:
	 * 
	 * WWWW-XXXX-XXYY
	 * 
	 * @param $apBlock Format: WWWW-XXXX-XXYY AP-Code
	 * @return unknown_type 
	 */
	function checkApBlock ($apBlock)
	{
		$errBase =	array ( "0","O","1","I","A","E","O","U","-","_","*","#");
	 	$repBase =	array ( "!","!","!","!","!","!","!","!","" ,"" ,"" ,"" );	
		
	 	$apBlock = trim(strtolower($apBlock));
	 	$apBlock = str_ireplace($errBase, $repBase, $apBlock);
		
	 	if (strstr($apBlock, "!") != false) return false;
	
	 	if (strlen($apBlock) != 12) return false;

	 	$checkMe = substr( md5( substr(strtoupper($apBlock), 0, 10) ),-2, 2);
	 	$foundChecksum = $this->convertApcsToCs(substr( $apBlock, -2, 2 ));
	 	return (strtoupper($checkMe) == $foundChecksum);
	}
	 	
  	public function __construct()
	{
		$this->myConfig = new AffiliPrintConfig;		
	}

	
	private function stripNonAlphaNumeric($string)
	{
		return preg_replace("/[^a-zA-Z0-9]/", "", $string);
	}
	
	private function buildRequest($data)
	{
		foreach ($data as $key => $value)
		{
			if (in_array($key, $this->SENDENCODED)) 
			{
				$request[]=$key."=".urlencode($value);	
			} else
			{
				$request[]=$key."=".$this->stripNonAlphaNumeric($value);	
			}
		}
		return implode("&", $request);
	}
	
	public function runRemoteQuery($parameters)
	{
		$this->setValues(0, "","","");
		
		$fetchResource = $this->doHttpRequest(
			$this->myConfig->COMURL, 
			$this->myConfig->COMPORT, 
			$this->myConfig->COMPATH, 
			"POST", 
			$this->buildRequest($parameters));

		if ($fetchResource == false)
		{
			$this->setValues(500, "ERROR: HTTP request error","","");
			return false;
		}		
		
		// check against failure and prevent overflows
		if ( (strlen($fetchResource) < 3) || (strlen($fetchResource) > 10000) ) 
		{
			$this->setValues(500, "ERROR: Invalid responselength","","");
			return false;
		}		
		
		try 
		{
			$parser = new XMLParser($fetchResource);
			$parser->Parse();
			$xml = $parser->document;
		}
		catch (Exception $xmlException) 
		{
			$this->setValues(500, "ERROR: Malformed response","","");
			return false;
		}
		
		// check if the protocol used is out of date
		$protocolVersion = $xml->version[0]->tagData;
		if (!$this->checkCompatibility($protocolVersion)) 
		{
			return false;
		}
			
		// seems like at least the response was fine
		$this->setValues(
			(string)$xml->response[0]->status[0]->tagData,
			(string)$xml->response[0]->message[0]->tagData,
			(string)$xml->response[0]->campaignuid[0]->tagData,
			(string)$xml->response[0]->coupontext[0]->tagData,
			(string)$xml->response[0]->campaigns[0]->tagData,
			$xml,
			(string)$xml->response[0]->orderuid[0]->tagData,
			(string)$xml->response[0]->orderinfo[0]->tagData
			);
	
		return ($this->getStatus() == 1);
	}

	public function checkCompatibility($toVersion)
	{
		if (floor($toVersion) != floor($this->MYVERSION))
		{
			$this->setValues(505, "ERROR: Invalid protocol (challenge: V".$this->MYVERSION.", response: V".$protocolVersion.")", "", "", null);
			return false;
		} 	
		else
		{
			return true;
		}
	}
		
	
	private function doHttpRequest($srv, $port, $path, $method, $data="", $HTTPS=-1) 
	{
		if ($HTTPS == -1) $HTTPS = $this->myConfig->COMUSESSL;
		
		switch(strtoupper($method)) {
			case "POST":
				$req = "POST ".$path." HTTP/1.1\r\n";
				break;
	
			case "GET":
				$req = "GET ".$path." HTTP/1.1\r\n";
				break;
	
			default:
				return false;
		}
		if ($srv=="")
		{
			return false;
		}
		$req .= "Host: ".$srv."\r\n";
		if ($method=="POST") 
		{
			$req .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$req .= "Content-Length: ".strlen($data)."\r\n";
		}
		$req .= "Connection: Close\r\n\r\n";
		if ($method=="POST") 
		{
			$req .= "".$data;
		}
		if ($HTTPS) 
		{
			$prefix = "ssl://";
		}
		$connection = @fsockopen($prefix.$srv, $port);
		if ($connection!==false) 
		{
			fputs($connection, $req);
			$result = "";
			while(!feof($connection)) 
			{
				$result .= fgets($connection);
			}
			$this->myResponse = $result;
			$xmlStart = "<?xml";
			$contentStart = strpos(strtolower($result), $xmlStart);
			$contentEnd = strrpos(strtolower($result), ">");
			if ($contentStart > 0)
			{	
				$result = ltrim(substr($result, $contentStart, $contentEnd-$contentStart+1));
			}
			else 
			{
				return false;
			}
			return $result;
		} 
		else 
		{
			return false;
		}
	}
	
	public function validateBonuscode($bonuscode) 
	{
		if (localValidateBonuscode($bonuscode))
		{
			return remoteValidateBonuscode($bonuscode);
		}
		else 
		{
			return false;
		}
	}
	
	private function setValues($newStatus, $newMessage, $newCampaignUid = "", $newCoupontext = "", $newCampaigns = false, $newXMLResponse = null, $newOrderUid = "", $newOrderInfo = "")  
	{
		$this->myStatus = $this->stripNonAlphaNumeric(urldecode($newStatus));
		$this->myMessage = urldecode($newMessage);
		$this->myCoupontext = urldecode($newCoupontext);
		$this->myCampaignUid = $this->stripNonAlphaNumeric(urldecode($newCampaignUid));
		$this->myCampaigns = $newCampaigns;
		$this->myXMLResponse = $newXMLResponse;
		$this->myOrderUid = $newOrderUid;
		$this->myOrderInfo = urldecode($newOrderInfo);
	}
	

	public function setAFFILIATEUID($AFFILIATEUID)
	{
		$this->myConfig->AFFILIATEUID = $AFFILIATEUID;
		return true;
	}
	
	public function setDebugConnection($COMURL, $COMPORT, $COMPATH, $COMUSESSL) 
	{
		$this->myConfig->COMURL = $COMURL;
		$this->myConfig->COMPORT = $COMPORT;
		$this->myConfig->COMPATH = $COMPATH;
		$this->myConfig->COMUSESSL = $COMUSESSL;
		return true;
	}
	
	public function getConnection() 
	{
		if ($this->myConfig->COMUSESSL) $prefix = "HTTPS://";
		else  $prefix = "HTTP://";
		return $prefix.$this->myConfig->COMURL.":".$this->myConfig->COMPORT.$this->myConfig->COMPATH;
	}
	
	public function getCoupontext() {
		return $this->myCoupontext;
	}
	
	public function getMessage() {
		return $this->myMessage;
	}
	
	public function getResponse() {
		return $this->myResponse;
	}
	
	public function getStatus() {
		return $this->myStatus;
	}
	
	public function getCampaignUid() {
		return $this->myCampaignUid;
	}
	
	public function getCampaigns() {
		return $this->myCampaigns;
	}
	
	public function getXMLResponse() {
		return $this->myXMLResponse;
	}

	public function getOrderUid() {
		return $this->myOrderUid;
	}

	public function getOrderInfo() {
		return $this->myOrderInfo;
	}
		
	public function getVersion() {
		return $this->MYVERSION;
	}
	
}	
?>
