<<extends file="bill/base.tpl">>
<<block name="content">>

    \begin{longtable}{lrrr}
        \hiderowcolors
        <<__('Beschreibung')|escape_latex>> &
        <<__('Menge')|escape_latex>> &
        <<__('Nettopreis (Einzel)')|escape_latex>> &
        <<__('Nettopreis (Gesamt)')|escape_latex>> \\
        \toprule
        \toprule
        <<if ($upsellingGoods->getCountCanton2626() > 0)>>
            <<__('Pizzakarton Größe: 26x26x4')|escape_latex>> &
            <<$upsellingGoods->getCountCanton2626()>> x <<$upsellingGoods->getUnitCanton2626()>> &
            <<__("%s €", $upsellingGoods->getCostCanton2626()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostCanton2626() * $upsellingGoods->getCountCanton2626())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountCanton2626N() > 0)>>
            <<__('Pizzakarton Größe: 26x26x4 Notebooksbilliger')|escape_latex>> &
            <<$upsellingGoods->getCountCanton2626N()>> x <<$upsellingGoods->getUnitCanton2626N()>> &
            <<__("%s €", $upsellingGoods->getCostCanton2626N()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostCanton2626N() * $upsellingGoods->getCountCanton2626N())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountCanton2626D() > 0)>>
            <<__('Pizzakarton Größe: 26x26x4 Discotel')|escape_latex>> &
            <<$upsellingGoods->getCountCanton2626D()>> x <<$upsellingGoods->getUnitCanton2626D()>> &
            <<__("%s €", $upsellingGoods->getCostCanton2626D()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostCanton2626D() * $upsellingGoods->getCountCanton2626D())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountCanton2626S() > 0)>>
            <<__('Pizzakarton Größe: 26x26x4 DeutschlandSIM')|escape_latex>> &
            <<$upsellingGoods->getCountCanton2626S()>> x <<$upsellingGoods->getUnitCanton2626S()>> &
            <<__("%s €", $upsellingGoods->getCostCanton2626S()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostCanton2626S() * $upsellingGoods->getCountCanton2626S())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountCanton2626H() > 0)>>
            <<__('Pizzakarton Größe: 26x26x4 Hannover')|escape_latex>> &
            <<$upsellingGoods->getCountCanton2626H()>> x <<$upsellingGoods->getUnitCanton2626H()>> &
            <<__("%s €", $upsellingGoods->getCostCanton2626H()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostCanton2626H() * $upsellingGoods->getCountCanton2626H())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountCanton2828() > 0)>>
            <<__('Pizzakarton Größe: 28x28x4')|escape_latex>> &
            <<$upsellingGoods->getCountCanton2828()>> x <<$upsellingGoods->getUnitCanton2828()>> &
            <<__("%s €", $upsellingGoods->getCostCanton2828()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostCanton2828() * $upsellingGoods->getCountCanton2828())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountCanton3232() > 0)>>
            <<__('Pizzakarton Größe: 32x32x4')|escape_latex>> &
            <<$upsellingGoods->getCountCanton3232()>> x <<$upsellingGoods->getUnitCanton3232()>> &
            <<__("%s €", $upsellingGoods->getCostCanton3232()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostCanton3232() * $upsellingGoods->getCountCanton3232())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountServicing() > 0)>>
            <<__('Servietten 2lagig, 33x33')|escape_latex>> &
            <<$upsellingGoods->getCountServicing()>> x <<$upsellingGoods->getUnitServicing()>> &
            <<__("%s €", $upsellingGoods->getCostServicing()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostServicing() * $upsellingGoods->getCountServicing())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountBags() > 0)>>
            <<__('Plastiktüten')|escape_latex>> &
            <<$upsellingGoods->getCountBags()>> x <<$upsellingGoods->getUnitBags()>> &
            <<__("%s €", $upsellingGoods->getCostBags()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostBags() * $upsellingGoods->getCountBags())|inttoprice)|escape_latex>> \\
        <</if>>
        <<if ($upsellingGoods->getCountSticks() > 0)>>
            <<__('Chopsticks')|escape_latex>> &
            <<$upsellingGoods->getCountSticks()>> x <<$upsellingGoods->getUnitSticks()>> &
            <<__("%s €", $upsellingGoods->getCostSticks()|inttoprice)|escape_latex>> &
            <<__("%s €", ($upsellingGoods->getCostSticks() * $upsellingGoods->getCountSticks())|inttoprice)|escape_latex>> \\
        <</if>>
        \toprule
        & & <<__("Zwischensumme:")|escape_latex>> &
        <<__("%s €", $upsellingGoods->calculateNetto()|inttoprice)|escape_latex>> \\
        & & <<__("Umsatzsteuersatz:")|escape_latex>> &
        19 \% \\
        & & <<__("Umsatzsteuer:")|escape_latex>> &
        <<__("%s €", $upsellingGoods->calculateTax()|inttoprice)|escape_latex>> \\
        \toprule
        & & <<__("Summe:")|escape_latex>> &
        <<__("%s €", $upsellingGoods->calculateBrutto()|inttoprice)|escape_latex>> \\
    \end{longtable}

    <<if $hasBalance>>
        <<__("Die vorliegende Rechnung wird mit Ihren zukünftigen Gutschriften verrechnet. Bitte überweisen sie NICHT den Betrag.")|escape_latex>> \\ \\
        <<__("Ihre Lieferung erfolgt innerhalb von 14 Tagen.")|escape_latex>> \\ \\
    <<else>>
        <<__("Bitte überweisen Sie den Betrag per Vorkasse auf das u.a. Konto und geben Sie bitte als Verwendungszweck die Rechnungsnummer an.")|escape_latex>> \\ \\
        <<__("Zu überweisender Betrag:")|escape_latex>> <<__("%s €", $upsellingGoods->calculateBrutto()|inttoprice)|escape_latex>> \\
        <<__("Verwendungszweck:")|escape_latex>> <<if $bill->isVoucher>><<$bill->getNumberVoucher()|escape_latex>><<else>><<$bill->getNumber()|escape_latex>><</if>> \\ \\
        <<__("Ihre Lieferung erfolgt innerhalb von 14 Tagen nach Zahlungseingang.")|escape_latex>> \\ \\
    <</if>>
    <<__("VIELEN DANK FÜR IHREN AUFTRAG")|escape_latex>>

<</block>>
