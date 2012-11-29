<<extends file="bill/base.tpl">>

<<block name="content">>

   <<__('Für die Vermittlung von Kurierdiensten im Zeitraum vom %s bis zum %s stellen wir Ihnen in Rechnung:',$bill->from|date_format:"%d.%m.%Y",$bill->until|date_format:"%d.%m.%Y")|escape_latex>> \\

    \begin{center}
        \begin{longtable}{p{12cm}r}
            \hiderowcolors
            <<__('Brutto Umsatz:')|escape_latex>>		& <<__('€ %s', $bill->getBrutto()|inttoprice)|escape_latex>> \\

            \hline
            <<__('Vertraglich festgelegt Provision %s %% vom Netto Umsatz:',$courier->getCommission())|escape_latex>> & <<__('€ %s', $bill->calculateCommission()|inttoprice)|escape_latex>> \\
         
            <<__('MwSt')|escape_latex>> 19\%:         & <<__('€ %s', $bill->getCommTaxTotal()|inttoprice)|escape_latex>> \\
            \hline
            \ding{203} <<__('Brutto Provision:')|escape_latex>>  & <<__('€ %s', $bill->getCommBruttoTotal()|inttoprice)|escape_latex>> \\
            \hline
            <<__('Bereits gezahlt')|escape_latex>> & <<__('€ %s', $bill->getCommBruttoTotal()|inttoprice)|escape_latex>> \\
            <<__('Offener Rechnungsbetrag')|escape_latex>> & <<__('€ %s', $bill->calculateBillingAmount()|inttoprice)|escape_latex>> \\
            \hline
            \hline
        \end{longtable}
    \end{center}

    <<if $bill->calculateBillingAmount() > 0>>
        <<__('Bitte überweisen Sie den offenen Rechnungsbetrag unter Angabe der Rechnungsnummer auf unser Konto! Eine genaue Auflistung der Leistungen entnehmen Sie bitte dem Anhang. Die Zahlung ist sofort fällig.')|escape_latex>>
    <</if>>

    <<include file='bill/courier/include/attachment.tpl'>>

<</block>>
