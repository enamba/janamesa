%Base Template for billings
%%%%%%%%%%%%%%%%%%%%%%%%%%%


\documentclass[12pt, german]{g-brief}

\usepackage[utf8]{inputenc}
\usepackage[german]{babel}
\usepackage[T1]{fontenc}
\usepackage{times}
\usepackage{lscape,mathptmx,multirow, graphicx, fancyhdr, booktabs,array,colortbl, longtable}
\usepackage{pdfpages}
\usepackage{pifont}
\usepackage{eurosym}

\fenstermarken
\Name                {Yourdelivery GmbH}
\Strasse             {Chausseestra{\ss}e 86}
\Ort                 {D-10115 Berlin}
\Telefon             {Jörg Gerbig, Kai Hansen, Christoph Gerber}
\Telefax             {AG Charlottenburg}
\HTTP                {HRB 118099 B}
\EMail               {DE 266464862}
\Bank                {Deutsche Bank Berlin}
\BLZ                 {100 701 24}
\Konto               {11 21 32 600}
\RetourAdresse       {yourdelivery GmbH Chausseestra{\ss}e 86 10115 Berlin}

\renewcommand{\telefontex}{{\footnotesize}}
\renewcommand{\telefaxtext}{{\footnotesize}}
\renewcommand{\httptext}{{\footnotesize}}
\renewcommand{\emailtext}{{\footnotesize}}

\Adresse{
    <<$header.heading|escape_latex>> \\
    <<$header.street|escape_latex>> <<$header.hausnr|escape_latex>> \\
    <<$header.plz|escape_latex>> <<$header.city|escape_latex>><<block name="addrAdd">><</block>>}

\Betreff{<<$this->getHeading()>>}
\Datum{\today}
\Anrede{Sehr geehrte Damen und Herren,}
\Gruss{}{0.1cm}


\begin{document}
\begin{g-brief}
    
    <<$text>>

    \vspace{0.5cm}

    \begin{center}
        \begin{longtable}{p{5cm}p{5cm}rr}
            <<__('Rechnungsnummer')|escape_latex>> & <<__('Rechnungszeitraum')|escape_latex>> & <<__('Betrag')|escape_latex>> & <<__('Mahnstufe')|escape_latex>> \\
            \hline
            <<foreach from=$bills item=bill>>
                <<$bill->getNumber()>> &
                <<$bill->getTimeFrom()|date_format:"%d.%m.%Y">> - <<$bill->getTimeUntil()|date_format:"%d.%m.%Y">> &
                <<__('€ %s', $bill->getAmount()|inttoprice)|escape_latex>> &
                <<$bill->getStep()>> \\
            <</foreach>>
            \hline
            \multicolumn{2}{p{13cm}}{\textbf{<<__('Offener Rechnungsbetrag:')|escape_latex>>}} & \textbf{<<__('€ %s', $this->getAmount()|inttoprice)|escape_latex>>} \\
            \hline \hline
        \end{longtable}
    \end{center}

    <<__('Wir bitten Sie, die Zahlung innerhalb von 10 Tagen auf unser unten aufgeführtes Konto anzuweisen.
    Falls Sie den Betrag bereits überwiesen haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.')|escape_latex>> \\ \\
    <<__('Bei Rückfragen stehen wir Ihnen gern per E-Mail unter %s oder telefonisch unter %s zur Verfügung.', $config->locale->email->accounting, $config->locale->tel->accounting )|escape_latex>>\\ \\

    <<__('Mit freundlichen Grüßen')|escape_latex>> \\
    <<__('gez. Jörg Gerbig')|escape_latex>>

    \newpage

    <<foreach from=$bills item=bill>>
        <<if file_exists($bill->getPdf())>>
            \includepdf[]{<<$bill->getPdf()>>}
        <</if>>
    <</foreach>>

\end{g-brief}
\end{document}