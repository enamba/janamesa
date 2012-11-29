<?php /* Smarty version Smarty-3.0.7, created on 2012-07-27 07:31:54
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/pyszne.pl/order/_includes/plz.htm" */ ?>
<?php /*%%SmartyHeaderCode:41645769950126e1a77c744-52999438%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ee588c219c7dc88e8cf005a43769bec1c439c49c' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/pyszne.pl/order/_includes/plz.htm',
      1 => 1343151152,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '41645769950126e1a77c744-52999438',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<form action="/order_basis/citystreet" method="post" class="pl-autocomplete-city-street yd-validation" <?php if ($_smarty_tpl->getVariable('idForm')->value){?>id="<?php echo $_smarty_tpl->getVariable('idForm')->value;?>
"<?php }?>>

    <?php if (isset($_smarty_tpl->getVariable('service',null,true,false)->value)){?>
        <input type="hidden" name="serviceId" value="<?php echo $_smarty_tpl->getVariable('service')->value->getId();?>
" />
    <?php }?>

    <input type="text" name="city" value="" class="pl-autocomplete-city" />

    <input type="text" name="street" value="" class="pl-autocomplete-street" />

    <!-- this field only shows up, if needed -->
    <input type="text" name="hausnr" class="pl-autocomplete-number" />

    <input type="submit" name="suchen" value="pokaż restauracje" class="pl-autocomplete-button" />

    <span class="pl-autocomplete-helper1">MIASTO</span>
    <span class="pl-autocomplete-helper2">ULICA</span>
    <span class="pl-autocomplete-helper3">NR</span>

    <span class="pl-autocomplete-why">
        Dlaczego potrzebujemy Twój adres?
        <span class="pl-autocomplete-reason">
            Twój adres jest konieczny, aby pokazać Ci dokładnie te restauracje, które na pewno do Ciebie dowożą.
            Uwaga - dane, które podasz będą użyte tylko w celu realizacji zamówienia.
        </span>
    </span>

</form>