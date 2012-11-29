<<extends file="bill/base.tpl">>

<<block name="content">>

    \begin{longtable}{p{12cm}r}
        \hiderowcolors
        <<__('Netto')|escape_latex>> 7\%:        & <<__('€ %s', $bill->calculateItem7()|inttoprice)|escape_latex>> \\
        \hline \\

        <<__('MwSt')|escape_latex>> 7\%:         & <<__('€ %s', $bill->calculateTax7()|inttoprice)|escape_latex>> \\
        \hline \\

        \ding{202}+\ding{203}\textbf{<<__('Summe Brutto:')|escape_latex>>}     & \textbf{<<__('€ %s', $bill->getBrutto()|inttoprice)|escape_latex>>} \\
        <<__('Provision aus offener Rechnung')|escape_latex>> \textbf{<<$bill->getNumber()>>}: & (<<__('€ %s', $bill->getCommBruttoTotal()|inttoprice)|escape_latex>>) \\
        \hline
        \textbf{<<__('Gutschrift:')|escape_latex>>} & \textbf{<<__('€ %s', $bill->calculateVoucherAmount()|inttoprice)|escape_latex>>} \\
        \hline
        \hline
    \end{longtable}

<</block>>