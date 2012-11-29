<<extends file="bill/base.tpl">>

    <<block name="content">>

        <<$header.preamble|escape_latex>> <<__('im Zeitraum vom %s bis zum %s stellen wir Ihnen in Rechnung:',$bill->from|date_format:"%d.%m.%Y",$bill->until|date_format:"%d.%m.%Y")|escape_latex>> \\

        \begin{center}
        <<if $config->locale->latex->hideSatellites>>
            \begin{longtable}{p{15cm}r}
        <<else>>
            \begin{longtable}{p{11cm}rr||r}
        <</if>>
        \hiderowcolors
        <<if !$config->locale->latex->hideSatellites>>
            & <<$config->domain->base>> & <<__('Domains')|escape_latex>> 
        <</if>>
        & <<_('Gesamt')|escape_latex>> \\
        <<__('Brutto Umsatz (€):')|escape_latex>> & 
        <<if !$config->locale->latex->hideSatellites>>
            <<$bill->getBrutto('all',true,false)|inttoprice|escape_latex>> &
            <<$bill->getBrutto('all',false,true)|inttoprice|escape_latex>> &
        <</if>>
        <<$bill->getBrutto()|inttoprice|escape_latex>> \\

        <<if $bill->getSoldPfandTotal() > 0>>
            <<__('Verkauftes Pfand (€):')|escape_latex>> & 
            <<if !$config->locale->latex->hideSatellites>>
                (<<$bill->getSoldPfandTotal('all',true,false)|inttoprice|escape_latex>>) &
                (<<$bill->getSoldPfandTotal('all',false,true)|inttoprice|escape_latex>>) &
            <</if>>
            (<<$bill->getSoldPfandTotal()|inttoprice|escape_latex>>) \\
        <</if>>

        <<if !$bill->getService()->isBillDeliverCost()>>
            <<__('Lieferkosten im Leistungszeitrum (€):')|escape_latex>> & 
            <<if !$config->locale->latex->hideSatellites>>
                <<$bill->getDeliverCostTotal('all',true,false)|inttoprice|escape_latex>> &
                <<$bill->getDeliverCostTotal('all',false,true)|inttoprice|escape_latex>> &
            <</if>>
            <<$bill->getDeliverCostTotal()|inttoprice|escape_latex>> \\
        <</if>>

        <<__('Provisionspflichtiges Brutto gesamt (€):')|escape_latex>>  & 
        <<if !$config->locale->latex->hideSatellites>>
            <<$bill->getProvTotal('all',true,false)|inttoprice|escape_latex>> &
            <<$bill->getProvTotal('all',false,true)|inttoprice|escape_latex>> &
        <</if>>
        <<$bill->getProvTotal()|inttoprice|escape_latex>>\\

        <<if $bill->getProvTotal('all',false, true)>>
            <<__('Provisionspflichtiges Brutto bei (€):')|escape_latex>> &
            <<if !$config->locale->latex->hideSatellites>>
                <<$bill->getProvTotal('all',true,false)|inttoprice|escape_latex>> &
                <<$bill->getProvTotal('all',false, true)|inttoprice|escape_latex>> & 
            <</if>>
            <<$bill->getProvTotal()|inttoprice|escape_latex>>\\
        <</if>>

        \hline

        <<if count($bill->getCommissionsInterval()) > 0>>
             <<foreach from=$bill->getCommissionsInterval() item=special>>
                <<__('Vertraglich festgelegte Provision %s %% vom %s bis %s (€)', $special.komm, $special.from|date_format:"%d.%m.%Y", $special.until|date_format:"%d.%m.%Y")|escape_latex>> &
                <<if !$config->locale->latex->hideSatellites>>
                    <<$bill->calculateCommissionSpecial($special.from,$special.until)|inttoprice:3|escape_latex>> &
                    0,00 &
                <</if>>
                <<$bill->calculateCommissionSpecial($special.from,$special.until)|inttoprice:3|escape_latex>> \\
                <</foreach>>
        <</if>>

        %BASE DOMAIN
        <<if $bill->calculateCommissionPercentStatic('all') > 0>>
            <<__('Vertraglich festgelegte Provision %s%%/%s%% vom Brutto Umsatz (€):', $service->getStaticCommission(), $service->getKommSat())|escape_latex>> & 
            <<if !$config->locale->latex->hideSatellites>>
                <<$bill->calculateCommissionPercentStatic('all',true, false)|inttoprice:3|escape_latex>> &
                <<$bill->calculateCommissionPercentStatic('all', false, true)|inttoprice:3|escape_latex>> &
            <</if>>
            <<$bill->calculateCommissionPercentStatic()|inttoprice|escape_latex>> \\
        <</if>>

        <<if $bill->calculateCommissionEach('all') > 0>>
            <<__('Vertraglich festgelegte Gebühr pro Bestellung %s/%s €', inttoprice($service->getStaticFee()), inttoprice($service->getFeeSat()))|escape_latex>>  & 
            <<if !$config->locale->latex->hideSatellites>>
                <<$bill->calculateCommissionEach('all', true, false)|inttoprice|escape_latex>> &
                <<$bill->calculateCommissionEach('all', false, true)|inttoprice|escape_latex>> &
            <</if>>
            <<$bill->calculateCommissionEach()|inttoprice|escape_latex>> \\
        <</if>>

        <<if $bill->calculateCommissionItem('all') > 0>>
            <<__('Vertraglich festgelegte Gebühr pro Artikel %s/%s €', $service->getStaticItem()|inttoprice, $service->getSatStatic()|inttoprice)|escape_latex>> &
            <<if !$config->locale->latex->hideSatellites>>
                <<$bill->calculateCommissionItem('all', true, false)|inttoprice|escape_latex>> &
                <<$bill->calculateCommissionItem('all', false, true)|inttoprice|escape_latex>> &
            <</if>>
            <<$bill->calculateCommissionItem()|inttoprice|escape_latex>>\\
        <</if>>              


        <<if $bill->calculateTransactionCost() > 0>>
            <<__('Gebühren Onlinezahlung (€):', $config->tax->provision)|escape_latex>> &
            <<if !$config->locale->latex->hideSatellites>>
                <<$bill->calculateTransactionCost('all', true, false)|inttoprice:3|escape_latex>> &
                <<$bill->calculateTransactionCost('all', false, true)|inttoprice:3|escape_latex>> &
            <</if>>
            <<$bill->calculateTransactionCost()|inttoprice|escape_latex>>\\
        <</if>>

        <<if $service->getBasefee() > 0>>
            <<__('Grundgebühr (€):')|escape_latex>> & 
            <<if !$config->locale->latex->hideSatellites>>
                0,00 &
                <<$service->getBasefee()|inttoprice|escape_latex>> & 
            <</if>>
            <<$service->getBasefee()|inttoprice|escape_latex>> \\
        <</if>>

        <<__('Summe Netto (€)')|escape_latex>> <<$config->tax->provision>>\%:         & 
        <<if !$config->locale->latex->hideSatellites>>
            <<$bill->calculateInvoiceNetto('all', true, false, false, true)|inttoprice:3|escape_latex>> &
            <<$bill->calculateInvoiceNetto('all', false, true, true, true)|inttoprice:3|escape_latex>> &
        <</if>>
        <<$bill->calculateInvoiceNetto()|inttoprice|escape_latex>>\\

        <<if $config->tax->provision > 0>>
            <<if $bill->getCommTaxTotal()> 0>>
                <<__('MwSt (€)')|escape_latex>> <<$config->tax->provision>>\%:         & 
                <<if !$config->locale->latex->hideSatellites>>
                    <<$bill->calculateInvoiceTax('all', true, false)|inttoprice:3|escape_latex>> &
                    <<$bill->calculateInvoiceTax('all', false, true)|inttoprice:3|escape_latex>> &
                <</if>>
                <<$bill->calculateInvoiceTax()|inttoprice|escape_latex>>\\
                \hline             
            <</if>>
        <</if>>

        <<if $config->tax->provision > 0>>
            <<__('Summe Brutto (€):')|escape_latex>>
        <<else>>
            <<__('Summe Netto (€):')|escape_latex>>
        <</if>>  & 

        <<if !$config->locale->latex->hideSatellites>>
            <<$bill->calculateInvoiceBrutto('all', true, false)|inttoprice|escape_latex>> &
            <<$bill->calculateInvoiceBrutto('all', false, true)|inttoprice|escape_latex>> &
        <</if>>
        <<$bill->calculateInvoiceBrutto()|inttoprice|escape_latex>>\\


        \hline

        <<if $bill->getBalanceAmount() < 0>>
            \ding{204}<<__('Übertrag aus Verrechnung (€):')|escape_latex>> & 
            <<if !$config->locale->latex->hideSatellites>>
                --- &
                --- &
            <</if>>
            <<($bill->getBalanceAmount()|abs|inttoprice)|escape_latex>> \\    
            <<__('Bereits bezahlt (€):')|escape_latex>> & 
            <<if !$config->locale->latex->hideSatellites>>
                --- &
                --- &
                <<if $bill->getAlreadyPayed(false) > ($bill->calculateInvoiceBrutto()+abs($bill->getBalanceAmount()))>>
                    <<($bill->calculateInvoiceBrutto()+abs($bill->getBalanceAmount()))|inttoprice|escape_latex>>
                <<else>>
                    <<$bill->getAlreadyPayed(false)|inttoprice|escape_latex>>
                <</if>> \\
            <<else>>
                <<$bill->getAlreadyPayed(false)|inttoprice|escape_latex>>
            <</if>> \\
        <<else>>
            <<__('Bereits bezahlt (€):')|escape_latex>> &                
            <<if !$config->locale->latex->hideSatellites>>
                --- &
                --- &
            <</if>>
            
            <<if $bill->getAlreadyPayed(false) > $bill->calculateInvoiceBrutto()>>
                <<$bill->calculateInvoiceBrutto()|inttoprice|escape_latex>>
            <<else>>
                <<$bill->getAlreadyPayed(false)|inttoprice|escape_latex>>
            <</if>> \\
            
        <</if>>

        \hline
        <<__('Offener Rechnungsbetrag (€):')|escape_latex>> &          
        <<if !$config->locale->latex->hideSatellites>>
            --- &
            --- &
        <</if>>
        <<$bill->calculateBillingAmount(true,false)|inttoprice|escape_latex>> \\
        \hline
        \hline
        \end{longtable}
        \end{center}


        <<if $bill->calculateBillingAmount(true,true) > 0>>
            <<if !$bill->getService()->isDebit()>>
                <<__('Bitte überweisen Sie den offenen Rechnungsbetrag unter Angabe der Rechnungsnummer auf unser Konto!')|escape_latex>>
            <</if>>
            <<__('Eine genaue Auflistung der Leistungen entnehmen Sie bitte dem Anhang.')|escape_latex>>
        <</if>>

        <<if ($DOMAIN_BASE == 'lieferando.at' || $DOMAIN_BASE == 'lieferando.ch')>>
            yd. yourdelivery GmbH \\
            IBAN: DE25 100 701 240 1121 32 600 \\
            BIC: DEUTDEDB101 \\
        <</if>>

        <<if $DOMAIN_BASE == 'lieferando.ch'>>
            <<'Die Leistung unterliegt der Bezugsteuerpflicht nach Art. 45 MWSTG.'|escape_latex>>
        <</if>>

        <<if $DOMAIN_BASE == 'lieferando.at'>>
            <<'Nicht steuerpflichtig nach Artikel 44 der MwSt-Systemrichtlinie 2006/112/EC'|escape_latex>> \\
            <<'Steuerpflicht liegt beim Begünstigten nach Artikel 196 der MwSt-Systemrichtlinie 2006/112/EC'|escape_latex>>
        <</if>>

        <<if $DOMAIN_BASE == 'taxiresto.fr'>>
            <<'Prestation hors TVA. cf. Art. 44 & 196 de la Directive 2006/112/CE, TVA due par le client.'|escape_latex>>
        <</if>>
        
        <<if $DOMAIN_BASE == 'pyszne.pl'>>
            <<'SPRZEDAWCA:'|escape_latex>>\\
            <<'STO2 SP. Z O.O. UL. LELEWELA 15 53-505 WROCŁAW'|escape_latex>>\\
            <<'NIP: 615-200-77-26'|escape_latex>>
        <</if>>

        <<include file='bill/service/include/attachment_balance.tpl'>>

        <<include file='bill/service/include/attachment.tpl'>>

    <</block>>
