<<extends file="bill/base.tpl">>
<<block name="content">>

    <<__('Für die Vermittlung von Speisen und Getränken stellen wir Ihnen in Rechnung:')|escape_latex>> \\
   
    \begin{center}
        \begin{longtable}{p{12cm}r}
                \hiderowcolors

                <<foreach from=$taxes item=tax>>
                    <<__('Netto')|escape_latex>> <<$tax>>\%:		& <<__('€ %s', $bill->calculateItem($tax)|inttoprice)|escape_latex>> \\
                <</foreach>>
                \hline
                <<__('Summe Netto:')|escape_latex>>		& \textbf{<<__('€ %s', $bill->calculateItem()|inttoprice)|escape_latex>>} \\ \\

                <<foreach from=$taxes item=tax>>
                    <<__('MwSt')|escape_latex>> <<$tax>>\%:          & <<__('€ %s', $bill->calculateTax($tax)|inttoprice)|escape_latex>> \\
                <</foreach>>
                \hline
                <<__('Summe Mwst:')|escape_latex>>         & \textbf{<<__('€ %s', $bill->calculateTax()|inttoprice)|escape_latex>>} \\ \\

                <<__('Summe Brutto:')|escape_latex>>       & \textbf{<<__('€ %s', $bill->calculateBrutto()|inttoprice)|escape_latex>>} \\

                <<if $bill->calculateDiscount() > 0>>
                    \hline
                    <<__('Rabatt:')|escape_latex>>       & \textbf{(<<__('€ %s', $bill->calculateDiscount()|inttoprice)|escape_latex>>)} \\
                <</if>>

                \hline
                <<__('Rechnungsbetrag:')|escape_latex>> & \textbf{<<__('€ %s', ($bill->calculateBrutto() - $bill->calculateDiscount())|inttoprice)|escape_latex>>} \\


        \end{longtable}
    \end{center}

    <<if $order->getPayment() == 'credit'>>
        <<__('Die Bestellung wurde bereits mit Kreditkarte bezahlt')|escape_latex>>
    <<elseif $order->getPayment() == 'paypal'>>
        <<__('Die Bestellung wurde bereits mit Paypal bezahlt')|escape_latex>>
    <<elseif $order->getPayment() == 'debit'>>
        <<__('Die Bestellung wurde bereits per Lastschrift bezahlt')|escape_latex>>
    <<elseif $order->getPayment() == 'debit'>>
        <<__('Die Bestellung wurde bereits per Rechnung beglichen')|escape_latex>>
    <</if>>

    %booktabs settings
    \newcolumntype{V}[1]{
      >{\bfseries\huge}p{#1}
    } 							%tabellenüberschriften
    \newcolumntype{T}[1]{
      >{\bfseries\large}p{#1}
    } 							%tabellenüberschriften
    \newcolumntype{v}[1]{
      >{\raggedright}p{#1}
    } 							%verwende v{Xcm} für linksbündige Spalten fester Breite
    \newcolumntype{w}[1]{
      >{\raggedleft}p{#1}
    } 							%verwende v{Xcm} für rechtsbündige Spalten fester Breite
    \newcolumntype{x}[1]{
      >{\centering}p{#1}
    } 		

    <<foreach from=$bill->getOrders() item=order>>

        \newpage

        % ------- Start Order
            \begin{longtable}{x{0.9cm}v{9cm}v{1cm}w{2cm}r}
                    \multicolumn{5}{V{\textwidth}}{Bestellung <<$order->getNr()|escape_latex>>} \\
                    \hiderowcolors
                    \toprule[1pt]

                    % Description of service
                    \multicolumn{5}{v{\textwidth}}{
            <<$order->getService()->getName()|escape_latex>>,
            <<$order->getService()->getStreet()|escape_latex>> <<$order->getService()->getHausnr()|escape_latex>>,
            <<$order->getService()->getPlz()|escape_latex>>,
            <<__('Fax:')|escape_latex>> <<$order->getService()->getFax()|default:'k.A.'|escape_latex>>,
            <<__('Tel:')|escape_latex>> <<$order->getService()->getTel()|default:'k.A.'|escape_latex>>,
            <<__('Kdnnr:')|escape_latex>> <<$order->getService()->getCustomerNr()|escape_latex>>} \\
            \addlinespace[7mm]

            \textbf{<<__('Anzahl')|escape_latex>>}	& \textbf{<<__('Artikel & Zutaten')|escape_latex>>}	& \textbf{<<__('Art.Nr')|escape_latex>>}	& \textbf{<<__('Einzelpreis')|escape_latex>>}	& \textbf{<<__('Summe')|escape_latex>>} \\
            \midrule
            <<foreach from=$order->getCard() item=card>>
                <<foreach from=$card item=bucket>>
                    <<foreach from=$bucket item=cBucket>>
                        <<assign var=meal value=$cBucket.meal>>
                        %item
                        <<assign var=mealTotal value=$meal->getCost()>>
                        \large{\textbf{<<$cBucket.count>> x}} &
                        <<$meal->getName()|escape_latex>> <<$meal->getCurrentSizeName()|escape_latex>> <<$meal->getDescription()|escape_latex>>
                        <<foreach from=$meal->getCurrentExtras() item=extra>>
                            <<assign var=mealTotal value=$mealTotal+$extra->getCost()>>
                            \textbf{<<$extra->getName()|escape_latex>> <<if $extra->getCost() > 0>>(<<__('€ %s', ($cBucket.count * $extra->getCost())|inttoprice)|escape_latex>>)<</if>>},
                        <</foreach>>
                        <<foreach from=$meal->getCurrentOptions() item=option>>
                            <<assign var=mealTotal value=$mealTotal+$option->getCost()>>
                            \textbf{<<$option->getName()|escape_latex>> <<if $option->getCost() > 0>>(<<__('€ %s', ($cBucket.count * $option->getCost())|inttoprice)|escape_latex>>),<</if>>}
                        <</foreach>>
                        & <<$meal->getNr()>> & <<__('€ %s', $meal->getCost()|inttoprice)|escape_latex>> & <<__('€ %s', ($mealTotal * $cBucket.count)|inttoprice)|escape_latex>> \\
                        & & & &  \\
                        <<if $meal->getSpecial() != "">>
                            & \textbf{<<__('Kundenhinweis:')|escape_latex>> <<$meal->getSpecial()|escape_latex>>} & & \\
                        <</if>>
                        \midrule
                    <</foreach>>
                <</foreach>>
            <</foreach>>

            <<if $order->getDeliverCost() > 0>>
                && \multicolumn{2}{r}{\textbf{<<__('Lieferkosten:')|escape_latex>>}} & \textbf{<<__('€ %s', $order->getDeliverCost()|inttoprice)|escape_latex>>} \\
            <</if>>

            <<foreach from=$taxes item=tax>>
                <<if $order->getTax($tax)>0>>
                    && \multicolumn{2}{r}{\textbf{<<__('inklusive MwSt %s%%:', $tax)|escape_latex>>}} & \textbf{<<__('€ %s', $order->getTax($tax)|inttoprice)|escape_latex>>} \\
                <</if>>
            <</foreach>>

            && \multicolumn{2}{r}{} & \\
            && \multicolumn{2}{r}{\textbf{<<__('Gesamtbetrag:')|escape_latex>>}} & \textbf{<<__('€ %s', $order->getAbsTotal(false, false, true)|inttoprice)|escape_latex>>} \\

            <<if $order->getPayedAmount() > 0>>
                && \multicolumn{2}{r}{\textbf{<<__('Bereits bezahlt:')|escape_latex>>}} & \textbf{<<__('€ %s', $order->getPayedAmount()|inttoprice)|escape_latex>>} \\
            <</if>>

        \end{longtable}

    <</foreach>>


<</block>>


