<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 06:40:49
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/menu/plz.htm" */ ?>
<?php /*%%SmartyHeaderCode:3159606625024d721dd15c4-61027664%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1f538f8a7071f8c21b62c48bfdbd196b9398d056' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/menu/plz.htm',
      1 => 1340281391,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3159606625024d721dd15c4-61027664',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<div class="yd-dialogs"><a class="yd-dialogs-close yd-go-back"></a>

    <div class="yd-dialogs-head">
        <h2><?php echo __('Bei %s bestellen',$_smarty_tpl->getVariable('service')->value->getName());?>
</h2>
    </div>

    <div class="yd-lightbox-content-container">

        <form id="yd-select-plz-form" action="">

            <div style="text-align: center; background: #ffe; border: 1px solid #999; padding: 10px; -moz-border-radius: 4px; -webkit-border-radius: 4px; outline: 0; margin: 0 auto;">
                <label for="cityId">
                    <?php if (isset($_smarty_tpl->getVariable('cityIds',null,true,false)->value)&&!count($_smarty_tpl->getVariable('cityIds')->value)){?>
                    <?php echo __('Dein Liefergebiet wurde nicht gefunden, bitte wähle ein anderes:');?>

                    <?php }else{ ?>
                    <?php echo __('Bitte wähle Dein Liefergebiet:');?>

                    <?php }?>
                </label>

                <br /><br />
                <input type="hidden" name="cityId" value="" />
                <input type="text" class="yd-plz-autocomplete yd-plz-autocomplete-autosubmit yd-only-nr yd-empty-text ui-autocomplete-input yd-only-priv yd-autocomplete-value[service[<?php echo $_smarty_tpl->getVariable('service')->value->getId();?>
]]" name="plz" value="" />
            </div>

            <div class="yd-lightbox-buttons">
                <input type="button" value="<?php echo __('Abbrechen');?>
" class="button yd-go-back" />
            </div>
            
        </form>
    </div>
</div>
