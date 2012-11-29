\newpage
\begin{landscape}

    <<foreach from=array('bar','paypal','credit','bill','debit','ebanking') item=billType>>

        <<if count($bill->getOrdersByPayment($billType)) > 0>>

            \footnotesize
            \fontsize{8}{12}
            \selectfont

            \begin{center}
                \begin{large}
                    \textbf{<<__('Bezahlung per %s', Default_Helpers_Human_Readable_Default::payment($billType))|escape_latex>>}
                \end{large}
            \end{center}
            
            \begin{longtable}{lll|lll|lll|ll|l|l<<if $config->tax->provision > 0>>l|l<</if>>|l}
                
                <<if $bill->hasChildren()>>\begin{sideways}<<__('Dienstleister')|escape_latex>>\end{sideways}<</if>>
                & &
                \begin{sideways}\textbf{<<__('Zeitpunkt')|escape_latex>>}\end{sideways} &

                \begin{sideways}\textbf{<<__('Bar bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                <<if $billType != "bar">>\begin{sideways}\textbf{<<Default_Helpers_Human_Readable_Default::payment($billType)>> <<__('bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways}<</if>> &
                \begin{sideways}\textbf{<<__('Gutschrift von %s', $config->domain->base)|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &

                \begin{sideways}\textbf{<<__('Gesamt Brutto Umsatz')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                \begin{sideways}\textbf{<<__('enthaltene Lieferkosten')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                \begin{sideways}\textbf{<<__('verkauftes Pfand')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                
                \begin{sideways}\textbf{<<__('Netto Transaktionskosten')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                \begin{sideways}\textbf{<<__('Netto Provision')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                
                <<if $config->tax->provision > 0>>
                    \begin{sideways}\textbf{<<__('Summe Netto')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                    \begin{sideways}\textbf{<<__('Mwst')|escape_latex>> <<$config->tax->provision>>\% (<<__('€')|escape_latex>>)}\end{sideways} &
                    \begin{sideways}\textbf{<<__('Summe Brutto')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                <</if>>
                
                \begin{sideways}\textbf{<<__('Pfand erhalten')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
                
                \begin{sideways}\textbf{<<__('Domain')|escape_latex>>}\end{sideways} \\
                
                \hline

                \showrowcolors
                <<foreach from=$bill->getOrdersByPayment($billType) item=order>>

                    <<if $bill->hasChildren()>><<$order->getService()->getName()|escape_latex>> : <<$order->getService()->getCustomerNr()|escape_latex>><</if>> &
                    <<$order->getNr()|escape_latex>> &
                    <<$order->getTime()|date_format:"%d.%m.%Y %H:%M">> &

                    <<$order->getCashAmount()|inttoprice>> &
                    <<if $billType != "bar">><<($order->getPayedAmount($bill->inclDeliver($order),false)-$order->getDiscountAmount(false))|inttoprice>><</if>> &
                    <<$order->getDiscountAmount(false)|inttoprice>> &

                    <<$bill->getBruttoAmountOfOrder($order,true)|inttoprice>> &
                    <<$order->getServiceDeliverCost()|inttoprice>> &
                    <<$order->getSoldPfand()|inttoprice>> &

                    <<$order->getCharge()|inttoprice>> &
                    <<$order->getCommission()|inttoprice>> &
                    
                    <<if $config->tax->provision > 0>>
                        <<($order->getCharge()+$order->getCommission())|inttoprice:3>> &
                        <<($order->getCommissionTax()+($order->getCharge()/100*$config->tax->provision))|inttoprice:3>> &
                        <<($order->getCommissionBrutto()+$order->getCharge()+($order->getCharge()/100*$config->tax->provision))|inttoprice:3>> &
                    <</if>>
                    
                    <<$order->getPfand()|inttoprice>> &
                    
                    <<$order->getDomain()|default:$DOMAIN_BASE|escape_latex>> \\

                <</foreach>>
                \hiderowcolors

                \hline
                \hline

                <<if $bill->hasChildren()>> &<</if>>
                & & <<__('Gesamt:')|escape_latex>> &
                
                <<$bill->getCashTotal($billType)|inttoprice>> &
                <<if $billType != "bar">><<$bill->getPayedAmountTotal($billType)|inttoprice>><</if>> &
                <<$bill->getDiscountTotal($billType)|inttoprice>> &
                
                <<$bill->getBrutto($billType)|inttoprice>> &
                <<$bill->getDeliverCostTotal($billType)|inttoprice>> &
                <<$bill->getSoldPfandTotal($billType)|inttoprice>> &
                
                <<$bill->calculateTransactionCost($billType)|inttoprice>> &
                <<$bill->getCommTotal($billType)|inttoprice>> &
                              
                <<if $config->tax->provision > 0>>
                    <<($bill->getCommTotal($billType)+$bill->calculateTransactionCost($billType))|inttoprice>> &
                    <<($bill->getCommTaxTotal($billType)+($bill->calculateTransactionCost($billType)/100*$config->tax->provision))|inttoprice:5>> &
                    <<($bill->getCommBruttoTotal($billType)+$bill->calculateTransactionCostBrutto($billType))|inttoprice:5>> &
                <</if>>
                
                <<$bill->getPfandTotal($billType)|inttoprice>> \\
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

    \begin{longtable}{lll|lll|lll|ll|l|l<<if $config->tax->provision > 0>>l|l<</if>>|l}
                
        & & &

        \begin{sideways}\textbf{<<__('Bar bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
        <<if $billType != "bar">>\begin{sideways}\textbf{<<Default_Helpers_Human_Readable_Default::payment($billType)>> <<__('bezahlt')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways}<</if>> &
        \begin{sideways}\textbf{<<__('Gutschrift von %s', $config->domain->base)|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &

        \begin{sideways}\textbf{<<__('Gesamt Brutto Umsatz')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
        \begin{sideways}\textbf{<<__('enthaltene Lieferkosten')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
        \begin{sideways}\textbf{<<__('verkauftes Pfand')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &

        \begin{sideways}\textbf{<<__('Netto Transaktionskosten')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
        \begin{sideways}\textbf{<<__('Netto Provision')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &

        <<if $config->tax->provision > 0>>
            \begin{sideways}\textbf{<<__('Summe Netto')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
            \begin{sideways}\textbf{<<__('Mwst')|escape_latex>> <<$config->tax->provision>>\% (<<__('€')|escape_latex>>)}\end{sideways} &
            \begin{sideways}\textbf{<<__('Summe Brutto')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} &
        <</if>>

        \begin{sideways}\textbf{<<__('Pfand erhalten')|escape_latex>> (<<__('€')|escape_latex>>)}\end{sideways} \\

        \hline

        \showrowcolors
        <<foreach from=array('bar','paypal','credit','bill','debit','ebanking') item=billType>>

            <<if count($bill->getOrdersByPayment($billType)) > 0>>

                & & \textbf{<<Default_Helpers_Human_Readable_Default::payment($billType)>>:} &
                <<$bill->getCashTotal($billType)|inttoprice>> &
                <<$bill->getPayedAmountTotal($billType)|inttoprice>> &
                <<$bill->getDiscountTotal($billType)|inttoprice>> &
                
                <<$bill->getBrutto($billType)|inttoprice>> &
                <<$bill->getDeliverCostTotal($billType)|inttoprice>> &
                <<$bill->getSoldPfandTotal($billType)|inttoprice>> &
                
                <<$bill->calculateTransactionCost($billType)|inttoprice>> &
                <<$bill->getCommTotal($billType)|inttoprice>> &
                              
                <<if $config->tax->provision > 0>>
                    <<($bill->getCommTotal($billType)+$bill->calculateTransactionCost($billType))|inttoprice>> &
                    <<($bill->getCommTaxTotal($billType)+($bill->calculateTransactionCost($billType)/100*$config->tax->provision))|inttoprice:5>> &
                    <<($bill->getCommBruttoTotal($billType)+$bill->calculateTransactionCostBrutto($billType))|inttoprice:5>> &
                <</if>>
                
                <<$bill->getPfandTotal($billType)|inttoprice>> \\

            <</if>>

        <</foreach>>
        \hiderowcolors

        \hline
        \hline

        & & \textbf{<<__('Gesamt:')|escape_latex>>} &
        
        <<$bill->getCashTotal()|inttoprice>> &
        <<$bill->getPayedAmountTotal()|inttoprice>> &
        <<$bill->getDiscountTotal()|inttoprice>> &
        
        <<$bill->getBrutto()|inttoprice>> &
        <<$bill->getDeliverCostTotal()|inttoprice>> &
        <<$bill->getSoldPfandTotal()|inttoprice>> &
        
        <<$bill->calculateTransactionCost()|inttoprice>> &
        <<$bill->getCommTotal()|inttoprice>> &
        
        <<if $config->tax->provision > 0>>
            <<($bill->getCommTotal()+$bill->calculateTransactionCost())|inttoprice>> &
            <<($bill->getCommTaxTotal()+($bill->calculateTransactionCost()/100*$config->tax->provision))|inttoprice:5>> &
            <<($bill->getCommBruttoTotal()+$bill->calculateTransactionCostBrutto())|inttoprice:5>> &
        <</if>>
        
        <<$bill->getPfandTotal()|inttoprice>> \\

    \end{longtable}

\end{landscape}

