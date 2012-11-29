<<if count($bill->getOrders()) > 0 || count($bill->getBillingAssets())>>
\begin{landscape}

    \fontsize{8}{12}
    \selectfont
    \begin{longtable}{<<if $custom.showEmployee>>m{3cm}<</if>>m{2cm}m{3.4cm}|
                      m{0.8cm}m{0.8cm}m{0.8cm}|
                      m{0.8cm}m{0.8cm}m{0.8cm}|
                      m{0.8cm}|
                      <<if $bill->calculatePfand() > 0>>m{0.8cm}<</if>>
                      <<if $bill->calculateDiscount() > 0>>m{0.8cm}<</if>>
                      <<if $custom.showProject || $custom.showCostcenter>>|m{4cm}<</if>>}

        <<if $custom.showEmployee>>{\textbf{<<__('Mitarbeiter')|escape_latex>>}} &<</if>>
        \textbf{<<__('Dienstleister')|escape_latex>>} &
        \textbf{<<__('Datum')|escape_latex>>} &

        \textbf{<<__('Netto')|escape_latex>> 7\%} &
        \textbf{<<__('Netto')|escape_latex>> 19\%} &
        \textbf{<<__('Summe Netto')|escape_latex>>} &
        \textbf{<<__('MwSt')|escape_latex>> 7\%} &
        \textbf{<<__('MwSt')|escape_latex>> 19\%} &
        \textbf{<<__('Summe Mwst')|escape_latex>>} &
        \textbf{<<__('Summe Brutto')|escape_latex>>}

        <<if $bill->calculatePfand() > 0>>& \textbf{<<__('eingreichtes Pfand Brutto')|escape_latex>>}<</if>>
        <<if $bill->calculateDiscount() > 0>>& \textbf{<<__('Gutschrift')|escape_latex>>} <</if>>
        <<if $custom.showProject || $custom.showCostcenter>>& \textbf{<<__('Projekt / Kostenstelle')|escape_latex>>}<</if>> \\ [0.5ex]
        \hline

        \showrowcolors
        <<foreach from=$bill->getAttachment($bill->getOnlyProject(),$bill->getOnlyCostcenter(),true) item=row name=list>>
            <<if $custom.showEmployee>><<$row.Mitarbeiter|escape_latex>> (<<$row.Nr>>) &<</if>>
            <<$row.Dienstleister|escape_latex>> (<<$row.DienstleisterKundennummer|escape_latex>>) &
            <<__('Bestellt:')|escape_latex>> <<$row.Bestellung_um|escape_latex>> \newline
            <<__('Lieferung:')|escape_latex>> <<$row.Lieferung_um|escape_latex>> &

            <<$row.Netto_7|inttoprice:3|escape_latex>> &
            <<$row.Netto_19|inttoprice:3|escape_latex>> &
            <<($row.Netto_19+$row.Netto_7)|inttoprice:3|escape_latex>> &

            <<$row.Steuern_7|inttoprice:3|escape_latex>> &
            <<$row.Steuern_19|inttoprice:3|escape_latex>> &
            <<($row.Steuern_19+$row.Steuern_7)|inttoprice:3|escape_latex>> &

            <<$row.Brutto_Summe|inttoprice|escape_latex>>

            <<if $bill->calculatePfand()>0>>& <<$row.Pfand|inttoprice|escape_latex>><</if>>
            <<if $bill->calculateDiscount() > 0>>& <<$row.Discount|inttoprice|escape_latex>><</if>>

            <<if $custom.showProject || $custom.showCostcenter>>& <<$row.Projekt|escape_latex>> \newline <<$row.Kostenstelle|escape_latex>><</if>>\\          
        <</foreach>>
        \hiderowcolors

        \hline
        <<if $custom.showEmployee>>&<</if>>
        &&

        <<$bill->calculateNetto7()|inttoprice:2>> &
        <<$bill->calculateNetto19()|inttoprice:2>> &
        <<($bill->calculateNetto19()+$bill->calculateNetto7())|inttoprice:2>> &
        <<$bill->calculateTax7()|inttoprice:2>> &
        <<$bill->calculateTax19()|inttoprice:2>> &
        <<($bill->calculateTax7()+$bill->calculateTax19())|inttoprice:2>> &
        <<$bill->calculateBruttoSumme()|inttoprice>>
        <<if $bill->calculatePfand() > 0>>& <<__('€ %s', $bill->calculatePfand()|inttoprice)|escape_latex>><</if>>
        <<if $bill->calculateDiscount() > 0>>& <<__('€ %s', $bill->calculateDiscount()|inttoprice)|escape_latex>> <</if>>
        <<if $custom.showProject || $custom.showCostcenter>>&<</if>> \\

    \end{longtable}

\end{landscape}
<</if>>