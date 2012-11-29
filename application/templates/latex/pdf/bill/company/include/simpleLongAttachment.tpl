<<if count($bill->getOrdersByAttachment($custom->showEmployee)) > 0>>
    \newpage
    
    \fontsize{8}{12}\selectfont
        \begin{longtable}{  p{2.5cm}
                            p{2.0cm}
                            p{3.8cm}
                            p{4.8cm}
                            |
                            r
                            } 
        
        <<if $custom.showEmployee>>\textbf{<<__('Mitarbeiter')|escape_latex>>}<</if>> &
        \textbf{<<__('Bestellung um')|escape_latex>>} &
        \textbf{<<__('Dienstleister')|escape_latex>>} &
        \textbf{<<__('Bestellung')|escape_latex>>} &
        \textbf{<<__('Gesamt')|escape_latex>>} \\
        \hline
        
        \showrowcolors

        <<assign var=lastEmployee value=0>>
        <<assign var=totalEmployee value=0>>
        <<assign var=total value=0>>
        <<foreach from=$bill->getOrdersByAttachment() item=order>>
            <<if $custom.showEmployee>>
                <<if ($lastEmployee != 0 && $lastEmployee != $order->getCustomer()->getId())>>
                    \hiderowcolors
                    \hline
                    & & & \multicolumn{1}{|r|}{<<__('Gesamt')|escape_latex>> - <<$lastEmployeeName|escape_latex>>} &
                    \textbf{<<__('€ %s', $totalEmployee|inttoprice)|escape_latex>>} \\
                    \hline
                    \showrowcolors
                    <<assign var=totalEmployee value=0>>
                <</if>>
                <<if $lastEmployee != $order->getCustomer()->getId()>>
                    \raggedright
                    <<$order->getCustomer()->getFullname()|escape_latex>> \newline
                    <<if $order->getEmployees()>>
                        (<<$order->getEmployees()|escape_latex>>)
                    <</if>>
                <</if>>
            <</if>> &
            <<$order->getTime()|date_format:"%d.%m.%Y %H:%M">> &
            \raggedright <<$order->getService()->getName()|escape_latex>> &
            \raggedright <<foreach from=$order->getCard() item=card>>
                <<foreach from=$card item=bucket>>
                    <<foreach from=$bucket item=cBucket>>
                        <<assign var=meal value=$cBucket.meal>>
                        <<$meal->getName()|escape_latex>> <<$meal->getCurrentSizeName()|escape_latex>> \newline
                    <</foreach>>
                <</foreach>>
            <</foreach>> &
            <<assign var=currentTotal value=($order->getTotal() + $order->getServiceDeliverCost())>>
            <<__('€ %s', $currentTotal|inttoprice)|escape_latex>> \\
            <<assign var=lastEmployee value=$order->getCustomer()->getId()>>
            <<assign var=lastEmployeeName value=$order->getCustomer()->getFullname()>>
            <<assign var=totalEmployee value=$totalEmployee+$currentTotal>>
            <<assign var=total value=$total+$currentTotal>>
        <</foreach>>
        \hiderowcolors
        <<if $custom.showEmployee>>
            \hline
            & & & \multicolumn{1}{r|}{<<__('Gesamt')|escape_latex>> - <<$lastEmployeeName|escape_latex>>} &
            \textbf{<<__('€ %s', $totalEmployee|inttoprice)|escape_latex>>} \\
        <</if>>
        \hline
        \hline
        & & & \multicolumn{1}{r|}{<<__('Gesamt')|escape_latex>>} &
        \textbf{<<__('€ %s', $total|inttoprice)|escape_latex>>} \\

    \end{longtable}

    \vspace{1.5cm}

<</if>>
