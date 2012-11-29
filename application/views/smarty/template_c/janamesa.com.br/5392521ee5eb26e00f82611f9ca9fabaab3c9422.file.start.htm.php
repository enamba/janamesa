<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:13:56
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/company/start.htm" */ ?>
<?php /*%%SmartyHeaderCode:6152743525024c5c72bbf93-77845932%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5392521ee5eb26e00f82611f9ca9fabaab3c9422' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/company/start.htm',
      1 => 1340281391,
      2 => 'file',
    ),
    '4f58a2b68d3ce1d14c1e0047210224be7eb87689' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/base.htm',
      1 => 1338548803,
      2 => 'file',
    ),
    'ef45d3c6888e64fe399a09840bd303cc8c9758eb' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_base/janamesa.com.br.htm',
      1 => 1344611266,
      2 => 'file',
    ),
    'cf85fd57bb43ff861bb86823d125bbb8371cf5c6' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/budget.htm',
      1 => 1338548803,
      2 => 'file',
    ),
    'a7b6a3eab1c2f8b584f718f472946e3795c7ed82' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/lastorder.htm',
      1 => 1344338315,
      2 => 'file',
    ),
    'c1b0ef572445d1a4d80499a5a9f390367081b7e3' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/favorites.htm',
      1 => 1344338315,
      2 => 'file',
    ),
    '37115fad77fda232f32c2dcd3e1855d4f16e1c05' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/sidebar/payment.htm',
      1 => 1342711276,
      2 => 'file',
    ),
    '83bf73cab645545dd1c16cece25937a3b15088f9' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/start/company_address.htm',
      1 => 1338548803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '6152743525024c5c72bbf93-77845932',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
if (!is_callable('smarty_modifier_inttoprice')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.inttoprice.php';
if (!is_callable('smarty_modifier_date_weekday')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.date_weekday.php';
if (!is_callable('smarty_modifier_date_format')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.date_format.php';
?><!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="pt" xml:lang="pt" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="pt" xml:lang="pt" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="pt" xml:lang="pt" class="ie8"> <![endif]-->
<!--[if gt IE 8 ]> <html lang="pt" xml:lang="pt" class="ie9"> <![endif]-->
<!--[!IE]>   <!--> <html lang="pt" xml:lang="pt">         <!--<![endif]-->
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="author" content="yourdelivery GmbH" />
        <meta name="publisher" content="yourdelivery GmbH" />

        <!-- custom meta tags -->
        <?php  $_smarty_tpl->tpl_vars['meta'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('additionalMetatags')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['meta']->key => $_smarty_tpl->tpl_vars['meta']->value){
?>
        <?php echo $_smarty_tpl->tpl_vars['meta']->value;?>

        <?php }} ?>
        
        <meta name="viewport" content="width=1100">

        <!-- page title -->
        <title>Janamesa</title>

        <?php if ($_smarty_tpl->getVariable('canonical')->value){?>
        <link rel="canoncial" href="<?php echo $_smarty_tpl->getVariable('canonical')->value;?>
" />
        <?php }?>

        <link rel="SHORTCUT ICON" href="/media/css/www.janamesa.com.br/favicon.ico" type="image/x-icon" />

        <?php $_template = new Smarty_Internal_Template('_cookies/janamesa/abtest.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        
        <link rel="stylesheet" type="text/css" href="/media/css/compiled/frontend-<?php echo (($tmp = @$_smarty_tpl->getVariable('extra_css')->value)===null||$tmp==='' ? 'no' : $tmp);?>
-<?php echo $_smarty_tpl->getVariable('REVISION')->value;?>
.css" />
        
        <script type="text/javascript" src="/media/javascript/compiled/frontend-<?php echo $_smarty_tpl->getVariable('REVISION')->value;?>
.js"></script>
        <!-- define global state of this page -->
        <script type="text/javascript">
            YdMode = "<?php echo (($tmp = @$_smarty_tpl->getVariable('mode')->value)===null||$tmp==='' ? 'rest' : $tmp);?>
";
            log('CURRENT MODE: ' + YdMode);
        </script>
        
        <?php if ($_smarty_tpl->getVariable('grid')->value){?>
            <script type='text/javascript' charset='UTF-8'>
                /* <![CDATA[ */
                    <?php echo $_smarty_tpl->getVariable('grid')->value->getHeaderScript();?>

                /* ]]> */
            </script>
        <?php }?>

        <?php $_template = new Smarty_Internal_Template('_cookies/google/analytics.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php if ($_smarty_tpl->getVariable('trackGoogleEcomerce')->value){?>
            <?php $_template = new Smarty_Internal_Template('_cookies/google/ecommerce.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php }?>
    </head>

    <body class="br">

        <div id="yd-group-fade" class="hidden"></div>
        <a name="anchor-0"></a>
        <div id="yd-nonfooter">

            <?php $_template = new Smarty_Internal_Template('base_header.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>

            <div id="yd-content" class="yd-clearfix">

                <?php $_template = new Smarty_Internal_Template('notification.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>

                

    <div id="yd-sidebar">
    	<?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div class="yd-box br-box"><?php }?>
        
            <?php $_template = new Smarty_Internal_Template("order/_includes/sidebar/budget.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '6152743525024c5c72bbf93-77845932';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:13:56
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/budget.htm" */ ?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
if (!is_callable('smarty_modifier_inttoprice')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.inttoprice.php';
if (!is_callable('smarty_modifier_date_weekday')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.date_weekday.php';
if (!is_callable('smarty_modifier_date_format')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.date_format.php';
?><div class="yd-box">
    <div class="yd-box-head yd-float-head cursor" id="yd-expand-budget-head">
        <a class="yd-plus cursor" id="yd-expand-budget-head-plus"></a>
        <a class="yd-minus cursor hidden" id="yd-expand-budget-head-minus"></a>
        <small><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('cust')->value->getCompany()->getName(),35," ...",true);?>
</small><br />
        <?php echo __('Ihr aktuelles Budget:');?>
 <?php echo __('%s €',smarty_modifier_inttoprice($_smarty_tpl->getVariable('cust')->value->getCurrentBudget()));?>
<br />
        <small><?php echo __('Für Übersicht bitte klicken!');?>
</small>
    </div>
    <div class="yd-box-body yd-clearfix" id="yd-expand-budget-body">
        <ul>
            <li class="yd-budget-time">
                <?php if (!is_null($_smarty_tpl->getVariable('cust')->value->getBudget())){?>
                <em><b><?php echo __('Ihre Budgetzeiten sind:');?>
</b></em><br />
                    <?php  $_smarty_tpl->tpl_vars['times'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['day'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getBudget()->getBudgetTimes(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['times']->key => $_smarty_tpl->tpl_vars['times']->value){
 $_smarty_tpl->tpl_vars['day']->value = $_smarty_tpl->tpl_vars['times']->key;
?>
                            <?php  $_smarty_tpl->tpl_vars['time'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['times']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['time']->key => $_smarty_tpl->tpl_vars['time']->value){
?>
                                <em><b><?php echo smarty_modifier_date_weekday($_smarty_tpl->tpl_vars['day']->value);?>
</b><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['time']->value['from'],'%H:%M');?>
 - <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['time']->value['until'],'%H:%M');?>
</em> <strong><?php echo __('%s €',smarty_modifier_inttoprice($_smarty_tpl->tpl_vars['time']->value['amount']));?>
</strong><br />
                            <?php }} ?>
                    <?php }} else { ?>
                    <div style="text-align:left;"><?php echo __('Keine Budgetzeiten vorhanden!');?>
</div>
                    <?php } ?>
                <?php }else{ ?>
                    <div style="text-align:left;"><?php echo __('Kein Budget vorhanden!');?>
</div>
                <?php }?>
            </li>
            

            <li class="yd-budget-yes-or-no">
                <em><?php echo __('Bestellungen beim Catering:');?>
</em>
                <?php if ($_smarty_tpl->getVariable('cust')->value->allowCater()){?>
                    <span class="yd-budget-yes"></span>
                <?php }else{ ?>
                    <span class="yd-budget-no"></span>
                <?php }?>
            </li>

            <li class="yd-budget-yes-or-no">
                <em><?php echo __('Bestellungen beim Supermarkt:');?>
</em>
                <?php if ($_smarty_tpl->getVariable('cust')->value->allowGreat()){?>
                    <span class="yd-budget-yes"></span>
                <?php }else{ ?>
                    <span class="yd-budget-no"></span>
                <?php }?>
            </li>
            
        </ul>
    </div>
</div>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/budget.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>
            <?php $_template = new Smarty_Internal_Template("order/_includes/sidebar/lastorder.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '6152743525024c5c72bbf93-77845932';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:13:56
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/lastorder.htm" */ ?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
if (!is_callable('smarty_modifier_date_format')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.date_format.php';
if (!is_callable('smarty_modifier_inttoprice')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.inttoprice.php';
?><div class="yd-box-head yd-float-head cursor" id="expand-order-head">
    <a id="expand-order-plus" class="yd-plus"></a>
    <a id="expand-order-minus" class="yd-minus"></a>
    <?php echo __('Letzte Bestellung');?>
<br />
    <small><?php echo __('Hier findest Du die Übersicht Deiner zuletzt getätigten Bestellung!');?>
</small>
</div>

<?php $_smarty_tpl->tpl_vars['ord'] = new Smarty_variable($_smarty_tpl->getVariable('cust')->value->getLastOrder(1,$_smarty_tpl->getVariable('mode')->value,$_smarty_tpl->getVariable('kind')->value), null, null);?>
<?php if (count($_smarty_tpl->getVariable('ord')->value)>0){?>
    <div id="expand-order">
        <div class="yd-box-body yd-float-body">
            <ul class="yd-sidebar-list">
                <li><em><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('ord')->value->getService()->getName(),20,"...");?>
</em><strong><?php echo smarty_modifier_date_format($_smarty_tpl->getVariable('ord')->value->getTime(),__("%d.%m.%y um %H:%M"));?>
</strong></li>
                <li><em><?php echo __('Art:');?>
</em><strong><?php if ($_smarty_tpl->getVariable('ord')->value->getKind()=="comp"){?><?php echo __('Firmenbestellung');?>
<?php }else{ ?><?php echo __('Privat');?>
<?php }?></strong></li>
                <li><em><?php echo __('Lieferzeit:');?>
</em><strong class="<?php if ($_smarty_tpl->getVariable('ord')->value->getMode()=="great"&&$_smarty_tpl->getVariable('ord')->value->getState()==0){?>red<?php }else{ ?>green<?php }?>"> <?php echo smarty_modifier_date_format($_smarty_tpl->getVariable('ord')->value->getDeliverTime(),__("%d.%m.%y um %H:%M"));?>
</strong></li>
                <li><em><?php echo __('Bestellwert:');?>
</em><strong><?php echo __('%s €',smarty_modifier_inttoprice($_smarty_tpl->getVariable('ord')->value->getAbsTotal(false,false,true,false,false)));?>
</strong></li>
                <li><em><?php echo __('Bestellnummer:');?>
</em><strong> <?php echo $_smarty_tpl->getVariable('ord')->value->getNr();?>
</strong></li>
                <?php if ($_smarty_tpl->getVariable('ord')->value->getMode()=="great"){?>
                    <li><em><?php echo __('Status:');?>
</em><strong class="<?php if ($_smarty_tpl->getVariable('ord')->value->getState()==0){?>red<?php }else{ ?>green<?php }?>"><?php echo $_smarty_tpl->getVariable('ord')->value->getStateName();?>
</strong></li>
                <?php }?>
                <li class="align-l"><a href="/ordercoupon/<?php echo $_smarty_tpl->getVariable('ord')->value->getHash();?>
" class="yd-popup">« <?php echo __('Bestellzettel ansehen');?>
</a></li>

                <?php if (($_smarty_tpl->getVariable('ord')->value->getKind()=='priv'||($_smarty_tpl->getVariable('ord')->value->getKind()=="comp"&&$_smarty_tpl->getVariable('ord')->value->getMode()=="rest")&&$_smarty_tpl->getVariable('ord')->value->isRepeatable())){?>
                    <li class="align-l">
                        <a href="#" id="yd-repeat-order-<?php echo $_smarty_tpl->getVariable('ord')->value->getHash();?>
-<?php echo $_smarty_tpl->getVariable('ord')->value->getService()->getId();?>
" class="yd-link-repeat-lastOrder">
                            <span>« <?php echo __('Bestellung wiederholen');?>
</span>
                        </a>
                        <span class="yd-repeat-loading hidden">
                            <?php echo __('Bitte warte, Du wirst weitergeleitet');?>
&nbsp;
                            <img src="<?php echo $_smarty_tpl->getVariable('config')->value->domain->static;?>
/images/yd-background/yd-load-small.gif" alt="" />
                        </span>
                    </li>
                <?php }?>
                <?php if ($_smarty_tpl->getVariable('ord')->value->showRatingLink()){?>
                    <li class="align-l"><a href="/rate/<?php echo $_smarty_tpl->getVariable('ord')->value->getHash();?>
" class="cursor">« <?php echo __('Bestellung bewerten');?>
</a></li>
                <?php }?>
                <?php if ($_smarty_tpl->getVariable('ord')->value->getKind()=="priv"){?>
                    <?php if ($_smarty_tpl->getVariable('config')->value->domain->base!='janamesa.com.br'){?>
                        <li class="align-l"><a href="/user/billrequest/hash/<?php echo $_smarty_tpl->getVariable('ord')->value->getHashtag();?>
">« <?php echo __('verifizierte Rechnung');?>
</a></li>
                    <?php }?>
                <?php }?>
            </ul>
        </div>
    </div>

<?php }else{ ?>
    <div id="expand-order">
        <div class="yd-box-body yd-float-body">
            <ul>
                <li class="align-l">
                    <?php echo __('Hier findest Du die Daten Deiner letzten Bestellung vor, sobald Du zum ersten Mal bestellt hast.');?>

                </li>
            </ul>
        </div>
    </div>
<?php }?>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/lastorder.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>
            <?php $_template = new Smarty_Internal_Template("order/_includes/sidebar/favorites.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '6152743525024c5c72bbf93-77845932';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:13:56
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/favorites.htm" */ ?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
?><?php if ($_smarty_tpl->getVariable('cust')->value->isLoggedIn()){?>
    <div class="yd-box-head cursor yd-float-head" id="yd-expand-fav-head">
        <a id="yd-expand-fav-plus" class="yd-plus"></a>
        <a id="yd-expand-fav-minus" class="yd-minus"></a>
        <?php echo __('Favoriten');?>
<br />
        <small><?php echo __('Bestellung als Favorit speichern und in 15 Sekunden bestellen.');?>
</small>
    </div>

    <div class="yd-box-body yd-float-body " id="yd-expand-fav">
        <ul class="yd-sidebar-list">
            <?php  $_smarty_tpl->tpl_vars['fav'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getFavourites(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['fav']->key => $_smarty_tpl->tpl_vars['fav']->value){
?>
                <li><em><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('fav')->value->getFavName(),20,"...");?>
</em>&nbsp;</li>
                <li class="align-l">
                    <?php if ($_smarty_tpl->getVariable('fav')->value->isRepeatable()){?>
                        <a href="#" id="yd-repeat-order-<?php echo $_smarty_tpl->getVariable('fav')->value->getHash();?>
-<?php echo $_smarty_tpl->getVariable('fav')->value->getService()->getId();?>
" class="repeat-order yd-link-repeat-fav">
                            « <?php echo __('Bestellung wiederholen');?>

                        </a>
                    <?php }?>
                    <?php if ($_smarty_tpl->getVariable('fav')->value->showRatingLink()){?>
                        <br /><a href="/rate/<?php echo $_smarty_tpl->getVariable('fav')->value->getHash();?>
">« <?php echo __('Bestellung bewerten');?>
</a>
                    <?php }?>
                </li>
            <?php }} else { ?>
                <li class="align-l">
                    <?php echo __('Du hast bisher keine Favoriten. Du kannst jede Bestellung am Ende als Favorit speichern oder vorherige Bestellungen in Favoriten umwandeln.');?>

                </li>
            <?php } ?>
        </ul>
    </div>

<?php if ($_smarty_tpl->getVariable('config')->value->domain->base!='janamesa.com.br'){?><br /><br /><?php }?>
<?php }?>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/favorites.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>
        
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div><?php }?> 
        
        <?php $_template = new Smarty_Internal_Template("order/_includes/sidebar/payment.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '6152743525024c5c72bbf93-77845932';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:13:56
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/sidebar/payment.htm" */ ?>
<?php if (!$_smarty_tpl->getVariable('service')->value||!$_smarty_tpl->getVariable('service')->value->isOnlycash()){?>
<div class="yd-box br-box">
    <img src="/media/css/www.janamesa.com.br/images/sidebar-payment.png" alt="paymentLogo" />
</div>
<?php }?><?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/sidebar/payment.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>
    </div>

    <div id="yd-ordering">

        <div class="yd-box">

            <div class="yd-bigbox-head">
                <?php echo __('Account wählen');?>

            </div>

            <div class="yd-bigbox-body yd-clearfix">
                <a href="/order_private/start" id="nav_order_01" class="yd-account-priv yd-set-kind-priv">
                    <?php echo __('Privataccount');?>

                </a>
                <a id="nav_order_02" class="yd-account-comp yd-set-kind-comp active">
                    <?php echo __('Firmenaccount');?>
<br />
                    <i><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('cust')->value->getCompany()->getName(),20," ...",true);?>
</i>
                </a>
            </div>

        </div>

        <?php if ($_smarty_tpl->getVariable('cust')->value->getCurrentBudget()>0||$_smarty_tpl->getVariable('cust')->value->allowCater()||$_smarty_tpl->getVariable('cust')->value->allowGreat()){?>

            <div class="yd-box br-address">

                <div class="yd-bigbox-head">
                    <?php echo __('Lieferadresse wählen');?>


                    <br />
                    <?php if ($_smarty_tpl->getVariable('cust')->value->isCompanyAdmin()){?>
                        <a class="yd-button-240 yd-add-caddress yd-start-adress-icon"><?php echo __('Adresse hinzufügen');?>
</a>
                    <?php }?>
                </div>

                <div class="yd-bigbox-body <?php if ($_smarty_tpl->getVariable('cust')->value->getCompanyLocations()->count()>0){?> is-address <?php }?>">
                    <?php $_smarty_tpl->tpl_vars['temp'] = new Smarty_variable(1, null, null);?>
                    
                    <?php if ($_smarty_tpl->getVariable('cust')->value->getCompanyLocations()->count()==0){?>
                        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
                        	<p>Pedindo de casa ou do trabalho? Não importa, adicione quantos endereços quiser e faça seus próximos pedidos ainda máis rápidos.</p>
                        <?php }?>
                        
                        <a class="cursor yd-adress yd-add-caddress br-add-address"><?php echo __('Noch keine Adressen eingetragen.');?>
</a>
                        
                    <?php }else{ ?>
                        <?php $_template = new Smarty_Internal_Template("order/_includes/start/company_address.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '6152743525024c5c72bbf93-77845932';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:13:56
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/start/company_address.htm" */ ?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
?><?php if ($_smarty_tpl->getVariable('cust')->value->isEmployee()){?>
    <?php  $_smarty_tpl->tpl_vars['address'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getCompanyLocations(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['address']->key => $_smarty_tpl->tpl_vars['address']->value){
?>
        <span class="yd-adress <?php if (!$_smarty_tpl->getVariable('cust')->value->hasPrimaryLocation()&&count($_smarty_tpl->getVariable('cust')->value->getLastOrder())>0&&$_smarty_tpl->getVariable('address')->value->getCityId()==$_smarty_tpl->getVariable('cust')->value->getLastOrder()->getLocation()->getCityId()||count($_smarty_tpl->getVariable('cust')->value->getLocations())==0){?>active<?php }?>"
            id="yd-addr-<?php echo $_smarty_tpl->getVariable('address')->value->getId();?>
-<?php echo $_smarty_tpl->getVariable('address')->value->getCity()->getId();?>
" data="/<?php echo $_smarty_tpl->getVariable('address')->value->getCity()->getUrl($_smarty_tpl->getVariable('mode')->value);?>
">
			
            <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div><?php }?>
                <?php echo $_smarty_tpl->getVariable('address')->value->getStreet();?>
 <?php echo $_smarty_tpl->getVariable('address')->value->getHausnr();?>
, <?php echo $_smarty_tpl->getVariable('address')->value->getPlz();?>
 <?php echo $_smarty_tpl->getVariable('address')->value->getOrt()->getOrt();?>
 
    
                <?php if ($_smarty_tpl->getVariable('cust')->value->isCompanyAdmin()){?>
                    <a href="/company/address/id/<?php echo $_smarty_tpl->getVariable('address')->value->getId();?>
"><?php echo __('bearbeiten');?>
</a>
                <?php }?>
    
                <?php if ($_smarty_tpl->getVariable('address')->value->getAddition()!=''){?>
                    <br />
                    <i><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('address')->value->getAddition(),80," ...",false);?>
</i>
                <?php }?>
            <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div><?php }?>
        </span>
        <?php $_smarty_tpl->tpl_vars['temp'] = new Smarty_variable($_smarty_tpl->getVariable('temp')->value+1, null, null);?>
    <?php }} ?>
<?php }?>

<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/start/company_address.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>
                    <?php }?>
                </div>
            </div>
			
            <?php if ($_smarty_tpl->getVariable('cust')->value->getCompanyLocations()->count()>0&&$_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
            	<a class="yd-button-240" id="yd-start-order-from-address" href="#" style="margin: 0 auto;"><?php echo __('Bestellung jetzt starten');?>
</a>
            	
                <a class="yd-button-240 cursor yd-adress yd-add-caddress"><?php echo __('Adressen hinzufügen');?>
</a>
        	<?php }?>
            
            <a class="yd-button-240 br-hidden" id="yd-start-order-from-address" href="#" style="margin: 0 auto;"><?php echo __('Bestellung jetzt starten');?>
</a>
        <?php }else{ ?>
            <div class="yd-box">
                <div class="yd-bigbox-head">
                    <?php echo __('Information');?>

                </div>

                <div class="yd-bigbox-body">
                    <ul>
                        <li><?php echo __('Du hast zur Zeit kein Budget oder Bestellrechte und kannst daher <b>keine</b> Firmenbestellung bei einem Bringdienst durchführen. <br /><br />Deine Budgetzeiten sind wie folgt:');?>
<br /><br /></li>
                    </ul>
                    <ul>
                        <?php if ($_smarty_tpl->getVariable('cust')->value->getBudget()){?>
                            <?php  $_smarty_tpl->tpl_vars['times'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getBudget()->getBudgetTimes(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['times']->key => $_smarty_tpl->tpl_vars['times']->value){
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['times']->key;
?>
                                <?php  $_smarty_tpl->tpl_vars['ab'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['times']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['ab']->key => $_smarty_tpl->tpl_vars['ab']->value){
?>
                                    <li><?php echo __('%s €',smarty_modifier_inttoprice($_smarty_tpl->tpl_vars['ab']->value['amount']));?>
 <?php echo __('am');?>
 <?php echo smarty_modifier_date_weekday($_smarty_tpl->tpl_vars['key']->value);?>
 <?php echo __('von');?>
 <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['ab']->value['from'],'%H:%M');?>
 <?php echo __('bis');?>
 <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['ab']->value['until'],'%H:%M');?>
 <?php echo __('Uhr');?>
<br /><br /></li>
                                <?php }} ?>
                            <?php }} else { ?>
                                <li><strong><?php echo __('Keine Budgetzeiten für Deine Gruppe eingetragen!');?>
</strong></li>
                            <?php } ?>
                        <?php }else{ ?>
                            <li><strong><?php echo __('Du bist keiner Budgetgruppe zugeordnet!');?>
</strong></li>
                        <?php }?>
                        
                        <li>
                            <strong>
                            <?php if (!$_smarty_tpl->getVariable('cust')->value->allowCater()){?>
                                <?php echo __('Du hast keine Rechte eine Cateringbestellung für deine Firma zu starten');?>

                            <?php }?>
                            </strong>
                        </li>
                        
                        <li>
                            <strong>
                            <?php if (!$_smarty_tpl->getVariable('cust')->value->allowGreat()){?>
                                <?php echo __('Du hast keine Rechte eine Großhandelbestellung für deine Firma zu starten');?>

                            <?php }?>
                            </strong>
                        </li>
                        
                        <li><a href="/order_private/start">« <?php echo __('Hier klicken und eine private Bestellung aufgeben');?>
</a></li>
                    </ul>
                </div>
            </div>
        <?php }?>

    </div>



            </div>

        </div>

        <?php $_template = new Smarty_Internal_Template("base_footer.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        
        <div id="yd-discount-evaluation-wait" style="display:none;"><img src="<?php echo $_smarty_tpl->getVariable('domain_static')->value;?>
/images/yd-backend/yd-back-load.gif" /></div>
        
        <?php $_template = new Smarty_Internal_Template('_cookies/masterpixel/global.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value); echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php $_template = new Smarty_Internal_Template('_cookies/piwik/global.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value); echo $_template->getRenderedTemplate();?><?php unset($_template);?>

        <?php if ($_smarty_tpl->getVariable('config')->value->google->conversion){?>
            <?php $_template = new Smarty_Internal_Template('_cookies/google/adwords.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php }?>
    </body>
</html>
