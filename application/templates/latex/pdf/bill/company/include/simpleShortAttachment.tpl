<<if count($bill->getOrders()) > 0 || count($bill->getBillingAssets())>>
    \newpage
    
    \fontsize{8}{12}
    \selectfont
    \begin{longtable}{<<if $custom.showEmployee>>p{4.20cm}<</if>>p{4.50cm}p{3.4cm}|
                      r|
                      <<if $bill->calculatePfand() > 0>>p{0.8cm}<</if>>
                      <<if $bill->calculateDiscount() > 0>>p{0.8cm}<</if>>
                      <<if $custom.showProject || $custom.showCostcenter>>|p{4cm}<</if>>}

        <<if $custom.showEmployee>>{\textbf{<<__('Mitarbeiter')|escape_latex>>}} &<</if>>
        \textbf{<<__('Dienstleister')|escape_latex>>} &
        \textbf{<<__('Datum')|escape_latex>>} &

        \textbf{<<__('Gesamt')|escape_latex>>}

        <<if $bill->calculatePfand() > 0>>& \textbf{<<__('eingreichtes Pfand Brutto')|escape_latex>>}<</if>>
        <<if $bill->calculateDiscount() > 0>>& \textbf{<<__('Gutschrift')|escape_latex>>} <</if>>
        <<if $custom.showProject || $custom.showCostcenter>>& \textbf{<<__('Projekt / Kostenstelle')|escape_latex>>}<</if>> \\ [0.5ex]
        \hline

        \showrowcolors
        <<foreach from=$bill->getAttachment($bill->getOnlyProject(),$bill->getOnlyCostcenter(),true) item=row name=list>>
            <<if $custom.showEmployee>><<$row.Mitarbeiter|escape_latex>> &<</if>>
            <<$row.Dienstleister|escape_latex>> &
            <<__('Bestellt:')|escape_latex>> <<$row.Bestellung_um|escape_latex>> \newline
            <<__('Lieferung:')|escape_latex>> <<$row.Lieferung_um|escape_latex>> &

            <<__('€ %s', $row.Brutto_Summe|inttoprice)|escape_latex>>

            <<if $bill->calculatePfand()>0>>& <<$row.Pfand|inttoprice|escape_latex>><</if>>
            <<if $bill->calculateDiscount() > 0>>& <<$row.Discount|inttoprice|escape_latex>><</if>>

            <<if $custom.showProject || $custom.showCostcenter>>& <<$row.Projekt|escape_latex>> \newline <<$row.Kostenstelle|escape_latex>><</if>>\\          
        <</foreach>>
        \hiderowcolors

        \hline
        <<if $custom.showEmployee>>&<</if>>
        &&

        \textbf{<<__('€ %s', $bill->calculateBruttoSumme()|inttoprice)|escape_latex>>}
        <<if $bill->calculatePfand() > 0>>& <<__('€ %s', $bill->calculatePfand()|inttoprice)|escape_latex>><</if>>
        <<if $bill->calculateDiscount() > 0>>& <<__('€ %s', $bill->calculateDiscount()|inttoprice)|escape_latex>> <</if>>
        <<if $custom.showProject || $custom.showCostcenter>>&<</if>> \\

    \end{longtable}

<</if>>