%Base Template for billings
%%%%%%%%%%%%%%%%%%%%%%%%%%%


\documentclass[12pt, german<<if $DOMAIN_BASE == 'taxiresto.fr'>>, frenchb<</if>><<if $DOMAIN_BASE == 'janamesa.com.br'>>, brazil<</if>><<if $DOMAIN_BASE == 'pyszne.pl'>>, polish<</if>>]{g-brief}

\usepackage[utf8]{inputenc}
\usepackage[german]{babel}
\usepackage[T1]{fontenc}
\usepackage{times}
\usepackage{rotating}
\usepackage{lscape,mathptmx,multirow, graphicx, fancyhdr, booktabs,array,colortbl, longtable}
\usepackage{arydshln}
\usepackage{pifont}
\usepackage{eurosym}
\usepackage{pdfpages}
\usepackage[table]{xcolor}

\fenstermarken
\Name                {<<$config->locale->latex->header->name|escape_latex>>}
\Strasse             {<<$config->locale->latex->header->address1|escape_latex>>}
\Zusatz              {<<$config->locale->latex->header->address2|escape_latex>>}
\Ort                 {<<$config->locale->latex->header->address3|escape_latex>>}
\Telefon             {<<$config->locale->latex->footer->line1|escape_latex>>}
\Telefax             {<<$config->locale->latex->footer->line2|escape_latex>>}
\Telex               {<<$config->locale->latex->footer->line3|escape_latex>>}
\EMail               {<<$config->locale->latex->footer->line4|escape_latex>>}
\HTTP                {<<$config->locale->latex->footer->line5|escape_latex>>}

<<if ($config->locale->latex->footer->bank1->value && ($config->domain->base == 'janamesa.com.br' || $bill->getObject() instanceof 'Yourdelivery_Model_Servicetype_Abstract' || ($bill->getObject() instanceof 'Yourdelivery_Model_Company' && ($bill->getCompany()->getBillMode() > 1 || in_array($bill->getCompany()->getId(),array(1261,1950))))))>>
    \Bank            {<<$config->locale->latex->footer->bank1->value|escape_latex>>}
    \BLZ             {<<$config->locale->latex->footer->bank2->value|escape_latex>>}
    \Konto           {<<$config->locale->latex->footer->bank3->value|escape_latex>>}
<</if>>

\RetourAdresse       {<<$config->locale->latex->header->retouradress|escape_latex>>}

\renewcommand{\telefontex}{{\footnotesize}}
\renewcommand{\telextext}{{\footnotesize}}
\renewcommand{\telefaxtext}{{\footnotesize}}
\renewcommand{\httptext}{{\footnotesize}}
\renewcommand{\emailtext}{{\footnotesize}}
\renewcommand{\banktext}{{\footnotesize <<__("Bankverbindung:")|escape_latex>>}}
\renewcommand{\datumtext}{{\footnotesize\textsc{<<__("Datum")|escape_latex>>}}}
<<if ($config->locale->latex->footer->bank1->label)>>
    \renewcommand{\banktext}{\footnotesize <<$config->locale->latex->footer->bank1->label|escape_latex>>}
<</if>>
<<if ($config->locale->latex->footer->bank2->label)>>
    \renewcommand{\blztext}{\footnotesize <<$config->locale->latex->footer->bank2->label|escape_latex>>}
<</if>>
<<if ($config->locale->latex->footer->bank3->label)>>
    \renewcommand{\kontotext}{\footnotesize <<$config->locale->latex->footer->bank3->label|escape_latex>>}
<</if>>

\definecolor{mgray}{HTML}{ECF3FE}
\rowcolors{1}{white}{mgray}

\Adresse{
    <<if $config->domain->base == 'pyszne.pl'>>Nabywca: \\<</if>>
    <<$header.heading|escape_latex>> \\
    <<if strlen($header.zHd) > 0 && $config->domain->base != 'pyszne.pl'>><<__('zHd:')|escape_latex>> <<$header.zHd|escape_latex>> \\<</if>>
    <<$header.street|escape_latex>> <<$header.hausnr|escape_latex>> \\
    <<$header.plz|escape_latex>> <<$header.city|escape_latex>>
    <<if $header.addition>>\\<<$header.addition|escape_latex>><</if>><<block name="addrAdd">><</block>>}

\Betreff{<<if $bill->isVoucher>><<__("Gutschrift")>> <<$bill->getNumberVoucher()|escape_latex>><<else>><<__('Rechnung')|escape_latex>> <<$bill->getNumber()|escape_latex>><</if>>}
\Datum{<<$bill->until|date_format:"%d.%m.%Y">>}
\Anrede{}
\Gruss{}{1cm}


\begin{document}
\begin{g-brief}

    <<$custom.content|escape_latex>>
    <<if $custom.content>>\\ \rule{\textwidth}{.2pt}<</if>>
    <<block name="content">><</block>>
    
    <<if $DOMAIN_BASE == 'taxiresto.fr' && !$bill->getObject() instanceof Yourdelivery_Model_Customer_Abstract>>
        \newpage
        \includepdf[]{../pdf/bill/service/include/fr/bankverbindung.pdf}
    <</if>>

\end{g-brief}
\end{document}