<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/private/start.htm" */ ?>
<?php /*%%SmartyHeaderCode:19033554595024c5d4797762-44452781%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fad2b6257915d9ddaa3d6c37692e49ff32e3d7e7' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/private/start.htm',
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
    '89750610137ccf57addc4169bc073491060cdf75' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/fidelity.htm',
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
    'b2fd69186c270b784ca65eba8bf7bff0e68a0f7b' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/start/private_address.htm',
      1 => 1338548803,
      2 => 'file',
    ),
    '2c4f624b3d235413692c9bc86db72de67ea2544b' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/masterpixel/orderflow.htm',
      1 => 1338548803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19033554595024c5d4797762-44452781',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
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
    <?php $_template = new Smarty_Internal_Template("order/_includes/sidebar/fidelity.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('propose',true);$_template->properties['nocache_hash']  = '19033554595024c5d4797762-44452781';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/fidelity.htm" */ ?>
<?php if ($_smarty_tpl->getVariable('config')->value->domain->base!='eat-star.de'&&$_smarty_tpl->getVariable('cust')->value->isLoggedIn()){?>   
<div class="yd-box" id="fidelity-box">
	<div class="br-inner-box">
        <div class="yd-box-title">
            <span><?php echo __('Treuepunkte');?>
</span>
            
            <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><h3><div>Seus Bônus</div></h3><?php }?>
            
            <!--<a id="how-fidelity-works"></a>-->
        </div>
    
        <div id="fidelity-content">
            <?php $_smarty_tpl->tpl_vars['countpoints'] = new Smarty_variable($_smarty_tpl->getVariable('cust')->value->getFidelityPoints()->getPoints(), null, null);?>
            <div class="yd-box-body countpoints-<?php echo $_smarty_tpl->getVariable('countpoints')->value;?>
" id="valid-points">
    
                <div class="yd-100-container">
                    <div class="yd-100-timeline">
                        <div class="yd-100-current yd-100-<?php echo min($_smarty_tpl->getVariable('countpoints')->value,100);?>
">
                            <div class="yd-100-current-point"><?php echo $_smarty_tpl->getVariable('countpoints')->value;?>
</div>
                        </div>
                        <div class="yd-100-inactive-point">100</div>
                    </div>
                </div>
    
                <p class="current-point-text" style="text-align:center;font-size:12px;margin:0;">
                    <?php ob_start();?><?php echo $_smarty_tpl->getVariable('cust')->value->getFidelity()->getPoints();?>
<?php $_tmp1=ob_get_clean();?><?php echo __('Du hast bisher %d Treuepunkte gesammelt.',$_tmp1);?>

                </p>
    
                <?php if ($_smarty_tpl->getVariable('cust')->value->isLoggedIn()&&$_smarty_tpl->getVariable('cust')->value->getFidelity()->isCashinReached()&&is_object($_smarty_tpl->getVariable('order')->value)&&is_object($_smarty_tpl->getVariable('order')->value->getService())&&!$_smarty_tpl->getVariable('order')->value->getService()->isOnlycash()){?>
                <div>
                    <br /><br />
                    <strong>
                        <a class="yd-cash-fidelity-points"><?php echo __('Treuepunkte einlösen');?>
</a>
                    </strong>
                    <input type="hidden" id="yd-fidelity-no-meal-found" value="<?php echo __('In deinem Warenkorb befindet sich keine Ware für die Treuepunkte eingelöste werden können.');?>
" />
                </div>
                <?php }?>
    
                <div id="how-fidelity-works-content" class="hidden">
                    <br />
                    <p>
                        <?php echo __('Für die erste Bestellung mit anschließender Registrierung erhältst Du 35 Punkte, für jede weitere Bestellung jeweils 10 Punkte. Registrierst Du Dich später, erhältst Du 20 Punkte. Wir freuen uns, wenn Du die Restaurants bewertest und uns sagst, wie zufrieden du warst. Dafür gibt es jeweils 5 Punkte. Weitere Treuepunkte-Aktionen sind in Vorbereitung, lass Dich überraschen.');?>

                    </p>
                    <br />
                </div>
    
                <?php if ($_smarty_tpl->getVariable('propose')->value&&$_smarty_tpl->getVariable('cust')->value->getFidelity()){?>
                
                    <?php if (count($_smarty_tpl->getVariable('customer')->value->getFidelity()->getOpenActions())>0){?>
                        <span class="title-daisy-duck" style="display:block; border-bottom: 1px solid #aaa; margin: 0 0 5px; padding: 0 0 5px; text-align:center;">
                            <br />
                            <?php echo __('Sammel jetzt noch mehr Treuepunkte:');?>

                        </span>
                    <?php }?>
                
                    <ul class="daisy-duck">
                        <!-- propose -->
                        <?php  $_smarty_tpl->tpl_vars['openAction'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getFidelity()->getOpenActions(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['openAction']->key => $_smarty_tpl->tpl_vars['openAction']->value){
?>
                        <?php  $_smarty_tpl->tpl_vars['action'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['openAction']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['action']->key => $_smarty_tpl->tpl_vars['action']->value){
?>
                        <li class="daisy-duck-do-something yd-clearfix">
                            <a href="<?php echo $_smarty_tpl->tpl_vars['action']->value['call2action'];?>
" target="_blank">
                                <span class="dd2"><?php echo $_smarty_tpl->tpl_vars['action']->value['info'];?>
</span>
                                <span class="dd3"><span class="yd-coin"><?php echo $_smarty_tpl->tpl_vars['action']->value['points'];?>
</span></span>
                            </a>
                        </li>
                        <?php }} ?>
                        <?php }} ?>
                    </ul>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<?php }?>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/sidebar/fidelity.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>

    <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div class="yd-box br-box"><?php }?>

        <?php $_template = new Smarty_Internal_Template("order/_includes/sidebar/lastorder.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '19033554595024c5d4797762-44452781';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
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
$_template->properties['nocache_hash']  = '19033554595024c5d4797762-44452781';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
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
$_template->properties['nocache_hash']  = '19033554595024c5d4797762-44452781';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
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

    <?php if ($_smarty_tpl->getVariable('cust')->value->isEmployee()&&$_smarty_tpl->getVariable('domain_base')->value!='eat-star.de'){?>
    <div class="yd-box">

        <div class="yd-bigbox-head">
            <?php echo __("Account wählen");?>

        </div>

        <div class="yd-bigbox-body yd-clearfix">
            <a id="nav_order_01" class="yd-account-priv yd-set-kind-priv active">
                <?php echo __("Privataccount");?>

            </a>
            <a href="/order_company/start" id="nav_order_02" class="yd-account-comp yd-set-kind-comp">
                <?php echo __("Firmenaccount");?>
<br />
                <i><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('cust')->value->getCompany()->getName(),20," ...",true);?>
</i>
            </a>
        </div>

    </div>
    <?php }?>

    <div class="yd-box br-address">

        <div class="yd-bigbox-head">
            <?php echo __('Lieferadresse wählen');?>


            <br />
            <a id="yd-add-address" class="yd-start-adress-icon" href="/user/locations#new_address"><?php echo __('Adressen hinzufügen');?>
</a>
        </div>

        <div class="yd-bigbox-body <?php if ($_smarty_tpl->getVariable('cust')->value->getLocations()->count()>0||$_smarty_tpl->getVariable('cust')->value->getCompanyLocations()->count()>0){?> is-address <?php }?>">
            <?php $_smarty_tpl->tpl_vars['temp'] = new Smarty_variable(1, null, null);?>

            <?php if ($_smarty_tpl->getVariable('cust')->value->getLocations()->count()==0&&$_smarty_tpl->getVariable('cust')->value->getCompanyLocations()->count()==0){?>
            <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
            <p>Pedindo de casa ou do trabalho? Não importa, adicione quantos endereços quiser e faça seus próximos pedidos ainda máis rápidos.</p>

            <a id="yd-add-address" href="/user/locations#new_address"><?php echo __('Adressen hinzufügen');?>
</a>
            <?php }else{ ?>
            <a class="cursor yd-adress" id="yd-add-address-0"><?php echo __('Noch keine Adressen eingetragen.');?>
</a>
            <?php }?>
            <?php }else{ ?>

            <?php $_template = new Smarty_Internal_Template("order/_includes/start/company_address.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '19033554595024c5d4797762-44452781';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
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

            <?php $_template = new Smarty_Internal_Template("order/_includes/start/private_address.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '19033554595024c5d4797762-44452781';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/start/private_address.htm" */ ?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
?><?php  $_smarty_tpl->tpl_vars['location'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getLocations(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['location']->key => $_smarty_tpl->tpl_vars['location']->value){
?>
    <span class="yd-adress <?php if ($_smarty_tpl->getVariable('location')->value->isPrimary()||(count($_smarty_tpl->getVariable('cust')->value->getLastOrder())>0&&!$_smarty_tpl->getVariable('cust')->value->hasPrimaryLocation()&&$_smarty_tpl->getVariable('location')->value->getCityId()==$_smarty_tpl->getVariable('cust')->value->getLastOrder()->getLocation()->getCityId())){?>active<?php }?>"
        id="yd-addr-<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
-<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getId();?>
" data="/<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getUrl($_smarty_tpl->getVariable('mode')->value);?>
">
		
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div><?php }?>
            <?php if ($_smarty_tpl->getVariable('domain_base')->value!='taxiresto.fr'){?>
                <?php echo $_smarty_tpl->getVariable('location')->value->getStreet();?>
 <?php echo $_smarty_tpl->getVariable('location')->value->getHausnr();?>
, 
            <?php }else{ ?>
                <?php echo $_smarty_tpl->getVariable('location')->value->getHausnr();?>
 <?php echo $_smarty_tpl->getVariable('location')->value->getStreet();?>
, 
            <?php }?>
            <?php echo $_smarty_tpl->getVariable('location')->value->getPlz();?>
 <?php echo $_smarty_tpl->getVariable('location')->value->getOrt()->getOrt();?>

    
            <a href="/user/locations#location_<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
"><?php echo __('bearbeiten');?>
</a>
    
            <?php if ($_smarty_tpl->getVariable('location')->value->getAddition()!=''){?>
                <br />
                <i><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('location')->value->getAddition(),80," ...",false);?>
</i>
            <?php }?>
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div><?php }?>
    </span>
<?php }} ?>

<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/start/private_address.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>

            <?php }?>
        </div>

    </div>
    <?php if ($_smarty_tpl->getVariable('cust')->value->getLocations()->count()!=0||$_smarty_tpl->getVariable('cust')->value->getCompanyLocations()->count()!=0){?>
    <a class="yd-button-240" id="yd-start-order-from-address" href="#" style="margin: 0 auto;"><?php echo __("Bestellung jetzt starten");?>
</a>

    <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
    <a id="yd-add-address" class="yd-button-240" href="/user/locations#new_address"><?php echo __('Adressen hinzufügen');?>
</a>
    <?php }?>
    <?php }?>
</div>

<?php $_template = new Smarty_Internal_Template('_cookies/masterpixel/orderflow.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value);$_template->assign('step1',1);$_template->assign('step2',0);$_template->assign('step3',0);$_template->assign('step4',0);$_template->properties['nocache_hash']  = '19033554595024c5d4797762-44452781';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:14:06
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/masterpixel/orderflow.htm" */ ?>
<?php if ($_smarty_tpl->getVariable('APPLICATION_ENV')->value=="production"){?>

    <?php if ($_smarty_tpl->getVariable('uadomain')->value=='lieferando.de'){?>
        <img src="http://altfarm.mediaplex.com/ad/bk/18421-129492-3840-0?step1=<?php echo $_smarty_tpl->getVariable('step1')->value;?>
&amp;step2=<?php echo $_smarty_tpl->getVariable('step2')->value;?>
&amp;step3=<?php echo $_smarty_tpl->getVariable('step3')->value;?>
&amp;step4=<?php echo $_smarty_tpl->getVariable('step4')->value;?>
&amp;mpuid=<?php echo time();?>
" height="1" width="1" alt="Mediaplex_tag" />
        
        <?php if ($_smarty_tpl->getVariable('order')->value){?>
            <?php if ($_smarty_tpl->getVariable('order')->value->getMode()=='rest'){?>
                <iframe src="http://img-cdn.mediaplex.com/0/18421/universal.html?page_name=orderfunnel_lieferdienst&Orderfunnel_Lieferdienst=1&amp;mpuid=<?php echo time();?>
" height="1" width="1" frameborder="0"></iframe>
            <?php }elseif($_smarty_tpl->getVariable('order')->value->getMode()=='cater'){?>
                <iframe src="http://img-cdn.mediaplex.com/0/18421/universal.html?page_name=orderfunnel_catering&Orderfunnel_Catering=1&amp;mpuid=<?php echo time();?>
" height="1" width="1" frameborder="0"></iframe>
            <?php }elseif($_smarty_tpl->getVariable('order')->value->getMode()=='great'){?>
                <iframe src="http://img-cdn.mediaplex.com/0/18421/universal.html?page_name=orderfunnel_supermarkt&Orderfunnel_Supermarkt=1&amp;mpuid=<?php echo time();?>
" height="1" width="1" frameborder="0"></iframe>
            <?php }?>
        <?php }?>
        
    <?php }elseif($_smarty_tpl->getVariable('uadomain')->value=='lieferando.at'){?>
        <img src="http://altfarm.mediaplex.com/ad/bk/18589-131108-3840-0?at_step1=<?php echo $_smarty_tpl->getVariable('step1')->value;?>
&amp;at_step2=<?php echo $_smarty_tpl->getVariable('step2')->value;?>
&amp;at_step3=<?php echo $_smarty_tpl->getVariable('step3')->value;?>
&amp;at_step4=<?php echo $_smarty_tpl->getVariable('step4')->value;?>
&amp;mpuid=<?php echo time();?>
" height="1" width="1" alt="Mediaplex_tag" />
    <?php }elseif($_smarty_tpl->getVariable('uadomain')->value=='lieferando.ch'){?>
        <img src="http://altfarm.mediaplex.com/ad/bk/18590-131109-3840-0?ch_step1=<?php echo $_smarty_tpl->getVariable('step1')->value;?>
&amp;ch_step2=<?php echo $_smarty_tpl->getVariable('step2')->value;?>
&amp;ch_step3=<?php echo $_smarty_tpl->getVariable('step3')->value;?>
&amp;ch_step4=<?php echo $_smarty_tpl->getVariable('step4')->value;?>
&amp;mpuid=<?php echo time();?>
" height="1" width="1" alt="Mediaplex_tag" />
    <?php }elseif($_smarty_tpl->getVariable('uadomain')->value=='taxiresto.fr'){?>  
        <img src="http://altfarm.mediaplex.com/ad/bk/18591-131110-3840-0?fr_step1=<?php echo $_smarty_tpl->getVariable('step1')->value;?>
&amp;fr_step2=<?php echo $_smarty_tpl->getVariable('step2')->value;?>
&amp;fr_step3=<?php echo $_smarty_tpl->getVariable('step3')->value;?>
&amp;fr_step4=<?php echo $_smarty_tpl->getVariable('step4')->value;?>
&amp;mpuid=<?php echo time();?>
" height="1" width="1" alt="Mediaplex_tag" />
    <?php }elseif($_smarty_tpl->getVariable('uadomain')->value=='elpedido.es'){?>
    
    <?php }elseif($_smarty_tpl->getVariable('uadomain')->value=='appetitos.it'){?>

    <?php }elseif($_smarty_tpl->getVariable('uadomain')->value=='smakuje.pl'){?>
    
    <?php }?>
    
<?php }else{ ?>

    <img src="/null.php?step1=<?php echo $_smarty_tpl->getVariable('step1')->value;?>
&amp;step2=<?php echo $_smarty_tpl->getVariable('step2')->value;?>
&amp;step3=<?php echo $_smarty_tpl->getVariable('step3')->value;?>
&amp;step4=<?php echo $_smarty_tpl->getVariable('step4')->value;?>
&amp;mpuid=<?php echo time();?>
" height="1" width="1" alt="Mediaplex_tag" />
    <?php if ($_smarty_tpl->getVariable('order')->value){?>
        <?php if ($_smarty_tpl->getVariable('order')->value->getMode()=='rest'){?>
            <iframe src="/null.php?page_name=orderfunnel_lieferdienst&amp;Orderfunnel_Lieferdienst=1&amp;mpuid=<?php echo time();?>
" height="1" width="1" frameborder="0"></iframe>
        <?php }elseif($_smarty_tpl->getVariable('order')->value->getMode()=='cater'){?>
            <iframe src="/null.php?page_name=orderfunnel_catering&amp;Orderfunnel_Catering=1&amp;mpuid=<?php echo time();?>
" height="1" width="1" frameborder="0"></iframe>
        <?php }elseif($_smarty_tpl->getVariable('order')->value->getMode()=='great'){?>
            <iframe src="/null.php?page_name=orderfunnel_supermarkt&amp;Orderfunnel_Supermarkt=1&amp;mpuid=<?php echo time();?>
" height="1" width="1" frameborder="0"></iframe>
        <?php }?>
    <?php }?>
<?php }?><?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/masterpixel/orderflow.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>



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
