\begin{landscape}

    \footnotesize
    \fontsize{8}{12}
    \selectfont

    \begin{center}
        \begin{large}
            \textbf{<<__('Auflistung')|escape_latex>>}
        \end{large}
    \end{center}

    \begin{longtable}{m{6cm}m{1.5cm}|<<foreach from=$taxes item=tax>>m{1.2cm}<</foreach>>|m{1.2cm}|<<foreach from=$taxes item=tax>>m{1.2cm}<</foreach>>|m{1.2cm}|m{1.2cm}|m{1.2cm}m{1.2cm}|m{1.2cm}}

        \textbf{<<__('Firma')|escape_latex>>} &
        \textbf{<<__('Datum')|escape_latex>>} &

        <<foreach from=$taxes item=tax>>
            <<__('Netto')|escape_latex>> <<$tax>>\%: &
        <</foreach>>

        \textbf{<<__('Netto Gesamt')|escape_latex>>} &

        <<foreach from=$taxes item=tax>>
            <<__('MwSt')|escape_latex>> <<$tax>>\%: &
        <</foreach>>

        \textbf{<<__('MwSt Gesamt')|escape_latex>>} &

        \textbf{<<__('Summe Brutto')|escape_latex>>} &

        \textbf{<<__('Netto Provision')|escape_latex>> (<<__('€')|escape_latex>>)} &
        \textbf{<<__('Mwst')|escape_latex>> <<$config->tax->provision>>\% (<<__('€')|escape_latex>>)} &

        \textbf{<<__('Brutto Provision')|escape_latex>> (<<__('€')|escape_latex>>)} \\

        \hline

        \showrowcolors
        <<foreach from=$bill->getBillingAssets() item=asset>>
            
            <<$asset->getCompany()->getName()|escape_latex>> &
            <<$asset->getTimeFrom()|date_format:"%d.%m.%Y">> &

            <<foreach from=$taxes item=tax>>
                <<$asset->getItem($tax)|inttoprice>> &
            <</foreach>>
            <<$asset->getItem()|inttoprice>> &

            <<foreach from=$taxes item=tax>>
                <<$asset->getTax($tax)|inttoprice>> &
            <</foreach>>
            <<$asset->getTax()|inttoprice>> &

            <<$asset->getBrutto()|inttoprice>> &

            <<$asset->getCommission()|inttoprice:3>> &           
            <<$asset->getCommissionTax()|inttoprice:3>> &
            <<$asset->getCommissionBrutto()|inttoprice>> \\

        <</foreach>>
        \hiderowcolors

        \hline
        \hline

        & Gesamt: &

        <<foreach from=$taxes item=tax>>
            <<$bill->calculateItem($tax)|inttoprice>> &
        <</foreach>>
        <<$bill->calculateItem()|inttoprice>> &

        <<foreach from=$taxes item=tax>>
            <<$bill->calculateTax($tax)|inttoprice>> &
        <</foreach>>
        <<$bill->calculateTax()|inttoprice>> &

        <<$bill->getBrutto()|inttoprice>> &
        <<$bill->getCommTotal()|inttoprice>> &
        <<$bill->getCommTaxTotal()|inttoprice>> &
        <<$bill->getCommBruttoTotal()|inttoprice>> \\

    \end{longtable}

\end{landscape}

