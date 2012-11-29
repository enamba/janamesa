\newpage

<<if count($bill->getOrdersByPayment('bar')) > 0>>

    \footnotesize
    \fontsize{8}{12}
    \selectfont

    \begin{center}
        \begin{large}
            \textbf{<<__('Bezahlung an der Tür')|escape_latex>>}
        \end{large}
    \end{center}

    \begin{longtable}{lll|ll|l}

        <<if $bill->hasChildren()>><<__('Dienstleister')|escape_latex>><</if>>
        & &
        \textbf{<<__('Zeitpunkt')|escape_latex>>} &

        \textbf{<<__('Bar bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)} &
        \textbf{<<__('Gutschrift')|escape_latex>> (<<__('€')|escape_latex>>)} &

        \textbf{<<__('Gesamt Brutto Umsatz')|escape_latex>> (<<__('€')|escape_latex>>)} \\

        \hline

        \showrowcolors
        <<foreach from=$bill->getOrdersByPayment('bar') item=order>>

            <<if $bill->hasChildren()>><<$order->getService()->getName()|escape_latex>> : <<$order->getService()->getCustomerNr()|escape_latex>><</if>> &
            <<$order->getNr()|escape_latex>> &
            <<$order->getTime()|date_format:"%d.%m.%Y %H:%M">> &

            <<$order->getCashAmount()|inttoprice>> &
            <<$order->getDiscountAmount(false)|inttoprice>> &

            <<$bill->getBruttoAmountOfOrder($order,true)|inttoprice>> \\

        <</foreach>>
        \hiderowcolors

        \hline
        \hline

        <<if $bill->hasChildren()>> &<</if>>
        & & <<__('Gesamt:')|escape_latex>> &

        <<$bill->getCashTotal('bar')|inttoprice>> &
        <<$bill->getDiscountTotal('bar')|inttoprice>> &

        <<$bill->getBrutto('bar')|inttoprice>> \\

    \end{longtable}
<</if>>

<<if count($bill->getOrdersByPayment('online')) > 0>>

    \footnotesize
    \fontsize{8}{12}
    \selectfont

    \begin{center}
        \begin{large}
            \textbf{<<__('Onlinezahlung')|escape_latex>>}
        \end{large}
    \end{center}

    \begin{longtable}{lll|ll|l}

        <<if $bill->hasChildren()>><<__('Dienstleister')|escape_latex>><</if>>
        & &
        \textbf{<<__('Zeitpunkt')|escape_latex>>} &

        \textbf{<<__('Online bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)} &
        \textbf{<<__('Gutschrift')|escape_latex>> (<<__('€')|escape_latex>>)} &

        \textbf{<<__('Gesamt Brutto Umsatz')|escape_latex>> (<<__('€')|escape_latex>>)} \\

        \hline

        \showrowcolors
        <<foreach from=$bill->getOrdersByPayment('online') item=order>>

            <<if $bill->hasChildren()>><<$order->getService()->getName()|escape_latex>> : <<$order->getService()->getCustomerNr()|escape_latex>><</if>> &
            <<$order->getNr()|escape_latex>> &
            <<$order->getTime()|date_format:"%d.%m.%Y %H:%M">> &

            <<($order->getPayedAmount($bill->inclDeliver($order),false)-$order->getDiscountAmount(false))|inttoprice>> &
            <<$order->getDiscountAmount(false)|inttoprice>> &

            <<$bill->getBruttoAmountOfOrder($order,true)|inttoprice>> \\

        <</foreach>>
        \hiderowcolors

        \hline
        \hline

        <<if $bill->hasChildren()>> &<</if>>
        & & <<__('Gesamt:')|escape_latex>> &

        <<$bill->getPayedAmountTotal('online')|inttoprice>> &
        <<$bill->getDiscountTotal('online')|inttoprice>> &

        <<$bill->getBrutto('online')|inttoprice>> \\

    \end{longtable}
<</if>>


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

\begin{longtable}{lll|lll|l}

    & & &

    \textbf{<<__('Bar bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)} &
    \textbf{<<__('Online bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)} &
    \textbf{<<__('Gutschrift')|escape_latex>> (<<__('€')|escape_latex>>)} &

    \textbf{<<__('Gesamt Brutto Umsatz')|escape_latex>> (<<__('€')|escape_latex>>)} \\

    \hline

    \showrowcolors
    <<foreach from=array('bar','paypal','credit','bill','debit','ebanking') item=billType>>

        <<if count($bill->getOrdersByPayment($billType)) > 0>>

            & & \textbf{<<if $billType == 'bar'>><<__('Bezahlung an der Tür')>><<else>><<Default_Helpers_Human_Readable_Default::payment($billType)>><</if>>:} &
            <<$bill->getCashTotal($billType)|inttoprice>> &
            <<$bill->getPayedAmountTotal($billType)|inttoprice>> &
            <<$bill->getDiscountTotal($billType)|inttoprice>> &

            <<$bill->getBrutto($billType)|inttoprice>> \\

        <</if>>

    <</foreach>>
    \hiderowcolors

    \hline
    \hline

    & & \textbf{<<__('Gesamt:')|escape_latex>>} &

    <<$bill->getCashTotal()|inttoprice>> &
    <<$bill->getPayedAmountTotal()|inttoprice>> &
    <<$bill->getDiscountTotal()|inttoprice>> &

    <<$bill->getBrutto()|inttoprice>> \\

\end{longtable}
