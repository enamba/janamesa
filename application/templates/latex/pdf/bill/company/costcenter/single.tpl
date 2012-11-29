<<extends file="bill/base.tpl">>

<<block name="addrAdd">>\\<<if !is_string($bill->getOnlyCostcenter())>><<$bill->getOnlyCostcenter()->getName()|escape_latex>><</if>><</block>>

<<block name="content">>

    <<$header.preamble|escape_latex>> <<__('im Zeitraum vom %s bis zum %s stellen wir Ihnen in Rechnung:',$bill->from|date_format:"%d.%m.%Y",$bill->until|date_format:"%d.%m.%Y")|escape_latex>> \\

    <<include file='bill/company/costcenter/loop.tpl'>>

<</block>>