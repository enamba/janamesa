<<extends file="bill/base.tpl">>
<<block name="content">>

    <<if $bill->getCompany()->getBillMode() <= 1 && !in_array($bill->getCompany()->getId(),array(1261,1950))>>
        <<include file='bill/crefo.tpl'>>
    <</if>>

    <<$header.preamble|escape_latex>> <<__('im Zeitraum vom %s bis zum %s stellen wir Ihnen in Rechnung:',$bill->from|date_format:"%d.%m.%Y",$bill->until|date_format:"%d.%m.%Y")|escape_latex>> \\

    \begin{center}
        \begin{longtable}{p{12cm}r}
                \hiderowcolors
                <<__('Netto')|escape_latex>>  7\%:		& <<__('€ %s', $bill->calculateItem7()|inttoprice)|escape_latex>> \\
                <<__('Netto')|escape_latex>> 19\%:		& <<__('€ %s', $bill->calculateItem19()|inttoprice)|escape_latex>> \\
                \hline
                <<__('Summe Netto:')|escape_latex>>		& \textbf{<<__('€ %s', $bill->calculateNetto()|inttoprice)|escape_latex>>} \\ \\

                <<__('MwSt')|escape_latex>> 7\%:          & <<__('€ %s', $bill->calculateTax7()|inttoprice)|escape_latex>> \\
                <<__('MwSt')|escape_latex>> 19\%:         & <<__('€ %s', $bill->calculateTax19()|inttoprice)|escape_latex>> \\
                \hline
                <<__('Summe Mwst:')|escape_latex>>         & \textbf{<<__('€ %s', $bill->calculateTax()|inttoprice)|escape_latex>>} \\ \\


                <<__('Summe Brutto:')|escape_latex>>       & \textbf{<<__('€ %s', $bill->calculateBrutto()|inttoprice)|escape_latex>>} \\
                \hline
                <<if $bill->calculatePfandBrutto() > 0>>
                    <<__('eingereichtes Pfand Netto:')|escape_latex>>        & (<<__('€ %s', $bill->calculatePfandNetto()|inttoprice)|escape_latex>>) \\
                    <<__('eingereichtes Pfand MwSt')|escape_latex>> 19\%:    & (<<__('€ %s', $bill->calculatePfandSteuern()|inttoprice)|escape_latex>>) \\
                <</if>>

                <<if $bill->calculateDiscount() > 0>>
                    <<__('Gutschrift:')|escape_latex>>         & (<<__('€ %s', $bill->calculateDiscount()|inttoprice)|escape_latex>>) \\
                <</if>>

                \hline
                \textbf{<<__('Offener Rechnungsbetrag:')|escape_latex>>} & \textbf{<<__('€ %s', $bill->calculateOpenAmount()|inttoprice)|escape_latex>>} \\
                \hline \hline

        \end{longtable}
    \end{center}
   
    <<if $bill->getCompany()->getBillMode() <= 1 && !in_array($bill->getCompany()->getId(),array(1261,1950))>>
        <<__('Zahlungen mit befreiender Wirkung können nur an die Crefo-Factoring Berlin-Brandenburg GmbH, Einemstraße 1, 10787 Berlin geleistet werden, an die wir unsere Forderungen übertragen und verkauft haben: Konto-Nr. 220624100, BLZ 100 400 00, bei der Commerzbank Berlin.')|escape_latex>>       
        <<__('Eine genaue Auflistung der Leistungen entnehmen Sie bitte dem Anhang. Bei Rückfragen können Sie uns unter %s oder unter %s kontaktieren.', $config->locale->email->accounting, $config->locale->tel->accounting)|escape_latex>>
    <<else>>
        <<__('Bitte überweisen Sie den offenen Rechnungsbetrag unter Angabe der Rechnungsnummer auf unser Konto! Eine genaue Auflistung der Leistungen entnehmen Sie bitte dem Anhang. Bei Rückfragen können Sie uns unter %s oder unter %s kontaktieren', $config->locale->email->accounting, $config->locale->tel->accounting)|escape_latex>>
    <</if>>
  
    <<if $custom.projectSub>>
        <<include file='bill/company/include/shortAttachment.tpl'>>

        <<if $custom.verbose>>
            <<include file='bill/company/include/longAttachment.tpl'>>
        <</if>>
    <<else>>

        %list all projectnumbers
        \newpage

        <<foreach from=$bill->getProjectNumbers() item=project>>
            <<$bill->setOnlyProject($project)>>
            <<if $bill->hasOrders()>>
                <<include file='bill/company/project/loop.tpl'>>
            <</if>>
        <</foreach>>

    <</if>>

<</block>>