<<extends file="bill/base.tpl">>

    <<block name="content">>

        <<$custom.preamble|escape_latex>> <<__('im Zeitraum vom %s bis zum %s stellen wir Ihnen in Rechnung:',$bill->from|date_format:"%d.%m.%Y",$bill->until|date_format:"%d.%m.%Y")|escape_latex>> \\

        \begin{center}
        \begin{longtable}{p{15cm}r}
        \hiderowcolors
        <<__('Brutto Umsatz (€):')|escape_latex>> &
        <<$bill->getProvTotal()|inttoprice|escape_latex>>\\
        
        <<if !$bill->getService()->isBillDeliverCost()>>
            <<__('Lieferkosten im Leistungszeitrum (€):')|escape_latex>> & 
            <<$bill->getDeliverCostTotal()|inttoprice|escape_latex>> \\
        <</if>>

        \hline

        <<if count($bill->getCommissionsInterval()) > 0>>
             <<foreach from=$bill->getCommissionsInterval() item=special>>
                <<if $bill->calculateCommissionSpecial($special.from,$special.until) > 0>>
                    <<__('Vertraglich festgelegte Provision %s %% vom %s bis %s (€)', $special.komm, $special.from|date_format:"%d.%m.%Y", $special.until|date_format:"%d.%m.%Y")|escape_latex>> &
                    <<$bill->calculateCommissionSpecial($special.from,$special.until)*-1|inttopricewithnegative:3|escape_latex>> \\
                <</if>>
             <</foreach>>
        <</if>>

        %BASE DOMAIN
        <<if $bill->calculateCommissionPercentStatic('all') != 0>>
            <<__('Vertraglich festgelegte Provision %s%% (€):', $service->getStaticCommission())|escape_latex>> & 
            <<($bill->calculateCommissionPercentStatic('all')*-1)|inttopricewithnegative|escape_latex>> \\
        <</if>>

        <<if $bill->calculateCommissionEach('all') != 0>>
            <<__('Vertraglich festgelegte Gebühr pro Bestellung %s/%s €', inttoprice($service->getStaticFee()), inttoprice($service->getFeeSat()))|escape_latex>>  & 
            <<($bill->calculateCommissionEach('all')*-1)|inttopricewithnegative|escape_latex>> \\
        <</if>>

        <<if $bill->calculateCommissionItem('all') != 0>>
            <<__('Vertraglich festgelegte Gebühr pro Artikel %s/%s €', $service->getStaticItem()|inttoprice, $service->getSatStatic()|inttoprice)|escape_latex>> &
            <<($bill->calculateCommissionItem('all')*-1)|inttopricewithnegative|escape_latex>>\\
        <</if>>              

        <<if $bill->calculateTransactionCost() != 0>>
            <<__('Gebühren Onlinezahlung (€):', $config->tax->provision)|escape_latex>> &
            <<($bill->calculateTransactionCost()*-1)|inttopricewithnegative|escape_latex>>\\
        <</if>>

        <<if $service->getBasefee() != 0>>
            <<__('Grundgebühr (€):')|escape_latex>> & 
            <<($service->getBasefee()*-1)|inttopricewithnegative|escape_latex>> \\
        <</if>>
        
        <<if $bill->getCashTotal('all') != 0>>
            <<__('Bar bezahlt (€):')|escape_latex>>     &
            <<($bill->getCashTotal('all')*-1)|inttopricewithnegative|escape_latex>> \\
        <</if>>

        \hline

        <<if $bill->getBalanceAmount() < 0>>
            <<__('Übertrag aus Verrechnung (€):')|escape_latex>> & 
            <<$bill->getBalanceAmount()|inttopricewithnegative|escape_latex>> \\    
            <<__('Bereits bezahlt (€):')|escape_latex>> & 
            <<if $bill->getAlreadyPayed(false) > ($bill->calculateInvoiceBrutto()+abs($bill->getBalanceAmount()))>>
                <<(($bill->getCommBruttoTotal()*-1)-$bill->calculateInvoiceBrutto()-abs($bill->getBalanceAmount()))|inttopricewithnegative|escape_latex>>
            <<else>>
                <<($bill->getAlreadyPayed(false)*-1)|inttopricewithnegative|escape_latex>>
            <</if>> \\
        <<elseif $bill->getBalanceAmount() > 0>>
             <<__('Bereits bezahlt (€):')|escape_latex>> &                
             <<if $bill->getAlreadyPayed(false) > ($bill->getCommBruttoTotal()+$service->getBasefee())>>
                <<($bill->calculateInvoiceBrutto()*-1)|inttopricewithnegative|escape_latex>>
            <<else>>
                <<($bill->getAlreadyPayed(false)*-1)|inttopricewithnegative|escape_latex>>
            <</if>> \\
        <</if>>
        \hline

        <<__('Rechnungsbetrag (€):')|escape_latex>> &          
        <<($bill->calculateBillingAmount(true,false,true)*-1)|inttopricewithnegative|escape_latex>> \\
        \hline
        \end{longtable}
        \end{center}


        <<if $bill->calculateBillingAmount(true,false,true) > 0>>
            <<if !$bill->getService()->isDebit()>>
                <<__('Bitte überweisen Sie den offenen Rechnungsbetrag unter Angabe der Rechnungsnummer auf unser Konto!')|escape_latex>> \\
            <</if>>
            <<__('Eine genaue Auflistung der Leistungen entnehmen Sie bitte dem Anhang.')|escape_latex>> \\
        <<elseif $bill->calculateBillingAmount(true,false,true) < 0>>
            <<__('Wir überweisen den offenen Rechnungsbetrag unter Angabe der Rechnungsnummer auf Ihr Konto.')|escape_latex>> \\
            \\
            <<if $DOMAIN_BASE == 'janamesa.com.br'>>
                <<if $custom.ktoBank>>
                    <<__('Bank')|escape_latex>>: <<$custom.ktoBank|escape_latex>> \\
                <</if>>
                <<if $custom.ktoAgentur>>
                    <<__('Agentur')|escape_latex>>: <<$custom.ktoAgentur|escape_latex>> \\
                <</if>>
                <<if $custom.ktoNr>>
                    <<__('Kontonr.')|escape_latex>>: <<$custom.ktoNr|escape_latex>> \\
                <</if>>
                <<if $custom.ktoDigit>>
                    <<__('Ziffer')|escape_latex>>: <<$custom.ktoDigit|escape_latex>> \\
                <</if>>
                <<if $custom.ktoName>>
                    <<__('Kontoinhaber')|escape_latex>>: <<$custom.ktoName|escape_latex>> \\
                <</if>>
                <<if $custom.ustIdNr>>
                    <<__('CPF')|escape_latex>>: <<$custom.ustIdNr|escape_latex>> \\
                <</if>>
            <<else>>
                <<if $custom.ktoName>>
                    <<__('Kontoinhaber')|escape_latex>>: <<$custom.ktoName|escape_latex>> \\
                <</if>>
                <<if $custom.ktoNr>>
                    <<__('Kontonr.')|escape_latex>>: <<$custom.ktoNr|escape_latex>> \\
                <</if>>
                <<if $custom.ktoBlz>>
                    <<__('BLZ')|escape_latex>>: <<$custom.ktoBlz|escape_latex>> \\
                <</if>>
                <<if $custom.ktoIban>>
                    <<__('IBAN')|escape_latex>>: <<$custom.ktoIban|escape_latex>> \\
                <</if>>
                <<if $custom.ktoSwift>>
                    <<__('Swift')|escape_latex>>: <<$custom.ktoSwift|escape_latex>> \\
                <</if>>
            <</if>>
        <</if>>
        

        <<if ($DOMAIN_BASE == 'lieferando.at' || $DOMAIN_BASE == 'lieferando.ch')>>
            yd. yourdelivery GmbH \\
            IBAN: DE25 100 701 240 1121 32 600 \\
            BIC: DEUTDEDB101 \\
        <</if>>

        <<if $DOMAIN_BASE == 'lieferando.ch'>>
            <<'Die Leistung unterliegt der Bezugsteuerpflicht nach Art. 45 MWSTG.'|escape_latex>> \\
        <</if>>

        <<if $DOMAIN_BASE == 'lieferando.at'>>
            <<'Nicht steuerpflichtig nach Artikel 44 der MwSt-Systemrichtlinie 2006/112/EC'|escape_latex>> \\
            <<'Steuerpflicht liegt beim Begünstigten nach Artikel 196 der MwSt-Systemrichtlinie 2006/112/EC'|escape_latex>> \\
        <</if>>

        <<if $DOMAIN_BASE == 'taxiresto.fr'>>
            <<'Prestation hors TVA. cf. Art. 44 & 196 de la Directive 2006/112/CE, TVA due par le client.'|escape_latex>> \\
        <</if>>

        <<include file='bill/service/include/attachment_simple.tpl'>>
        <<include file='bill/service/include/attachment_stats.tpl'>>

        \newpage

    <</block>>
