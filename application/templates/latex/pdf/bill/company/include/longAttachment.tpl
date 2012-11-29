<<if count($bill->getOrdersByAttachment()) > 0>>
\begin{landscape}

    <<foreach from=$bill->getOrdersByAttachment() item=order>>
        \fontsize{6}{6}\selectfont
            \begin{longtable}{  m{2.0cm}
                                m{1.0cm}
                                m{2.5cm}
                                m{2.0cm}
                                m{2.0cm}
                                m{1.5cm}
                                m{0.8cm}
                                m{1.2cm}
                                m{1.2cm}
                                m{1.2cm}
                                l}

            <<if $custom.showEmployee>>\textbf{<<__('Mitarbeiter')|escape_latex>>}<</if>> &
            \textbf{<<__('Bestellart')|escape_latex>>} &
            \textbf{<<__('Dienstleister')|escape_latex>>} &
            \textbf{<<__('Bestellung um')|escape_latex>>} &
            \textbf{<<__('Lieferung um')|escape_latex>>} &
            \textbf{<<__('Lieferkosten')|escape_latex>>} &
            \textbf{<<__('Gesamt')|escape_latex>>} &
            \textbf{<<__('Netto')|escape_latex>> 19\%} &
            \textbf{<<__('Netto')|escape_latex>> 7\%} &
            \textbf{<<__('MwSt')|escape_latex>> 19\%} &
            \textbf{<<__('MwSt')|escape_latex>> 7\%} \\ [0.5ex]
            \toprule

            <<if $custom.showEmployee>>
                \raggedright
                <<$order->getEmployees()|escape_latex>>
            <</if>> &
            <<$order->getMode()|typeToReadable|escape_latex>> &
            <<$order->getService()->getName()|escape_latex>> : <<$order->getService()->getCustomerNr()|escape_latex>> &
            <<$order->getTime()|date_format:"%d.%m.%Y %H:%M">> &
            <<if $order->getTime() >= $order->getDeliverTime()>>
                <<__('sofort')|escape_latex>> &
            <<else>>
                <<$order->getDeliverTime()|date_format:"%d.%m.%Y %H:%M">> &
            <</if>>
            <<__('€ %s', $order->getServiceDeliverCost()|inttoprice)|escape_latex>> &
            <<__('€ %s', ($order->getTotal() + $order->getServiceDeliverCost())|inttoprice)|escape_latex>> &
            <<__('€ %s', $order->getItem19()|inttoprice)|escape_latex>> &
            <<__('€ %s', $order->getItem7()|inttoprice)|escape_latex>> &
            <<__('€ %s', $order->getTax19()|inttoprice)|escape_latex>> &
            <<__('€ %s', $order->getTax7()|inttoprice)|escape_latex>> \\
            \\
            \toprule
            \toprule
            \multicolumn{4}{m{7.5cm}}{\textbf{<<__('Artikel')|escape_latex>>}}
            & 
            & \textbf{<<__('Anzahl')|escape_latex>>}
            & \textbf{<<__('Brutto')|escape_latex>>}
            & \textbf{<<__('Netto')|escape_latex>> 19\%}
            & \textbf{<<__('Netto')|escape_latex>> 7\%}
            & \textbf{<<__('MwSt')|escape_latex>> 19\%}
            & \textbf{<<__('MwSt')|escape_latex>> 7\%} \\
            <<foreach from=$order->getCard() item=card>>
                <<foreach from=$card item=bucket>>
                    <<foreach from=$bucket item=cBucket>>
                        <<assign var=meal value=$cBucket.meal>>
                        \multicolumn{4}{m{7.5cm}}{<<$meal->getName()|escape_latex>> <<$meal->getCurrentSizeName()|escape_latex>> <<$meal->getDescription()|escape_latex>>}
                        && <<$cBucket.count>>
                        & <<__('€ %s', ($meal->getAllCosts() * $cBucket.count)|inttoprice)|escape_latex>>
                        & <<__('€ %s', ($meal->getItem19() * $cBucket.count)|inttoprice)|escape_latex>>
                        & <<__('€ %s', ($meal->getItem7() * $cBucket.count)|inttoprice)|escape_latex>>
                        & <<__('€ %s', ($meal->getTax19() * $cBucket.count)|inttoprice)|escape_latex>>
                        & <<__('€ %s', ($meal->getTax7() * $cBucket.count)|inttoprice)|escape_latex>> \\
                    <</foreach>>
                <</foreach>>
            <</foreach>>

        \end{longtable}

        \vspace{1.5cm}

    <</foreach>>
\end{landscape}
<</if>>
