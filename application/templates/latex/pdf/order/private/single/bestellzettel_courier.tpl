%
%  order report template
%
%  Created by Matthias Laug on 2009-08-06.
%
\documentclass[9pt]{scrartcl}

% set font to times
\usepackage{mathptmx}
\usepackage[scaled=1]{helvet}
\renewcommand\familydefault{phv}

% set margins 
\usepackage[left=5mm,right=10mm, top=15mm, bottom=30mm]{geometry}

% Use utf-8 encoding for foreign characters
\usepackage[utf8]{inputenc}

% Use german language
\usepackage[german]{babel}
\usepackage[T1]{fontenc}

% Setup for fullpage use
% multirow - rowspan in tables
% graphicx - include graphics
% fancyhdr - footer/header
% booktabs - nice tables
\usepackage{multirow, graphicx, fancyhdr, booktabs,array,longtable,lastpage}

% define \EUR{amount}
\usepackage[left]{eurosym}

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
} 							%verwende v{Xcm} für zentrierte Spalten fester Breite

%-------------------------------- Kopf- und Fußzeile
\pagestyle{fancy}
\fancyhf{}
\setlength{\parindent}{0pt} 
%header
\fancyhead[L]{}
\fancyhead[R]{\ifnum\thepage=1 \includegraphics{../includes/logos/<<$DOMAIN_BASE|replace:'.':'-'>>.jpg}\fi}
\renewcommand{\headrulewidth}{0pt}
% footer
\fancyfoot[L]{<<__('Verschickt durch %s, %s', $config->locale->headoffice->name, $config->locale->headoffice->address )|escape_latex>>\\
 \textbf{<<__('Kundensupport:')|escape_latex>>} <<__('%s, Mo. - Fr. 09:00 - 24:00 Uhr, Sa. - So. 11:00 - 24:00 Uhr', $config->locale->areacodetel->support)|escape_latex>> \\
<<__('Änderungen können in der Zeit Montag bis Freitag von 09:00 - 18:00 Uhr durchgeführt werden.')|escape_latex>>
}
\fancyfoot[C]{}
\fancyfoot[R]{}
\renewcommand{\footrulewidth}{0.4pt}


%-------------------------------- content
\begin{document}
	\vspace*{5mm}

		% Dienstleisterinfos
		\begin{longtable}{lp{15cm}}

			\multicolumn{2}{V{\textwidth}}{<<__('Dienstleister:')|escape_latex>>} \\
			\toprule[2pt]

            <<__('Strasse, Hausnr.')|escape_latex>>        & \textbf{<<$order->getService()->getStreet()|escape_latex>>} \textbf{<<$order->getService()->getHausnr()|escape_latex>>}\\
            <<__('PLZ, Ort:')|escape_latex>>               & \textbf{<<$order->getService()->getPlz()|escape_latex>>} \textbf{<<$order->getService()->getOrt()->getOrt()|escape_latex>>}\\
            <<__('Telefonnummer:')|escape_latex>>          & \textbf{<<$order->getService()->getTel()|escape_latex>>}\\
            <<__('Name:')|escape_latex>>                   & \textbf{<<$order->getService()->getName()|escape_latex>>}\\
                
		\end{longtable}

        % Empfaenger infos
		\vspace*{1cm}
		\begin{longtable}{lp{15cm}}

			\multicolumn{2}{V{\textwidth}}{<<__('Empfänger:')|escape_latex>>} \\
			\toprule[2pt]

            <<__('Vorname')|escape_latex>>                 & \textbf{<<$order->getCustomer()->getPrename()|escape_latex>>}\\
            <<__('Nachname')|escape_latex>>                & \textbf{<<$order->getCustomer()->getName()|escape_latex>>}\\
            <<__('Strasse, Hausnr.')|escape_latex>>        & \textbf{<<$order->getLocation()->getStreet()|escape_latex>> <<$order->getLocation()->getHausnr()|escape_latex>>}\\
            <<__('PLZ, Ort:')|escape_latex>>               & \textbf{<<$order->getLocation()->getPlz()|escape_latex>> <<$order->getLocation()->getOrt()->getOrt()>>}\\
            <<__('Telefonnummer:')|escape_latex>>          & \textbf{<<$order->getLocation()->getTel()|escape_latex>>}\\
            <<__('Firma:')>>                  & \textbf{<<$order->getLocation()->getCompany()|default:__("k.A.")|escape_latex>>}\\
            <<__('Besonderheiten:')|escape_latex>>         & \textbf{<<$order->getLocation()->getComment()|default:__("k.A.")|escape_latex>>}\\

		\end{longtable}

        % Kurierdienst infos
		\vspace*{1cm}
		\begin{longtable}{lp{15cm}}

			\multicolumn{2}{V{\textwidth}}{<<__('Kurierdienst:')|escape_latex>>} \\
			\toprule[2pt]

            <<__('Strasse, Hausnr.')|escape_latex>>        & \textbf{<<$order->getService()->getCourier()->getStreet()|escape_latex>>} \textbf{<<$order->getService()->getCourier()->getHausnr()|escape_latex>>}\\
            <<__('Telefonnummer:')|escape_latex>>          & \textbf{<<$order->getService()->getCourier()->getMobile()|escape_latex>>}\\
            <<__('Faxnummer:')|escape_latex>>              & \textbf{<<$order->getService()->getCourier()->getFax()|escape_latex>>}\\
            <<__('Name:')|escape_latex>>                   & \textbf{<<$order->getService()->getCourier()->getName()|escape_latex>>}\\

		\end{longtable}

		\vspace*{1cm}

    <<if $order->getService()->hasGoCourier() && $order->getDeliverTime() > $order->getTime()>>
        \Huge{\textbf{<<$order->getDeliverTime()|date_format:"%d.%m.%y um %H:%M">> <<__('soll beim Kunden sein')|escape_latex>>}}\\
    <<else>>
        \Huge{\textbf{<<$order->computePickUpTime()|date_format:"%d.%m.%y um %H:%M">> <<__('Abholbereit')|escape_latex>>}}\\
    <</if>>

        \Large{\textbf{<<__('Distanz (Luftlinie):')|escape_latex>> <<$order->getService()->getCourier()->calculateRange()|string_format:"%01.2f">> KM / <<__('€ %s', $order->getCourierCost()|inttoprice)|escape_latex>>}}\\
 
        \textbf{<<__('Bei Problemen oder Verzögerungen kontaktieren sie bitten den Kunden direkt.')|escape_latex>>}

\end{document}
