%
%  order report template
%
%  Created by Matthias Laug on 2009-08-06.
%
\documentclass[11pt]{scrartcl}

% set font to times
\usepackage{mathptmx}
\usepackage[scaled=1]{helvet}
\renewcommand\familydefault{phv}

% set margins 
\usepackage[left=5mm,right=10mm, top=15mm, bottom=30mm]{geometry}

% Use utf-8 encoding for foreign characters
\usepackage[utf8]{inputenc}

% Use german language
\usepackage[german]{babel}
\usepackage[T1]{fontenc}
\usepackage{pdfpages}

% Setup for fullpage use
% multirow - rowspan in tables
% graphicx - include graphics
% fancyhdr - footer/header
% booktabs - nice tables
\usepackage{multirow, graphicx, fancyhdr, booktabs,array,longtable,lastpage}

% define \EUR{amount}
\usepackage[left]{eurosym}

%booktabs settings
\newcolumntype{V}[1]{
  >{\bfseries\huge}p{#1}
} 							%tabellenüberschriften
\newcolumntype{T}[1]{
  >{\bfseries\large}p{#1}
} 							%tabellenüberschriften
\newcolumntype{v}[1]{
  >{\raggedright}p{#1}
} 							%verwende v{Xcm} für linksbündige Spalten fester Breite
\newcolumntype{w}[1]{
  >{\raggedleft}p{#1}
} 							%verwende v{Xcm} für rechtsbündige Spalten fester Breite
\newcolumntype{x}[1]{
  >{\centering}p{#1}
} 							%verwende v{Xcm} für zentrierte Spalten fester Breite

%-------------------------------- Kopf- und Fußzeile
\pagestyle{fancy}
\fancyhf{}
\setlength{\parindent}{0pt} 
%header
\fancyhead[L]{}

<<if $order->getService()->isAvanti() && $order->getDomain() == 'www.avanti.de'>>
\fancyhead[R]{\ifnum\thepage=1 \includegraphics{../includes/logos/avanti-de.jpg}\fi}
<<else>>
\fancyhead[R]{\ifnum\thepage=1 \includegraphics{../includes/logos/<<$DOMAIN_BASE|replace:'.':'-'>>.jpg}\fi}
<</if>>

\renewcommand{\headrulewidth}{0pt}
% footer
\fancyfoot[L]{<<__('Verschickt durch %s, %s', $config->locale->headoffice->name, $config->locale->headoffice->address )|escape_latex>>\\
 \textbf{<<__('Kundensupport:')|escape_latex>>} <<__('%s, Mo. - Fr. 09:00 - 24:00 Uhr, Sa. - So. 11:00 - 24:00 Uhr', $config->locale->areacodetel->support)|escape_latex>> \\
<<__('Änderungen können in der Zeit Montag bis Freitag von 09:00 - 18:00 Uhr durchgeführt werden.')|escape_latex>>
}
\fancyfoot[C]{}
\fancyfoot[R]{}
\renewcommand{\footrulewidth}{0.4pt}


%-------------------------------- content
\begin{document}
	\vspace*{5mm}

		% Bestellinformationen
		\begin{longtable}{lp{15cm}}

			\multicolumn{2}{V{\textwidth}}{<<__('Besteller Informationen')|escape_latex>>} \\
			\toprule[2pt]

            \\
        <<if $order->getService()->hasCourier()>>
            \Huge{<<__('Abholung am:')|escape_latex>>}  & \Huge{<<$order->computePickUpTime()|date_format:__("%d.%m.%y %H:%M")>>}\\
        <<else>>
            \Huge{<<__('Lieferzeitpunkt:')|escape_latex>>}  & \Huge{<<$order->getDeliverTimeFormated()|date_format:__("%d.%m.%y %H:%M")>>}\\
        <</if>>
            \\
            <<assign var=location value=$order->getLocation()>>
            <<__('Bestellt am:')|escape_latex>>             & \textbf{<<$order->getTime()|date_format:__("%d.%m.%y %H:%M")>>}\\
            <<__('Bestellnummer:')|escape_latex>>               & \textbf{<<$order->getNr()|escape_latex>>}\\
            <<__('Name:')|escape_latex>>                   & \textbf{<<$order->getCustomer()->getFullname()|escape_latex>>}\\
            <<__('Strasse:')|escape_latex>>                & \textbf{<<if $DOMAIN_BASE != 'taxiresto.fr'>><<$location->getStreet()|escape_latex>> <<$location->getHausnr()|escape_latex>><<else>><<$location->getHausnr()|escape_latex>> <<$location->getStreet()|escape_latex>><</if>>}\\
            <<if !is_null($location->getOrt())>>
                <<__('Stadt:')|escape_latex>>                  & \textbf{<<$location->getPlz()|escape_latex>> <<$location->getOrt()->getOrt()|escape_latex>>}\\
            <<else>>
                <<__('Stadt:')|escape_latex>>                  & \textbf{<<__("k.A.")|escape_latex>>}\\
            <</if>>
            <<__('Lieferanweisungen:')|escape_latex>>      & \multicolumn{1}{v{13.5cm}}{\textbf{<<$location->getComment()|default:__("k.A.")|escape_latex>>}}\\
            <<__('Firma:')|escape_latex>>                  & \textbf{<<$location->getCompanyName()|default:__("k.A.")|escape_latex>>}\\
            <<__('Stockwerk:')|escape_latex>>              & \textbf{<<$location->getEtage()|default:__("k.A.")|escape_latex>>}\\
            <<__('Telefon:')|escape_latex>>                & \textbf{<<$location->getTel()|default:__("k.A.")|escape_latex>>}\\
                
		\end{longtable}
		
		\vspace*{1cm}

        <<if !$order->isContract()>>
            \LARGE{\textbf{<<__('Achtung, diese Bestellung ist kostenlos von %s vermittelt', $config->domain->base)|escape_latex>>}}
        <</if>>
                
        <<if $order->getCashAmount() > 0>>
            \huge{\textbf{<<__('ACHTUNG: OFFENER BETRAG:')|escape_latex>> <<__('%s €', $order->getCashAmount()|inttoprice)|escape_latex>>}}\\
            <<if $order->getPaymentAddition()>>
            \LARGE{\textbf{<<__('KUNDE MÖCHTE MIT %s BEZAHLEN', $order->getPaymentAdditionReadable())|escape_latex>>}}
            <<else>>
            \LARGE{\textbf{<<__('BARZAHLUNG')|escape_latex>>}}
            <</if>>
            \normalsize
        <<else>>
            \huge{\textbf{<<__('ACHTUNG: Bestellung ist bereits online bezahlt!')|escape_latex>>}}\\
            \LARGE{\textbf{<<__('Bitte kassieren Sie nicht beim Kunden, %s', $config->locale->headoffice->name)|escape_latex>> \\ <<__('überweist Ihnen den Betrag wie vertraglich vereinbart.')|escape_latex>>}}
            \normalsize
        <</if>>
        
        <<if $order->getCpf() && $config->domain->base == 'janamesa.com.br'>>
            \huge{\textbf{Cliente quer CPF na nota: <<$order->getCpf()|escape_latex>>}} \\
            \normalsize
        <</if>>
        
        <<if $order->getChange() > 0 && $config->domain->base == 'janamesa.com.br'>>
            \huge{\textbf{Troco para: R$ <<$order->getChange()|inttoprice|escape_latex>>}} \\
            \normalsize
        <</if>>
        
        <<if $order->getService()->hasCourier()>>

            \textbf{<<__('Bestellung wird von %s abgeholt!',$order->getService()->getCourier()->getName()|escape_latex)>>}
            <<if $order->getService()->hasPromptCourier()>>
                <<__('Lieber Restaurantmitarbeiter, damit für den Kurier keine Wartezeiten entstehen, bitten wir Sie die besprochene Zubereitungszeit von %s Minuten und den Abholzeitpunkt von %s genau einzuhalten.', $order->getService()->getRealDeliverTimeFormated($order->getLocation()->getCityId()), $order->computePickUpTime()|date_format:__("%d.%m.%y %H:%M"))|escape_latex>>
                <<__('Bei Verzögerungen oder evtl. Rückfragen erreichen Sie uns von 9.00 bis 24.00 Uhr unter der folgenden Tel. Nummer: 030 609 88 548')|escape_latex>>
            <<else>>
                <<__('Lieber Restaurantmitarbeiter, in Kürze werden Sie einen Anruf von unserem Telefonsupport erhalten.  Wir würden Sie bitten die genaue Abholzeit mit unserem Supportmitarbeiter abzustimmen.
                Damit für den Kurier keine Wartezeiten entstehen, bitten wir Sie den besprochenen Abholzeitpunkt genau einzuhalten. Für evtl. Rückfragen erreichen Sie uns von 9.00 bis 24.00 Uhr unter der folgenden Tel. Nummer: 030 609 88 548')|escape_latex>>
            <</if>>
        <</if>>

        <<if $order->getService()->getName() == 'Vapiano' and $order->getSoldPfand() > 0>>
            \vspace*{1cm}
            \textbf{Gegen Vorlage dieser Bestellbestätigung können Sie die Dessert-Pfandgläser in der
                    Vapiano Filiale Bockenheimer Landstraße einlösen und erhalten einen Betrag von \EUR{<<$order->getSoldPfand()|inttoprice>>} erstattet.
                    Bitte haben Sie Verständnis, dass unsere Fahrer aus logistischen Gründen keine Pfandgläser abholen oder auszahlen können.}
        <</if>>

        \vspace*{1cm}

		% ------- Start Order
		\begin{longtable}{x{0.9cm}v{12cm}v{1cm}w{2cm}r}
			\multicolumn{5}{V{\textwidth}}{<<__('Bestellung')|escape_latex>><<if $order->getService()->hasPromptCourier()>>-ID: <<Yourdelivery_Model_DbTable_Prompt_Nr::getNr($order)>><</if>>} \\
			\toprule[1pt]

			% Description of service
			\multicolumn{5}{v{\textwidth}}{
                <<$order->getService()->getName()|escape_latex>>,
                <<$order->getService()->getStreet()|escape_latex>> <<$order->getService()->getHausnr()|escape_latex>>,
                <<$order->getService()->getPlz()|escape_latex>>,
                <<__('Fax:')|escape_latex>> <<$order->getService()->getFax()|default:__('k.A.')|escape_latex>>,
                <<__('Tel:')|escape_latex>> <<$order->getService()->getTel()|default:__('k.A.')|escape_latex>>,
                <<__('Kdnnr.:')|escape_latex>> <<$order->getService()->getCustomerNr()|escape_latex>>} \\
            \addlinespace[7mm]

            <<assign var=card value=$order->getCard()>>
            <<assign var=bucket value=$card.bucket>>

            \textbf{QTD}	& \textbf{<<__('Artikel & Zutaten')|escape_latex>>}	& \textbf{<<__('Art.Nr.')|escape_latex>>}	& \textbf{<<__('Einzelpreis')|escape_latex>>}	& \textbf{<<__('Summe')|escape_latex>>} \\
            \midrule
            <<foreach from=$order->getCard() item=card>>
                <<foreach from=$card item=bucket>>
                    <<foreach from=$bucket item=cBucket>>
                        <<assign var=meal value=$cBucket.meal>>
                        %item
                        <<assign var=mealTotal value=$meal->getCost()>>
                        \large{\textbf{<<$cBucket.count>> x}} &
                        <<$meal->getName()|escape_latex>> <<$meal->getCurrentSizeName()|escape_latex>>
                        <<foreach from=$meal->getCurrentExtras() item=extra>>
                            <<assign var=mealTotal value=$mealTotal+$extra->getCost()>>
                            \textbf{<<$extra->getName()|escape_latex>> <<$extra->getCount()>>x<<if $extra->getCost() > 0>>(<<__('%s €', ($cBucket.count * $extra->getCost())|inttoprice)|escape_latex>>)<</if>>},
                        <</foreach>>
                        <<foreach from=$meal->getCurrentOptions() item=option>>
                            <<assign var=mealTotal value=$mealTotal+$option->getCost()>>
                            \textbf{<<$option->getName()|escape_latex>> <<if $option->getCost() > 0>>(<<__('%s €', ($cBucket.count * $option->getCost())|inttoprice)|escape_latex>>),<</if>>}
                        <</foreach>>
                        & <<$meal->getNr()|escape_latex>> & <<__('%s €', $meal->getCost()|inttoprice)|escape_latex>> & <<__('%s €', ($mealTotal * $cBucket.count)|inttoprice)|escape_latex>> \\
                        & & & &  \\
                        <<if $meal->getSpecial() != "">>
                            & \textbf{<<__('Kundenhinweis:')|escape_latex>> <<$meal->getSpecial()|escape_latex>>} & & \\
                        <</if>>
                        \midrule
                    <</foreach>>
                <</foreach>>
            <</foreach>>

            <<if $order->getDeliverCost() > 0>>
                && \multicolumn{2}{r}{\textbf{<<__('Lieferkosten:')|escape_latex>>}} & \textbf{<<__('%s €', $order->getDeliverCost()|inttoprice)|escape_latex>>} \\
            <</if>>

            <<foreach from=$taxes item=tax>>
                <<if $order->getTax($tax)>0>>
                    && \multicolumn{2}{r}{\textbf{<<__('inklusive MwSt %s%%:', $tax)|escape_latex>>}} & \textbf{<<__('%s €', $order->getTax($tax)|inttoprice)|escape_latex>>} \\
                <</if>>
            <</foreach>>

            && \multicolumn{2}{r}{} & \\
            && \multicolumn{2}{r}{\textbf{<<__('Gesamtbetrag:')|escape_latex>>}} & \textbf{<<__('%s €', $order->getAbsTotal(false, false, true)|inttoprice)|escape_latex>>} \\

            <<if $order->getPayedAmount() > 0>>
                && \multicolumn{2}{r}{\textbf{<<__('Bereits bezahlt:')|escape_latex>>}} & \textbf{<<__('%s €', $order->getPayedAmount()|inttoprice)|escape_latex>>} \\
            <</if>>

        \end{longtable}
       
        <<if !$order->isContract()>>
            \includepdf[]{../includes/nocontract.pdf}
        <</if>>
        
\end{document}
