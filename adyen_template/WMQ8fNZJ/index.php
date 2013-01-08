<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title>Etapa 1: Escolha a forma de pagamento</title>

	
	<link rel="stylesheet" type="text/css" href="/WMQ8fNZJ/css/reset.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/WMQ8fNZJ/css/screen.css" />
	<link rel="stylesheet" media="print"  type="text/css" href="/WMQ8fNZJ/css/print.css" />
	<script type="text/javascript" src="/WMQ8fNZJ/default.js"></script>
	<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="/sf/WMQ8fNZJ/css/screen_ie6.css" />
	<![endif]-->
</head>
<body>
 <script type="text/javascript">
	//<![CDATA[	
	var clientIPAddress="177.139.195.86";
	
	var config = new Array();
	config["pmmanimation"] = 2;
	
	//]]>
  </script>
    
<?php
include('inc/cheader.txt');
?>

        <form id="pageform" action="details.shtml" method="post"  autocomplete="off" onsubmit="return formValidate(this  ,'default' );">
    <div id="content">
        <?php
include('inc/pmheader.txt');
?>

        <div class="paddiv1"></div>
			<script type="text/javascript">
//<![CDATA[
/* Form validation */
var requiredFields = new Array();
var fieldLinks = new Array();
var errorMessages = new Array();
var errorAreas = new Array();
var validationFunctions = new Array();
var details = new Array();

errorMessages["default"] = new Array();
requiredFields["default"] = new Array();



var maySubmitOnlyOnce=true;

requiredFields["default"].push("brandCode");
errorMessages["default"]["brandCode"] = "Por favor insira seus dados de pagamento";
errorMessages["default"]["submitonce"] = "Seu pedido está sendo processado, por favor aguarde...";

var locked = false;
var _valFunc = new function() {};

var displayAmountExtras = new Object();

function show(detail, actionURL, group, brandCode) {

	if(config["pmmanimation"] != 1) {
		if(locked) { return false; }
		locked = true;
		setTimeout('locked=false',1000);
	}
	
	if(group == "card") {
		brandCode="brandCodeUndef";
	}
	
	document.forms["pageform"].action=actionURL;
	document.forms["pageform"].onsubmit=null;

	if(document.forms["pageform"].addEventListener) {
	  document.forms["pageform"].removeEventListener('submit',_valFunc ,false);
	  document.forms["pageform"].addEventListener('submit',_valFunc = function(e) { 
	    result = formValidate(document.forms["pageform"] ,group); 
		if(result == false) {
		  e=e||event;
		  e.preventDefault? e.preventDefault() : e.returnValue = false; 
		} 
	  },false);
	} else {
	  document.forms["pageform"].detachEvent('onsubmit',_valFunc);
	  document.forms["pageform"].attachEvent('onsubmit',_valFunc = function(e) { 
		result = formValidate(document.forms["pageform"] ,group); 
		if(result == false) {
		  e=e||event;
		  e.preventDefault? e.preventDefault() : e.returnValue = false; 
		}
	  });
	}

	document.forms["pageform"]["brandCode"].value=brandCode;
	document.forms["pageform"]["displayGroup"].value=group;
	document.getElementById('extraCostAmount').innerHTML = displayAmountExtras[group];
	
	if(detail != "") {
    	detail.slideit();
    	for (i = 0; i < details.length; i++) { 	
    		if (details[i].divId != detail.divId) {
    			details[i].slideup();
    		}
    	}
	}
	
	// possible selection handler hook
	var selectHandler = "select"+group+"Handler";
	if (eval("typeof " + selectHandler + " === 'function'")) {
		window[selectHandler](detail, actionURL, group, brandCode);
	}
	
	return false;
}


	addOnLoad(preventEnterSubmit);



//]]>
</script>
<script type="text/javascript" src="/hpp/js/animatedcollapse.js"></script>
<script type="text/javascript" src="/hpp/js/cc.js"></script>
  <input type="hidden" id="displayGroup" name="displayGroup" value="" />
<h2 id="stageheader">Etapa 1: Por favor, selecione o método de pagamento</h2>

<div id="orderDataWrapper">
 <div id="orderDataHeader">Detalhes de pedido</div>
 <div id="orderData">
	<div class="cart"><div id=yd-rest-name>Restaurante Jánamesa NMB</div></div>
 </div>
</div>
<div id="displayAmount">
 Quantia total de pagamento BRL 79.80 <span id="extraCostAmount"></span>
</div>


<ul id="paymentMethods">
		
		
			
<li style="list-style-type: none;">
	 <input 	type="submit" name="brandName" value="Cartão de crédito	" class="imgB pmB pmBcard"
		
			onclick="return show(collapsecard, 'completeCard.shtml', 'card', 'brandCodeUndef');"

	/>
	    <span id="pmmextracosts-card" class="pmmextracosts">
    	    </span>

	<span id="pmcarddescription" class="pmmdescription"></span>
		<div id="pmmdetails-card" class="pmmdetails">
		
		<script type="text/javascript">
			var collapsecard = new animatedcollapse("pmmdetails-card", 1000, false, false, config["pmmanimation"]==1?false:true);
			details.push(collapsecard);
			
							displayAmountExtras['card'] = "";
						
									
											addOnLoad(function () {
					setTimeout("show(collapsecard, 'completeCard.shtml', 'card', 'brandCodeUndef')",100);
				});
										
			if (notNull(document.getElementById('pmmform-card'))) {
				document.getElementById('pmmform-card').setAttribute("autocomplete","off"); 
			}
		</script>
		
									<!-- useNewCardId = true, groupName = card -->

<script type="text/javascript">
//<![CDATA[
/* Form validation */
requiredFields["card"] = new Array();
requiredFields["card"].push( "card.cardHolderName" );
requiredFields["card"].push( "card.cardNumber" );
requiredFields["card"].push( "card.expiryMonth" );
requiredFields["card"].push( "card.expiryYear" );
errorMessages["card"] = new Array();
errorMessages["card"][ "card.cardHolderName" ] = "Preencha o nome do dono do cartão";
errorMessages["card"][ "card.cardNumber" ] = "Número de cartão inválido ou não preenchido";
errorMessages["card"][ "card.expiryMonth" ] = "Preencha o mês de validade";
errorMessages["card"][ "card.expiryYear" ] = "Preencha o ano de validade";
errorMessages["card"][ "card.cvcCode" ] = "CVC/CVV/CID não está preenchido";
errorMessages["card"]["generic"] = "Por favor, insira os dados de seu cartão";


 										



	var card_cvcinfo = new Array();
	card_cvcinfo["mc"] =	"<h3>O que \u00E9 CVC?<\/h3>" + 
	                  	"<p><img style=\"margin-right: 5px\" class=\"fl\" src=\"/hpp/img/CVC_mini.jpg\" alt=\"CVC location\" />" +
						"O Card Validation Code (CVC) \u00E9 um <i>additional<\/i> " + 
						"C\u00F3digo de seguran\u00E7a de tr\u00EAs d\u00EDgitos que est\u00E1 impresso (e n\u00E3o gravado) atr\u00E1s " +
						"do seu cart\u00E3o.<\/p>" +
						"<p>O CVC \u00E9 uma medida adicional de seguran\u00E7a para garantir que voc\u00EA \u00E9 o possuidor do cart\u00E3o.<\/p><br style=\"clear: both\" />";
	card_cvcinfo["visa"] =	"<h3>O que \u00E9 CVV?<\/h3>" + 
	                  	"<p><img style=\"margin-right: 5px\" class=\"fl\" src=\"/hpp/img/CVV_mini.jpg\" alt=\"CVV location\" />" +
						"O Card Verification Value (CVV) \u00E9 um <i>additional<\/i> " + 
						"C\u00F3digo de seguran\u00E7a de tr\u00EAs d\u00EDgitos que est\u00E1 impresso (e n\u00E3o gravado) atr\u00E1s " +
						"do seu cart\u00E3o.<\/p>" +
						"<p>O CVV \u00E9 uma medida adicional de seguran\u00E7a para garantir que voc\u00EA \u00E9 o possuidor do cart\u00E3o.<\/p><br style=\"clear: both\" />";
	card_cvcinfo["amex"] =	"<h3>O que \u00E9 CID?<\/h3>" + 
	                  	"<p><img style=\"margin-right: 5px\" class=\"fl\" src=\"/hpp/img/CID_mini.jpg\" alt=\"CID location\" />" +
						"O Card IDentification (CID) \u00E9 um <i>additional<\/i> " + 
						"C\u00F3digo de seguran\u00E7a de quatro d\u00EDgitos que est\u00E1 impresso (e n\u00E3o gravado) na frente " +
						"do seu cart\u00E3o.<\/p>" +
						"<p>O CID \u00E9 uma medida adicional de seguran\u00E7a para garantir que voc\u00EA \u00E9 o possuidor do cart\u00E3o.<\/p><br style=\"clear: both\" />";
	card_cvcinfo["card"] =	"<h3>O que \u00E9 CVC\/CVV\/CID?<\/h3>" + 
	                  	"<p>O Card Security Code (CVC\/CVV\/CID) \u00E9 um <i>additional<\/i> " + 
						"C\u00F3digo de seguran\u00E7a de tr\u00EAs ou quatro d\u00EDgitos que est\u00E1 impresso (e n\u00E3o gravado) na frente ou atr\u00E1s " +
						"do seu cart\u00E3o.<\/p>" +
						"<p>O CVC\/CVV\/CID \u00E9 uma medida adicional de seguran\u00E7a para garantir que voc\u00EA \u00E9 o possuidor do cart\u00E3o.<\/p><br style=\"clear: both\" />";	

	// pass these arround, instead of just using 
	var card_types = new Array();
	var card_logos = new Array();
	var card_displayAmountExtras = new Array();
	var card_extras = new Array();
	var card_previousCardNumber ="";
	var card_subVariantExtras = new Object();
	var card_subVariantExtrasPhrase = new Object();
	var card_extraCostDivId = 'pmmextracosts-card';
	var card_originalExtraCostPhrase = document.getElementById(card_extraCostDivId).innerHTML;
	
	
			card_types.push("diners");
		card_logos.push("/img/pm/diners");
					card_displayAmountExtras.push("");
			card_extras.push("");
		
											card_subVariantExtras['diners'] = "";
				card_subVariantExtrasPhrase['diners'] = "";
								card_types.push("amex");
		card_logos.push("/img/pm/amex");
					card_displayAmountExtras.push("");
			card_extras.push("");
		
											card_subVariantExtras['amex'] = "";
				card_subVariantExtrasPhrase['amex'] = "";
								card_types.push("mc");
		card_logos.push("/img/pm/mc");
					card_displayAmountExtras.push("");
			card_extras.push("");
		
											card_subVariantExtras['mcatm'] = "";
				card_subVariantExtrasPhrase['mcatm'] = "";
												card_subVariantExtras['mccredit'] = "";
				card_subVariantExtrasPhrase['mccredit'] = "";
												card_subVariantExtras['maestro'] = "";
				card_subVariantExtrasPhrase['maestro'] = "";
												card_subVariantExtras['mc'] = "";
				card_subVariantExtrasPhrase['mc'] = "";
												card_subVariantExtras['bijcard'] = "";
				card_subVariantExtrasPhrase['bijcard'] = "";
												card_subVariantExtras['cirrus'] = "";
				card_subVariantExtrasPhrase['cirrus'] = "";
												card_subVariantExtras['mcpro'] = "";
				card_subVariantExtrasPhrase['mcpro'] = "";
												card_subVariantExtras['bcmc'] = "";
				card_subVariantExtrasPhrase['bcmc'] = "";
												card_subVariantExtras['mcdebit'] = "";
				card_subVariantExtrasPhrase['mcdebit'] = "";
												card_subVariantExtras['mccommercialcredit'] = "";
				card_subVariantExtrasPhrase['mccommercialcredit'] = "";
												card_subVariantExtras['mccorporate'] = "";
				card_subVariantExtrasPhrase['mccorporate'] = "";
						
		
	var baseURL = "/hpp/";
	if(baseURL.indexOf(";jsession") != -1) {
		baseURL = baseURL.substr(0,baseURL.indexOf(";jsession"));
	}
	
	function card_validateCcNumber(e, groupName, group_types, group_logos, group_subVariantExtras, group_subVariantExtrasPhrase, dontHideErrorFrame) {
		cardNumber = (document.getElementById( 'card.cardNumber' ).value);
		
		// empty card field - reset all
		if (cardNumber.length == 0) {
			card_previousCardNumber = cardNumber;
			card_resetExtraCost(groupName);
			card_setCardBrand(null, false, groupName, group_types, group_logos);
			return;
		}

		// When editing the card (but not adding digits at the end), don't display validation error(s) and don't reformat the number
		var l=0;
		while(l < card_previousCardNumber.length && l < cardNumber.length) {
			if(cardNumber[l] != card_previousCardNumber[l]) {
				card_previousCardNumber = cardNumber;
				return;
			} 
			l++;
		}
	
		// remove all whitespace
		reg = /\s+/g;
		cardNumber = cardNumber.replace(reg,'');
		
		nrOfDigits = cardNumber.length;
		if(nrOfDigits > 19){
			card_ccNumberPresentation(false, groupName);
			return;
		}
		
		card_ccNumberPresentation(true, groupName, dontHideErrorFrame);
		
		baseCard = getBaseCard(cardNumber, group_types);
		if(baseCard != null) {
		    card_setCardBrand(baseCard, true, groupName, group_types, group_logos);
		} else if(nrOfDigits > 4) {
		    card_setCardBrand(null, true, groupName, group_types, group_logos);
			card_ccNumberPresentation(false,groupName);
		} else {
			card_setCardBrand(null, false, groupName, group_types, group_logos);
		}

		if (nrOfDigits < 6) {
			card_setExtraCost(baseCard, null, groupName, group_types, group_subVariantExtras, group_subVariantExtrasPhrase);
		} else if (nrOfDigits == 6 || nrOfDigits == 9 || nrOfDigits == 12 || nrOfDigits == 16){
			_.X("/hpp/binLookup.shtml",function(d,r){
				if(r.status != 200 || d.indexOf('"result"') == -1) return false;
	            var response=eval("("+d+")");
					
				if(typeof(response.result)=='undefined') return false;
		
				if (response.result == 0) {
					lookedUpCardType = response.cardType;
				} else {
					lookedUpCardType = null;	
				}
				card_setExtraCost(baseCard, lookedUpCardType, groupName, group_types, group_subVariantExtras, group_subVariantExtrasPhrase);
		
				return true;
	        }, 'bin='+cardNumber+'&'+_.Q(_.G("pageform")));
		}
		
		//show value with white space after four numbers
		result = cardNumber.replace(/(\d{4})/g, '$1 ');
		result = result.replace(/\s+$/, ''); //remove trailing spaces
		
		card_previousCardNumber = result;
		document.getElementById( 'card.cardNumber' ).value = result;
	}
	
	function card_setExtraCost(selectedCard, lookedUpCard, groupName, group_types, group_subVariantExtras, group_subVariantExtrasPhrase) {
		var extraCostDisplayed = false;
		if (lookedUpCard != null && group_subVariantExtras[lookedUpCard] != null) {
			document.getElementById('extraCostAmount').innerHTML = group_subVariantExtras[lookedUpCard];
			displayAmountExtras[groupName] = group_subVariantExtras[lookedUpCard];
			document.getElementById(card_extraCostDivId).innerHTML = group_subVariantExtrasPhrase[lookedUpCard];
			extraCostDisplayed = true;
		} else {
			        	for(var i = 0; i < group_types.length; ++i) {
    			if(selectedCard != null && group_types[i] == selectedCard.cardtype && group_subVariantExtras[selectedCard.cardtype] != null) {
					document.getElementById('extraCostAmount').innerHTML = group_subVariantExtras[selectedCard.cardtype];
					displayAmountExtras[groupName] = group_subVariantExtras[selectedCard.cardtype];
					document.getElementById(card_extraCostDivId).innerHTML = group_subVariantExtrasPhrase[selectedCard.cardtype];
					extraCostDisplayed = true;
    			}
        	}
		}
		
		if (!extraCostDisplayed) {
			card_resetExtraCost(groupName);
		}
	}
	
	function card_resetExtraCost(groupName) {
		// groupName is not used anymore
		displayAmountExtras['card'] = "";
		document.getElementById('extraCostAmount').innerHTML = "";
		document.getElementById(card_extraCostDivId).innerHTML = card_originalExtraCostPhrase;
	}
	
	function card_setCardBrand(selectedCard, greyInactive, groupName, group_types, group_logos) {

		for(var i = 0; i < group_types.length; ++i) {
		    var imageId =  'card.cclogo'  + i;
			if(selectedCard != null && group_types[i] == selectedCard.cardtype) {
				document.getElementById(imageId).src=baseURL + group_logos[i] + "_small.png";
			} else {
			    if(greyInactive) {
				  document.getElementById(imageId).src=baseURL + group_logos[i] + "_small_grey.png";
				} else {
				  document.getElementById(imageId).src=baseURL + group_logos[i] + "_small.png";
				}
			}
			document.getElementById(imageId).style.display="inline";
		}
		
		card_setCvcElement(selectedCard != null ? selectedCard.cardtype : null, groupName);
	}
	
	function card_setCvcElement(selectedCardType, groupName) {
		// for tokenising, the cvc element is not displayed, so check before using it
		var cvcCodeElem = document.getElementById( 'card.cvcCode' );
		if(selectedCardType != null && cvcCodeElem != null) {
		  if(selectedCardType == "amex") {
		 	cvcCodeElem.maxLength = 4;
			document.getElementById( 'card.cvcName' ).innerHTML = "CID";
			document.getElementById( 'card.cvcWhatIs' ).innerHTML = "O que é CID?";
			document.getElementById( 'card.cvcFrame' ).innerHTML = card_cvcinfo["amex"];
		  } else if (selectedCardType == "visa" || selectedCardType == "electron") {
		  	cvcCodeElem.maxLength = 3;
			document.getElementById( 'card.cvcName' ).innerHTML = "CVV";
			document.getElementById( 'card.cvcWhatIs' ).innerHTML = "O que é CVV?";
			document.getElementById( 'card.cvcFrame' ).innerHTML = card_cvcinfo["visa"];
		  } else if (selectedCardType == "mc" || selectedCardType == "maestro" || selectedCardType == "maestrouk" || selectedCardType == "solo" || selectedCardType == "bijcard" || selectedCardType == "elo") {
		  	cvcCodeElem.maxLength = 3;
			document.getElementById( 'card.cvcName' ).innerHTML = "CVC";
			document.getElementById( 'card.cvcWhatIs' ).innerHTML = "O que é CVC?";
			document.getElementById( 'card.cvcFrame' ).innerHTML = card_cvcinfo["mc"];
		  } else {
		   	cvcCodeElem.maxLength = 3;
			document.getElementById( 'card.cvcName' ).innerHTML = "CVC";
			document.getElementById( 'card.cvcWhatIs' ).innerHTML = "O que é CVC?";
			document.getElementById( 'card.cvcFrame' ).innerHTML = card_cvcinfo[groupName];
		  }  
		}
	}
	
	
	function card_ccNumberPresentation(valid, groupName, dontHideErrorFrame){
		// groupName is not used anymore
		var errors = new Array();
		errors.push( 'card.cardNumber' );
		if(valid){
			clearErrors(errors, dontHideErrorFrame);
		}
		else{
			markErrorFields(errors);
		}
	}
	
	function card_doCCCheck(groupName){
		// groupName is not used anymore
		var cardNumberField = document.getElementById( 'card.cardNumber' );
		if(card_isCardNumberValid(cardNumberField)) {
			card_ccNumberPresentation(true,groupName);
		} else {
			card_ccNumberPresentation(false,groupName);
		}
	}
		
	function card_isCardNumberValid(cardNumberField){
		cardNumber = cardNumberField.value;
		reg = /\s+/g;
		cardNumber = cardNumber.replace(reg,'');
		if(cardNumber == "" || luhnCheck(cardNumber)){
			return true;
		}
		return false;
	}
	
	validationFunctions["card"] = new Array();
	validationFunctions["card"][ "card.cardNumber" ] = card_isCardNumberValid;	



//]]>
</script>

 
<table class="basetable">
	

	
<tr  id="card.cclogoTr" >
		<td class="mid">
			<div  id="card.cclogoheader"  style="display: none">Tipo de cartão</div>
					</td>
		<td class="mid">
		    <div style="height: 25px"  id="card.cclogo" >
				  <img alt=""  id="card.cclogo0"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo1"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo2"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo3"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo4"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo5"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo6"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo7"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <img alt=""  id="card.cclogo8"  style="display: none" class="mid" src="/hpp/img/pm/unknown_small.png" />
				  <script type="text/javascript">
				  	card_setCardBrand(null, false, 'card', card_types, card_logos);
				  </script>
			</div>
		</td>
</tr>

<tr  id="card.cardNumberTr" >
        <td class="cardNumberTitle"><div>Número do cartão</div></td>
        <td><div class="fieldDiv"><input type="text" class="inputField"  id="card.cardNumber" 						onkeypress="return blockNonNumberEvents(event)"
			onkeyup="card_validateCcNumber(event, 'card', card_types, card_logos, card_subVariantExtras, card_subVariantExtrasPhrase)"
			onchange="card_validateCcNumber(event, 'card', card_types, card_logos, card_subVariantExtras, card_subVariantExtrasPhrase) ; card_doCCCheck('card')"
						 name="card.cardNumber"  value="" 													 size="24" maxlength="23" 
			                                        /></div></td>
</tr>
<tr>
        <td><div>Nome e sobrenome impressos no cartão</div></td>
        <td><div class="fieldDiv">
        	<input type="text" class="inputField"  id="card.cardHolderName"   name="card.cardHolderName"  value="" size="19" maxlength="30" />
        </div></td>
</tr>

<tr>
        <td><div>Data de validade do cartão</div></td><td>
        <div class="fieldDiv"  id="card.expiryContainer" >
			<select class="inputField"  name="card.expiryMonth"   id="card.expiryMonth"  size="1">
        <option value="">&nbsp;</option>
					<option  value="01">01</option>
					<option  value="02">02</option>
					<option  value="03">03</option>
					<option  value="04">04</option>
					<option  value="05">05</option>
					<option  value="06">06</option>
					<option  value="07">07</option>
					<option  value="08">08</option>
					<option  value="09">09</option>
					<option  value="10">10</option>
					<option  value="11">11</option>
					<option  value="12">12</option>
		        </select>
        &nbsp;/&nbsp; 
        <select class="inputField"  name="card.expiryYear"   id="card.expiryYear"  size="1">
        <option value="">&nbsp;</option>
        			<option  value="2013">2013</option>
        			<option  value="2014">2014</option>
        			<option  value="2015">2015</option>
        			<option  value="2016">2016</option>
        			<option  value="2017">2017</option>
        			<option  value="2018">2018</option>
        			<option  value="2019">2019</option>
        			<option  value="2020">2020</option>
        			<option  value="2021">2021</option>
        			<option  value="2022">2022</option>
        			<option  value="2023">2023</option>
        			<option  value="2024">2024</option>
        			<option  value="2025">2025</option>
        			<option  value="2026">2026</option>
        			<option  value="2027">2027</option>
        		</select>
        </div>
        </td>
</tr>

<tr>
        <td><!--  brandCodeUndef --><div  id="card.cvcName" >CVC/CVV/CID</div></td>
        <td><div class="fieldDiv"><input class="inputField" type="text"  name="card.cvcCode"  value=""  id="card.cvcCode"  size="7" maxlength="3" /> &nbsp; 
        <a href="#" onclick="return toggleElement( 'card.cvcFrame' );">
        <span  id="card.cvcWhatIs" >O que é CVC/CVV/CID?</span></a></div></td>
</tr>

											


	<tr>
    	<td colspan="2"><div class="r">
                            <input class="paySubmit paySubmitcard" type="submit" name="pay" value="pagar" />
            		</div></td>
    </tr>
</table>

<div class="popupMsg  popupMsgOPP " style="display: none;" onclick="return hideElement( 'card.cvcFrame' );"  id="card.cvcFrame" >
	<h3>O que é CVC/CVV/CID?</h3>
	<p>O Card Security Code (CVC/CVV/CID) é um <i>additional</i>
					Código de segurança de três ou quatro dígitos que está impresso (e não gravado) na frente ou atrás
				do seu cartão.</p>
		<p>O CVC/CVV/CID é uma medida adicional de segurança para garantir que você é o possuidor do cartão.</p>
</div>


<script type="text/javascript">
//<![CDATA[
	if(document.getElementById( "card.cardNumber" ).value.length > 0) {
		var validateCcNumberTimer = setTimeout("card_validateCcNumber(null, 'card', card_types, card_logos, card_subVariantExtras, card_subVariantExtrasPhrase, true)", 2500);
	}
//]]>
</script>

						</div>
</li>
	
	
</ul>


	<div id="errorFrame" style="display: none;" class="popupMsg errorFrame">
		<div id="errorFrameValidationErrors">
		</div>
	</div>
	<div id="okFrame" style="display: none;" class="popupMsg okFrame">
	<div id="okFrameMessages">
	</div>
</div>



<input type="text" style="display: none" />
 <input type="hidden" name="sig" value="6SXAFPqB3aqwsKCHExxNmWASldc=" /> 
 <input type="hidden" name="merchantReference" value="21310041-1357243740" /> 	
 <input type="hidden" name="brandCode" value="brandCodeUndef" /> 
 <input type="hidden" name="paymentAmount" value="7980" /> 
 <input type="hidden" name="currencyCode" value="BRL" /> 
 <input type="hidden" name="shipBeforeDate" value="1357243736" /> 	
 <input type="hidden" name="skinCode" value="WMQ8fNZJ" /> 
 <input type="hidden" name="merchantAccount" value="JanamesaCieCom" /> 
 <input type="hidden" name="shopperLocale" value="pt_BR" /> 
 <input type="hidden" name="stage" value="pay" /> 	
 <input type="hidden" name="sessionId" value="olxqgJjEk7sQkHPRa1OqFaEoqiQ=" /> 
 <input type="hidden" name="orderData" value="H4sIAAAAAAAAA7NJySxTSM5JLC62VUpOLCpRsrMBiWSm2Fam6BalFpfo5iXmptoFAVmJpUWJeSWpCl6HF4LEihMV/HydbPSByu0gJAD/xvuATAAAAA==" /> 

 <input type="hidden" name="sessionValidity" value="1357247336" /> 



 <input type="hidden" name="countryCode" value="" /> 
	
 <input type="hidden" name="shopperEmail" value="eduardo@eduardo.cc" /> 
 <input type="hidden" name="shopperReference" value="1357243741" /> 

 <input type="hidden" name="resURL" value="" /> 
	






 <input type="hidden" name="originalSession" value="H4sIAAAAAAAAAG2SW4/aMBCF/0ueKSHcskVaqUkWRCPYXRIuVRVpZewJeHFs4zgLadX/XptrqzYvluaMz3w+mZ/Omgm8AzIFvRWkdAa8YqzhCEVAPSGNnIEz7pZfg+vnP8d1epyn0148ifrt5UK+TCKZlGoa0lVatEeo6IeIjWQuevRbIbUYBcsilotVDOlKRqw/HnUnQ7qdLjN3XJP1axrWVWsTB0+Ze/yogvlpzOOj03BwpRRwXEeCgOEIk4kpFqDwFnGdgK4UPyOemdeUMco3ASEKynJeS7gqlFOdarSxLhLVxqXcCilBJZCDHWEFr9Pz292O3/X+mnJvaHsdr9Xqep9unS3rtKP8Ariazh7y5+/x3X9YIMqMAqRCiogvl7OJ8b3HgGkogOsr7XV2gLGobNmJEUcFlCiiEInidJXKEHKhwATwJ3ynb1TzROsXFOfr/ueHVsNR9iUK1MKkOHC2WstB5mbu4XBovl/sm1gUzbXK3IvBGyI18My18VHE6A+4U08ERuyUp34LE1s3mVPBl6aPUF3fmPzOiYkAox+g6v/8HcSYOPyzgdcUUroxXoId95v4fbjzy9lu/Jog72U/QkOxpzO7KSLPS7gFqMBujlmFSHCtEL4Iv34DlEmILe0CAAA=" /> 	
								
								
								

	
	

	<input type="hidden" name="referrerURL" value="http://www.janamesa.com.br/payment_adyen/initialize" />


	<input type="hidden" name="usingFrame" id="usingFrame" value="false" />
	<script type="text/javascript">
		//<![CDATA[
		try {
    		var uf = _.G("usingFrame");
    		if(uf && uf.value != "") {
    			uf.value = ( top.location != self.location );
    		}
		} catch(e){ }
		//]]>
	</script>
  <div class="paddiv2"></div>
  	</div>
<?php
include('inc/pmfooter.txt');
?>  	    </div>
  <div id="foot">
	<div id="footc">
	  <div id="nextstep">
		<div id="nextstepc">Próxima etapa: Insira os detalhes de pagamento</div>
	  </div>
	  <div id="footerb2div">
	  	  </div>
	  <div id="footerb1div">
	  		   <input 		  						  onclick="this.blur(); prepareForBack();" name="back" id="mainBack" value="anterior" type="submit" class="hideforprint footerB backB"
						  />
		  	  	  </div>
	</div>
  </div>
    </form>
    
        
<?php
include('inc/cfooter.txt');
?>

      </body>
</html>