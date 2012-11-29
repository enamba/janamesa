<<extends file="bill/base.tpl">>

<<block name="content">>

    <<$header.preamble|escape_latex>> <<__('im Zeitraum vom %s bis zum %s stellen wir Ihnen in Rechnung:',$bill->from|date_format:"%d.%m.%Y",$bill->until|date_format:"%d.%m.%Y")|escape_latex>> \\

    \begin{center}
        \begin{longtable}{p{12cm}r}

            \hiderowcolors
            <<__('Brutto Umsatz:')|escape_latex>>		& <<__('€ %s', $bill->getBrutto()|inttoprice)|escape_latex>> \\

            \ding{202} <<__('Provisionspflichtiges Brutto:')|escape_latex>>  & <<__('€ %s', $bill->getBrutto()|inttoprice)|escape_latex>> \\

            \hline

            <<__('Provision:')|escape_latex>>  & <<__('€ %s', $bill->getCommTotal()|inttoprice)|escape_latex>> \\
            <<__('Mwst')|escape_latex>> <<$config->tax->provision>>\%:  & <<__('€ %s', $bill->getCommTaxTotal()|inttoprice)|escape_latex>> \\
            \ding{203} <<__('Brutto Provision:')|escape_latex>>  & <<__('€ %s', $bill->getCommBruttoTotal()|inttoprice)|escape_latex>> \\
            \hline
            <<__('Bereits bezahlt:')|escape_latex>>	& <<__('€ %s', $bill->getCommBruttoTotal()|inttoprice)|escape_latex>> \\ %everything is payed on bills, so do not bother, that is always fixed
            \hline
            <<__('Gutschrift')|escape_latex>>& <<__('€ %s', $bill->calculateVoucherAmount()|inttoprice)|escape_latex>> \\
            \hline
            \hline
        \end{longtable}
    \end{center}

    <<if $bill->calculateBillingAmount() > 0>>
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
        Die Leistung unterliegt der Bezugsteuerpflicht nach Art. 45 MWSTG.
    <</if>>

    <<include file='bill/service/include/attachment_assets.tpl'>>

<</block>>