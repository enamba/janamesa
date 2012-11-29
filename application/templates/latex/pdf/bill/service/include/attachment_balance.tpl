<<if count($bill->getBalanceList()) > 0>>
    \newpage
    
    \begin{center}
        \begin{large}
            \textbf{<<__('Auflistung')|escape_latex>>}
        \end{large}
    \end{center}

    \begin{longtable}{m{4cm}m{2cm}m{10cm}}

        \textbf{<<__('Datum')|escape_latex>>} &
        \textbf{<<__('Betrag')|escape_latex>>} &

        \textbf{<<__('Kommentar')|escape_latex>>} \\

        \hline

        \showrowcolors
        <<foreach from=$bill->getBalanceList() item=balance>>
            
            <<$balance.created|date_format:"%d.%m.%Y %H:%m"|escape_latex>> &
            <<$balance.amount|escape_latex>> &
            <<$balance.comment|escape_latex>> 
            <<if $balance.reference instanceof Yourdelivery_Model_Billing>>
                %nothing to do here
            <</if>> \\

        <</foreach>>
        \hiderowcolors

        \hline
        \hline
        & \ding{204} <<if $bill->getBalanceAmount() < 0>>(<</if>><<$bill->getBalanceAmount()|abs|inttoprice>><<if $bill->getBalanceAmount() < 0>>)<</if>> \\
        
    \end{longtable}
<</if>>