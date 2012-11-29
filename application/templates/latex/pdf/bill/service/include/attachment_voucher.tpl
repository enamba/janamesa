\begin{landscape}

    <<foreach from=array('bar','paypal','credit','bill','debit','ebanking') item=billType>>

        <<if count($bill->getOrdersByPayment($billType)) > 0>>

            \footnotesize
            \fontsize{8}{12}
            \selectfont

            \begin{center}
                \begin{large}
                    \textbf{<<__('Bezahlung per %s',Default_Helpers_Human_Readable_Default::payment($billType))|escape_latex>>}
                \end{large}
            \end{center}

            \begin{longtable}{p{1.5cm}p{2cm}|<<foreach from=$taxes item=tax>>p{1.8cm}<</foreach>>|p{1.8cm}|<<foreach from=$taxes item=tax>>p{1.8cm}<</foreach>>|p{1.8cm}|p{1.8cm}p{1.8cm}}

                &
                \textbf{<<__('Zeitpunkt')|escape_latex>>} &

                <<foreach from=$taxes item=tax>>
                    <<__('Netto')|escape_latex>> <<$tax>>\%: &
                <</foreach>>

                \textbf{<<__('Netto Gesamt')|escape_latex>>} &

                <<foreach from=$taxes item=tax>>
                    <<__('MwSt')|escape_latex>> <<$tax>>\%: &
                <</foreach>>

                \textbf{<<__('MwSt Gesamt')|escape_latex>>} &

                \textbf{<<__('Summe Brutto')|escape_latex>>} &

                \textbf{<<__('Bar bezahlt')|escape_latex>>} \\

                \hline

                \showrowcolors
                <<foreach from=$bill->getOrdersByPayment($billType) item=order>>

                    <<$order->getNr()|escape_latex>> &
                    <<$order->getTime()|date_format:"%d.%m.%Y %H:%M">> &

                    <<foreach from=$taxes item=tax>>
                        <<$order->getItem($tax, $bill->inclDeliver($order), false)|inttoprice:3>> &
                    <</foreach>>
                    <<$order->getItem(1000, $bill->inclDeliver($order), false)|inttoprice:3>> &

                    <<foreach from=$taxes item=tax>>
                        <<$order->getTax($tax, $bill->inclDeliver($order), false)|inttoprice:3>> &
                    <</foreach>>
                    <<$order->getTax(1000, $bill->inclDeliver($order), false)|inttoprice:3>> &

                    <<$bill->getBruttoAmountOfOrder($order)|inttoprice>> &
                    <<$order->getCashAmount()|inttoprice>> \\

                <</foreach>>
                \hiderowcolors

                \hline
                \hline

                & <<__('Gesamt:')|escape_latex>> &

                <<foreach from=$taxes item=tax>>
                    <<$bill->calculateItem($billType,$tax)|inttoprice>> &
                <</foreach>>
                <<$bill->calculateItem($billType)|inttoprice>> &

                <<foreach from=$taxes item=tax>>
                    <<$bill->calculateTax($billType,$tax)|inttoprice>> &
                <</foreach>>
                <<$bill->calculateTax($billType)|inttoprice>> &

                <<$bill->getBrutto($billType)|inttoprice>> &
                <<$bill->getCashTotal($billType)|inttoprice>> \\

            \end{longtable}
        <</if>>

    <</foreach>>

    %%%%%%%%%%%%%%%%%%
    %sum up everything

    \footnotesize
    \fontsize{8}{12}
    \selectfont

    \begin{center}
        \begin{large}
            \textbf{<<__('Zusammenfassung')|escape_latex>>}
        \end{large}
    \end{center}

    \begin{longtable}{p{1cm}p{2cm}|<<foreach from=$taxes item=tax>>p{1.8cm}<</foreach>>|p{1.8cm}|<<foreach from=$taxes item=tax>>p{1.8cm}<</foreach>>|p{1.8cm}|p{1.8cm}p{1.8cm}}
        
        &
        &

        <<foreach from=$taxes item=tax>>
            <<__('Netto')|escape_latex>> <<$tax>>\%: &
        <</foreach>>

        \textbf{<<__('Netto Gesamt')|escape_latex>>} &

        <<foreach from=$taxes item=tax>>
            <<__('MwSt')|escape_latex>> <<$tax>>\%: &
        <</foreach>>

        \textbf{<<__('MwSt Gesamt')|escape_latex>>} &

        \textbf{<<__('Summe Brutto')|escape_latex>>} &

        \textbf{<<__('Bar bezahlt')|escape_latex>>} \\

        \hline


        \showrowcolors
        <<foreach from=array('bar','paypal','credit','bill','debit','ebanking') item=billType>>

            <<if count($bill->getOrdersByPayment($billType)) > 0>>

                & \textbf{<<Default_Helpers_Human_Readable_Default::payment($billType)>>:} &

                <<foreach from=$taxes item=tax>>
                    <<$bill->calculateItem($billType, $tax)|inttoprice>> &
                <</foreach>>
                <<$bill->calculateItem($billType)|inttoprice>> &

                <<foreach from=$taxes item=tax>>
                    <<$bill->calculateTax($billType, $tax)|inttoprice>> &
                <</foreach>>
                <<$bill->calculateTax($billType)|inttoprice>> &
                
                <<$bill->getBrutto($billType)|inttoprice>> &
                <<$bill->getCashTotal($billType)|inttoprice>> \\

            <</if>>

        <</foreach>>
        \hiderowcolors

        \hline
        \hline

        & \textbf{<<__('Gesamt:')|escape_latex>>} &

        <<foreach from=$taxes item=tax>>
            <<$bill->calculateItem('all', $tax)|inttoprice>> &
        <</foreach>>
        <<$bill->calculateItem()|inttoprice>> &

        <<foreach from=$taxes item=tax>>
            <<$bill->calculateTax('all', $tax)|inttoprice>> &
        <</foreach>>
        <<$bill->calculateTax()|inttoprice>> &

        <<$bill->getBrutto()|inttoprice>> &
        <<$bill->getCashTotal()|inttoprice>> \\

    \end{longtable}

\end{landscape}

