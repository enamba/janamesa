\begin{center}
    \begin{longtable}{p{12cm}r}
        \rowcolor{black}
        \color{white}
        \textbf{<<__('Kostenstelle:')|escape_latex>> }
        \textbf{<<if is_string($bill->getOnlyCostcenter())>><<__('keine Angaben')>><<else>><<$bill->getOnlyCostcenter()->getName()>><</if>>} & \\

        \hiderowcolors
        <<__('Netto')|escape_latex>>  7\%:		& <<__('€ %s', $bill->calculateItem7()|inttoprice)|escape_latex>> \\
        <<__('Netto')|escape_latex>> 19\%:		& <<__('€ %s', $bill->calculateItem19()|inttoprice)|escape_latex>> \\
        \hline
        <<__('Summe Netto:')|escape_latex>>		& \textbf{<<__('€ %s', $bill->calculateNetto()|inttoprice)|escape_latex>>} \\ \\

        <<__('MwSt')|escape_latex>> 7\%:         & <<__('€ %s', $bill->calculateTax7()|inttoprice)|escape_latex>> \\
        <<__('MwSt')|escape_latex>> 19\%:        & <<__('€ %s', $bill->calculateTax19()|inttoprice)|escape_latex>> \\
        \hline
        <<__('Summe Mwst:')|escape_latex>>         & \textbf{<<__('€ %s', $bill->calculateTax()|inttoprice)|escape_latex>>} \\ \\


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

<<include file='bill/company/include/shortAttachment.tpl'>>

<<if $bill->verbose>>
    <<include file='bill/company/include/longAttachment.tpl'>>
<</if>>