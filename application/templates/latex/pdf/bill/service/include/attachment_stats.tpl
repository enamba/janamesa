\newpage

<<if count($bill->getTopPlz(1)) > 0>>
\begin{center}
    \begin{large}
        \textbf{<<__('Top PLZ')|escape_latex>>}
    \end{large}
\end{center}

\begin{longtable}{l|r}
    \textbf{<<__('PLZ')|escape_latex>>} &
    \textbf{<<__('Bestellungen')|escape_latex>>} \\

    \hline

    \showrowcolors
    <<foreach from=$bill->getTopPlz(10) item=count key=plz>>

            \textbf{<<$plz>>} &
            <<$count>> \\

    <</foreach>>
    \hiderowcolors

\end{longtable}
<</if>>

<<if count($bill->getTopPlz(1)) > 0>>
\begin{center}
    \begin{large}
        \textbf{<<__('Top Speisen')|escape_latex>>}
    \end{large}
\end{center}

\begin{longtable}{l|r}
    \textbf{<<__('Speisen')|escape_latex>>} &
    \textbf{<<__('Bestellungen')|escape_latex>>} \\

    \hline

    \showrowcolors
    <<foreach from=$bill->getTopMeals(10) item=count key=meal>>

            \textbf{<<$meal|escape_latex>>} &
            <<$count>> \\

    <</foreach>>
    \hiderowcolors

\end{longtable}
<</if>>

<<if count($bill->getTopPlz(1)) > 0>>
\begin{center}
    \begin{large}
        \textbf{<<__('Top Uhrzeit')|escape_latex>>}
    \end{large}
\end{center}

\begin{longtable}{l|r}
    \textbf{<<__('Uhrzeit')|escape_latex>>} &
    \textbf{<<__('Bestellungen')|escape_latex>>} \\

    \hline

    \showrowcolors
    <<foreach from=$bill->getTopTimes(10) item=count key=time>>

            \textbf{<<$time>>:00 - <<($time+1)>>:00} &
            <<$count>> \\

    <</foreach>>
    \hiderowcolors

\end{longtable}
<</if>>
