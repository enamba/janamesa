<<extends file="bill/base.tpl">>

<<block name="content">>

    \begin{longtable}{p{12cm}r}

        \hiderowcolors
        <<foreach from=$taxes item=tax>>
            <<__('Netto')|escape_latex>> <<$tax>>\% <<__('€')|escape_latex>>:        & <<$bill->calculateItem('all', $tax)|inttoprice|escape_latex>> \\
        <</foreach>>
        \hline
        \ding{202}\textbf{<<__('Summe Netto (€):')|escape_latex>>}	   & \textbf{<<$bill->calculateItem()|inttoprice|escape_latex>>} \\ \\

        <<foreach from=$taxes item=tax>>
            <<__('Netto')|escape_latex>> <<$tax>>\% <<__('€')|escape_latex>>:        & <<$bill->calculateTax('all', $tax)|inttoprice|escape_latex>> \\
        <</foreach>>
        \hline
        \ding{203}\textbf{<<__('Summe Mwst (€):')|escape_latex>>}        & \textbf{<<$bill->calculateTax()|inttoprice|escape_latex>>} \\ \\

        \ding{202}+\ding{203}\textbf{<<__('Summe Brutto (€):')|escape_latex>>}     & \textbf{<<$bill->getBrutto()|inttoprice|escape_latex>>} \\
        <<if $bill->getPfandTotal('all')>0>><<__('Pfand erhalten und')|escape_latex>> <</if>><<__('Bar bezahlt (€):')|escape_latex>>     &  (<<($bill->getPfandTotal('all') + $bill->getCashTotal('all'))|inttoprice|escape_latex>>) \\
               
        <<__('Brutto Betrag aus offener Rechnung (€):')|escape_latex>> \textbf{<<$bill->getNumber()>>}: & (<<($bill->calculateInvoiceBrutto())|inttoprice|escape_latex>>) \\
        \hline
        \textbf{<<__('Gutschrift (€):')|escape_latex>>} & \textbf{<<$bill->getVoucherAmount()|inttoprice|escape_latex>>} \\
        <<if $bill->getVoucherAmount(true) != $bill->getVoucherAmount()>>
            \hline
            \ding{204} <<__('Übertrag aus Verrechnung (€):')|escape_latex>> & \textbf{<<if $bill->getBalanceAmount() < 0>>(<</if>><<$bill->getBalanceAmount()|abs|inttoprice|escape_latex>><<if $bill->getBalanceAmount() < 0>>)<</if>>} \\    
            \textbf{<<__('finale Gutschrift (€):')|escape_latex>>} & \textbf{<<$bill->getVoucherAmount(true)|inttoprice|escape_latex>>} \\
        <</if>>
        \hline
        \hline
    \end{longtable}
     
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

    <<include file='bill/service/include/attachment_balance.tpl'>>
        
    <<include file='bill/service/include/attachment_voucher.tpl'>>
<</block>>