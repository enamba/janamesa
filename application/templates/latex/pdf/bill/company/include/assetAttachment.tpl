<<if count($bill->getBillingAssets()) > 0>>
    \newpage

    \begin{center}
        \begin{large}
            \textbf{<<__('Rechnungsposten')|escape_latex>>}
        \end{large}
    \end{center}

    \begin{longtable}{m{4cm}|m{3cm}|m{3cm}|m{3cm}|l}
        <<__('Restaurant')|escape_latex>> &
        <<__('Datum')|escape_latex>> &
        <<__('Netto Betrag')|escape_latex>> &
        <<__('Mwst Betrag')|escape_latex>> &
        <<__('Brutto Betrag')|escape_latex>> \\
        \toprule
        \toprule
        <<foreach from=$bill->getBillingAssets() item=asset>>
            <<$asset->getService()->getName()|escape_latex>> &
            <<$asset->getTimeFrom()|date_format:"%d.%m.%Y">> &
            <<__('€ %s', $asset->getTotal()|inttoprice)|escape_latex>> &
            <<$asset->getMwst()>> \% &
            <<__('€ %s', $asset->getBrutto()|inttoprice)|escape_latex>> \\      
        <</foreach>>
    \end{longtable}
<</if>>