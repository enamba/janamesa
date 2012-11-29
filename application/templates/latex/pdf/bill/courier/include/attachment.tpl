\begin{landscape}

    \begin{longtable}{ll|l|lll}
        &
        \textbf{<<__('Zeit')|escape_latex>>} &

        \textbf{<<__('Lieferkosten')|escape_latex>> (<<__('€')|escape_latex>>)} &
        \textbf{<<__('Netto Provision')|escape_latex>> (<<__('€')|escape_latex>>)} &
        \textbf{<<__('Mwst')|escape_latex>> 19\% (<<__('€')|escape_latex>>)} &

        \textbf{<<__('Provision')|escape_latex>> (<<__('€')|escape_latex>>)} \\

        \hline

        \showrowcolors
        <<foreach from=$bill->getOrders() item=order>>

            <<$order->getNr()|escape_latex>> &
            <<$order->getTime()|date_format:"%d.%m.%Y %H:%M">> &

            <<$order->getCourierCost()|inttoprice>> &
            <<$order->getCommissionCourier()|inttoprice:5>> &
            <<$order->getCommissionTaxCourier()|inttoprice:5>> &

            <<$order->getCommissionBruttoCourier()|inttoprice>> \\

        <</foreach>>
        \hiderowcolors

        \hline
        \hline

    \end{longtable}

\end{landscape}

