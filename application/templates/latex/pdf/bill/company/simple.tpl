<<extends file="bill/base.tpl">>
<<block name="content">>
    
    <<$header.preamble|escape_latex>> <<__('im Zeitraum vom %s bis zum %s stellen wir Ihnen in Rechnung:',$bill->from|date_format:"%d.%m.%Y",$bill->until|date_format:"%d.%m.%Y")|escape_latex>> \\
   
    \begin{center}
        \begin{longtable}{p{12cm}r}
                \hiderowcolors
                <<__('Summe Brutto:')|escape_latex>>       & \textbf{<<__('€ %s', $bill->calculateBrutto()|inttoprice)|escape_latex>>} \\
                \hline

                <<if $bill->calculatePfandBrutto() > 0>>
                    <<__('eingereichtes Pfand Netto:')|escape_latex>>        & (<<__('€ %s', $bill->calculatePfandNetto()|inttoprice)|escape_latex>>) \\
                    <<__('eingereichtes Pfand MwSt')|escape_latex>> 19\%:    & (<<__('€ %s', $bill->calculatePfandSteuern()|inttoprice)|escape_latex>>) \\
                <</if>>

                <<if $bill->calculateDiscount() > 0>>
                    <<__('Gutschrift:')|escape_latex>>         & (<<__('€ %s', $bill->calculateDiscount()|inttoprice)|escape_latex>>) \\
                <</if>>

                \hline
                \textbf{<<__('Offener Rechnungsbetrag:')|escape_latex>>}& \textbf{<<__('€ %s', $bill->calculateOpenAmount()|inttoprice)|escape_latex>>} \\
                \hline \hline

        \end{longtable}
    \end{center}
    
    <<__('Bitte überweisen Sie den offenen Rechnungsbetrag unter Angabe der Rechnungsnummer auf unser Konto!')|escape_latex>> \\
    <<__('Eine genaue Auflistung der Leistungen entnehmen Sie bitte dem Anhang.')|escape_latex>> \\
   

    <<include file='bill/company/include/simpleShortAttachment.tpl'>>

    <<if $custom.verbose>>
        <<include file='bill/company/include/simpleLongAttachment.tpl'>>
    <</if>>

<</block>>


