<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 05:41:53
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/locations.htm" */ ?>
<?php /*%%SmartyHeaderCode:6302181375024c9515d4dd2-35605555%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ebc63c5fed506c124f377839a6415f67ee1ce750' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/locations.htm',
      1 => 1341829247,
      2 => 'file',
    ),
    '500ab6653ea4acc5cbc2ba27f15f67d57314f1eb' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_base.htm',
      1 => 1338548803,
      2 => 'file',
    ),
    '625186780f2b60433d8c6e4e515e1828215ad902' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_base/janamesa.com.br.htm',
      1 => 1344436321,
      2 => 'file',
    ),
    '0ac1966404ba8a23198c844c1f464fb6444d6a4d' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_header.htm',
      1 => 1344338315,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '6302181375024c9515d4dd2-35605555',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="pt" xml:lang="pt" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="pt" xml:lang="pt" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="pt" xml:lang="pt" class="ie8"> <![endif]-->
<!--[if gt IE 8 ]> <html lang="pt" xml:lang="pt" class="ie9"> <![endif]-->
<!--[!IE]>   <!--> <html lang="pt" xml:lang="pt">         <!--<![endif]-->
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex, follow" />
        <meta name="viewport" content="width=1100">

        <link rel="stylesheet" type="text/css" href="/media/css/yd-frontend-user-fonts.css" />
        <link rel="stylesheet" type="text/css" href="/media/css/yd-frontend-step2.css" />
        <link rel="stylesheet" type="text/css" href="/media/css/yd-frontend-user-uniform.css" />
        <link rel="stylesheet" type="text/css" href="/media/css/compiled/frontend-<?php echo (($tmp = @$_smarty_tpl->getVariable('extra_css')->value)===null||$tmp==='' ? 'no' : $tmp);?>
-<?php echo $_smarty_tpl->getVariable('REVISION')->value;?>
.css" />

        <script type="text/javascript" src="/media/javascript/compiled/frontend-test.js"></script>

        <title>
            <?php if ($_smarty_tpl->getVariable('cust')->value->isLoggedIn()){?>
                <?php echo __('Profil von %s bei %s',$_smarty_tpl->getVariable('cust')->value->getFullname(),$_smarty_tpl->getVariable('config')->value->domain->base);?>

            <?php }else{ ?>
                <?php echo __('Profil von %s bei %s','Gast',$_smarty_tpl->getVariable('config')->value->domain->base);?>

            <?php }?>
        </title>

        <script>
            $(function(){
                $(".yd-profile-picture input, .yd-profile-picture textarea, .yd-profile-picture select, .yd-profile-picture button").uniform();
            });
        </script>

        <?php $_template = new Smarty_Internal_Template('_cookies/google/analytics.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php if ($_smarty_tpl->getVariable('trackGoogleEcomerce')->value){?>
            <?php $_template = new Smarty_Internal_Template('_cookies/google/ecommerce.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php }?>
    </head>

    <body class="br yd-user-content">

        <div id="fb-root"></div>
        
        <script>
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1&appId=APP_ID";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        

        <div id="yd-nonfooter">

            <?php $_template = new Smarty_Internal_Template("notification.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
            
            <?php $_template = new Smarty_Internal_Template("base_header.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>

            <div id="yd-content" class="yd-clearfix">

                

<div class="yd-profile yd-clearfix">

	<?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div class="br-inner-box"><?php }?>

    <?php $_template = new Smarty_Internal_Template('user/_header.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('active',2);$_template->assign('active_title',__('Lieferadressen'));$_template->properties['nocache_hash']  = '6302181375024c9515d4dd2-35605555';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 05:41:53
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_header.htm" */ ?>
<div class="yd-profile-head">

    <a href="/order_private/start" class="yd-button-140" ><?php echo __('Essen bestellen');?>
</a>

    <ul>
        <li><a class="<?php if ($_smarty_tpl->getVariable('active')->value==0){?>active<?php }?>" href="/user/index"><?php echo $_smarty_tpl->getVariable('cust')->value->getFullname();?>
</a></li>
        <li>
            <a class="active">
                <?php echo $_smarty_tpl->getVariable('active_title')->value;?>

                <span class="hidden yd-user-form-error"><?php echo __('Bitte klicke in die rot markierten Felder und überprüfe sie.');?>
</span>
            </a>
        </li>
    </ul>

</div>

<div class="yd-profile-nav yd-box br-box">

    <ul>
    	<li><a href="/user/index" class="yd-pn0 <?php if ($_smarty_tpl->getVariable('active')->value==0){?>active<?php }?>"><span><?php echo __('Perfil');?>
</span></a></li>
        <li><a href="/user/settings" class="yd-pn1 <?php if ($_smarty_tpl->getVariable('active')->value==1){?>active<?php }?>"><span><?php echo __('Meine Daten');?>
</span></a></li>
        <li><a href="/user/socialnetworks" class="yd-pn7 <?php if ($_smarty_tpl->getVariable('active')->value==7){?>active<?php }?>"><span><?php echo __('Soziale Netzwerke');?>
</span></a></li>
        <li><a href="/user/locations" class="yd-pn2 <?php if ($_smarty_tpl->getVariable('active')->value==2){?>active<?php }?>"><span><?php echo __('Lieferadressen');?>
</span></a></li>
        <li><a href="/user/favourites" class="yd-pn3 <?php if ($_smarty_tpl->getVariable('active')->value==3){?>active<?php }?>"><span><?php echo __('Favoriten');?>
</span></a></li>
        <li><a href="/user/orders/perPageuser_orders/25" class="yd-pn4 <?php if ($_smarty_tpl->getVariable('active')->value==4){?>active<?php }?>"><span><?php echo __('Vorherige Bestellungen');?>
</span></a></li>
        <li><a href="/user/ratings" class="yd-pn5 <?php if ($_smarty_tpl->getVariable('active')->value==5){?>active<?php }?>"><span><?php echo __('Restaurant bewerten');?>
</span></a></li>
        <li><a href="/user/fidelity" class="yd-pn6 <?php if ($_smarty_tpl->getVariable('active')->value==6){?>active<?php }?>"><span><?php echo __('Treuepunkte');?>
</span></a></li>
    </ul>

    <!-- <a class="yd-profile-button"><?php echo __('Jetzt Freunde einladen');?>
</a> -->

</div>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_header.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>

    <div class="yd-profile-body">

        <div id="dialog-locations" class="yd-dialog-parent yd-dialogs hidden"><a class="yd-dialogs-close"></a>

            <div class="yd-dialogs-head">
                <h2><?php echo __('Neue Lieferadresse anlegen');?>
</h2>
            </div>

            <div class="yd-dialogs-body">

                <form class="yd-new-address-form" action="/request_user_location/create" method="post" style="width: 380px; margin: 0 auto;">    

                    <div class="yd-form">
                        <div class="yd-form-wrapper">
                            <ul class="yd-clearfix">
                                <li class="yd-form-left">
                                    <span><?php echo __('Straße');?>
</span>
                                    <input type="text" name="street">                                    
                                    <div class="formError nameformError" style="display:none; top: -30px; left: 140px">
                                        <div class="formErrorContent"></div>
                                        <div class="formErrorArrow">
                                            <div class="line10"></div>
                                            <div class="line9"></div>
                                            <div class="line8"></div>
                                            <div class="line7"></div>
                                            <div class="line6"></div>
                                            <div class="line5"></div>
                                            <div class="line4"></div>
                                            <div class="line3"></div>
                                            <div class="line2"></div>
                                            <div class="line1"></div>
                                        </div>
                                    </div>
                                </li>
                                <li class="yd-form-right">
                                    <span><?php echo __('Nr');?>
</span>
                                    <input type="text" name="hausnr" size="4" />
                                    <div class="formError nameformError" style="display:none; top: -30px; left: 140px">
                                        <div class="formErrorContent"></div>
                                        <div class="formErrorArrow">
                                            <div class="line10"></div>
                                            <div class="line9"></div>
                                            <div class="line8"></div>
                                            <div class="line7"></div>
                                            <div class="line6"></div>
                                            <div class="line5"></div>
                                            <div class="line4"></div>
                                            <div class="line3"></div>
                                            <div class="line2"></div>
                                            <div class="line1"></div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <ul class="yd-clearfix">
                                <li class="yd-form-left">
                                    <span><?php echo __('Postleitzahl');?>
</span>
                                    <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
                                        <a id="br-cep-autocomplete" class="br-cep-location">Não sabe seu CEP? Clique aqui</a>
                                    <?php }?>
                                    <input class="hidden" type="hidden" name="cityId" value="" />
                                    <input type="text" id="plz" name="plz" value="" class="yd-plz-autocomplete yd-only-nr" />
                                    <div class="formError nameformError" style="display:none; top: -30px; left: 140px">
                                        <div class="formErrorContent"></div>
                                        <div class="formErrorArrow">
                                            <div class="line10"></div>
                                            <div class="line9"></div>
                                            <div class="line8"></div>
                                            <div class="line7"></div>
                                            <div class="line6"></div>
                                            <div class="line5"></div>
                                            <div class="line4"></div>
                                            <div class="line3"></div>
                                            <div class="line2"></div>
                                            <div class="line1"></div>
                                        </div>
                                    </div>
                                </li>
                                <li class="yd-form-right">
                                    <span><?php echo __('Telefon');?>
</span>
                                    <input type="text" name="tel" />
                                    <div class="formError nameformError" style="display:none; top: -30px; left: 140px">
                                        <div class="formErrorContent"></div>
                                        <div class="formErrorArrow">
                                            <div class="line10"></div>
                                            <div class="line9"></div>
                                            <div class="line8"></div>
                                            <div class="line7"></div>
                                            <div class="line6"></div>
                                            <div class="line5"></div>
                                            <div class="line4"></div>
                                            <div class="line3"></div>
                                            <div class="line2"></div>
                                            <div class="line1"></div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <ul class="yd-clearfix">
                                  <li class="yd-form-left">
                                    <span><?php echo __('Firma');?>
</span>
                                    <input type="text" name="companyName" />
                                </li>                                
                                <li class="yd-form-right">
                                    <span><?php echo __('Stockwerk');?>
</span>
                                    <input type="text" name="etage" size="4" />
                                </li>
                            </ul>
                            <ul class="yd-clearfix">
                                <li class="yd-form-left">
                                    <span><?php echo __('Lieferanweisungen');?>
</span>
                                    <textarea name="comment"></textarea>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <a class="yd-button-190 yd-create-location" href="#new_address" name="new_address"><?php echo __('Adresse speichern');?>
</a>

                </form>

            </div>

            <div class="yd-dialogs-footer"></div>

        </div>

        <h1 class="locations">
            <a class="yd-profile-blue-button " id="yd-user-location-create"><?php echo __('Neue Lieferadresse anlegen');?>
</a>
            <?php echo __('Lieferadressen');?>

        </h1>
		
        <div class="table yd-profile-table locations-old">
            <div class="thead">
                <div class="th"><?php echo __('Straße');?>
</div>
                <div class="th"><?php echo __('Nr');?>
</div>
                <div class="th"><?php echo __('Postleitzahl');?>
</div>
                <div class="th"><?php echo __('Telefon');?>
</div>
                <div class="th"><?php echo __('Lieferanweisungen');?>
</div>
                <div class="th"><?php echo __('Stockwerk');?>
</div>
                <div class="th"><?php echo __('Firma');?>
</div>
                <div class="th"><?php echo __('Bearbeiten');?>
</div>
            </div>

            <?php  $_smarty_tpl->tpl_vars['location'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getLocations(null,false); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['location']->key => $_smarty_tpl->tpl_vars['location']->value){
?>

            <form class="tr yd-form-toggle" action="/request_user_location/edit" method="post">
                <input class="hidden" type="hidden" name="id" value="<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" />
                <input class="hidden" type="hidden" name="cityId" value="<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getId();?>
" />
                <input class="hidden" type="hidden" name="plz" value="<?php echo $_smarty_tpl->getVariable('location')->value->getPlz();?>
" />
                <div class="td"><textarea style="width: 80px" name="street"><?php echo $_smarty_tpl->getVariable('location')->value->getStreet();?>
</textarea></div>
                <div class="td"><input style="width: 20px" type="text" value="<?php echo $_smarty_tpl->getVariable('location')->value->getHausnr();?>
" name="hausnr" size="4" /></div>
                <div class="td"><span style="display:block;padding:5px"><?php echo $_smarty_tpl->getVariable('location')->value->getPlz();?>
 <?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getCity();?>
</span></div>
                <div class="td"><input style="width: 75px" type="text" value="<?php echo $_smarty_tpl->getVariable('location')->value->getTel();?>
" name="tel" /></div>
                <div class="td"><textarea style="width: 80px" name="comment"><?php echo $_smarty_tpl->getVariable('location')->value->getComment();?>
</textarea></div>
                <div class="td"><input style="width: 30px" type="text" value="<?php echo $_smarty_tpl->getVariable('location')->value->getEtage();?>
" name="etage" /></div>             
                <div class="td"><textarea style="width: 80px" name="companyName"><?php echo $_smarty_tpl->getVariable('location')->value->getCompanyName();?>
</textarea></div>
                <div class="td">
                    <span class="yd-please-wait hidden"><img src="<?php echo $_smarty_tpl->getVariable('domain_static')->value;?>
/images/ajax-loader-16-white.gif" /></span>
                    <a class="td-edit yd-form-toggle locations-edit" title="<?php echo __('Adresse editieren');?>
" href="#location_<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" name="location_<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" id="location_<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
"></a>
                    <a class="td-check yd-edit-location locations-check" title="<?php echo __('Speichern');?>
" style="display:none"></a>
                    <a class="td-heart yd-heart-location <?php if ($_smarty_tpl->getVariable('location')->value->isPrimary()){?>active<?php }?>" title="<?php echo __('Primäre Adresse');?>
" id="yd-heart-location-<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
"></a>
                    <a href="/user/locations/del/<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" class="td-delete locations-delete" title="<?php echo __('Adresse löschen');?>
" onclick="if(!confirm('<?php echo __('Adresse löschen?');?>
')){return false;}"></a>
                    <a class="td-letsgo locations-letsgo yd-start-order-from-address" title="<?php echo __('Jetzt dorthin bestellen');?>
" id="yd-addr-<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
-<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getId();?>
" href="/<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getRestUrl();?>
"></a>
                </div>
            </form>

            <?php }} ?>
            
        </div>





        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>

			<div class="yd-profile-top-border"></div>
	        <div class="table yd-profile-table locations-new">
	
	            <?php  $_smarty_tpl->tpl_vars['location'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cust')->value->getLocations(null,false); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['location']->key => $_smarty_tpl->tpl_vars['location']->value){
?>
	
	            <form class="tr yd-form-toggle" action="/request_user_location/edit" method="post">
	                <input class="hidden" type="hidden" name="id" value="<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" />
	                <input class="hidden" type="hidden" name="cityId" value="<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getId();?>
" />
	                <input class="hidden" type="hidden" name="plz" value="<?php echo $_smarty_tpl->getVariable('location')->value->getPlz();?>
" />
	                <div class="td">
	                	<textarea class="locations-textarea" name="street"><?php echo $_smarty_tpl->getVariable('location')->value->getStreet();?>
</textarea> 
	                	<input class="locations-house" type="text" value="<?php echo $_smarty_tpl->getVariable('location')->value->getHausnr();?>
" name="hausnr" size="5" />, 
	                	<?php echo $_smarty_tpl->getVariable('location')->value->getPlz();?>
 <?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getCity();?>

	                	<input style="width:75px;display:none" type="text" value="<?php echo $_smarty_tpl->getVariable('location')->value->getTel();?>
" name="tel" />
	                	<div class="comments-locations"><textarea name="comment"><?php echo $_smarty_tpl->getVariable('location')->value->getComment();?>
</textarea></div>
	                	<input style="width:30px;display:none;" type="text" value="<?php echo $_smarty_tpl->getVariable('location')->value->getEtage();?>
" name="etage" />
	                	<textarea style="width:80px;display:none;" name="companyName"><?php echo $_smarty_tpl->getVariable('location')->value->getCompanyName();?>
</textarea>
	                </div>
	                <div class="td">
	                    <span class="yd-please-wait hidden"><img src="<?php echo $_smarty_tpl->getVariable('domain_static')->value;?>
/images/ajax-loader-16-white.gif" /></span>
	                    <a class="td-edit yd-form-toggle locations-edit" title="<?php echo __('Adresse editieren');?>
" href="#location_<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" name="location_<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" id="location_<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
"></a>
	                    <a class="td-check yd-edit-location locations-check" title="<?php echo __('Speichern');?>
" style="display:none"></a>
	                    <a class="td-heart yd-heart-location <?php if ($_smarty_tpl->getVariable('location')->value->isPrimary()){?>active<?php }?>" title="<?php echo __('Primäre Adresse');?>
" id="yd-heart-location-<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
"></a>
	                    <a href="/user/locations/del/<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
" class="td-delete locations-delete" title="<?php echo __('Adresse löschen');?>
" onclick="if(!confirm('<?php echo __('Adresse löschen?');?>
')){return false;}"></a>
	                    <a class="td-letsgo locations-letsgo yd-start-order-from-address" title="<?php echo __('Jetzt dorthin bestellen');?>
" id="yd-addr-<?php echo $_smarty_tpl->getVariable('location')->value->getId();?>
-<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getId();?>
" href="/<?php echo $_smarty_tpl->getVariable('location')->value->getCity()->getRestUrl();?>
"></a>
	                </div>
	            </form>
	
	            <?php }} ?>
	            
	        </div>        
        
        <?php }?>





        
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?> <a class="yd-profile-blue-button " id="yd-user-location-create"><?php echo __('Neue Lieferadresse anlegen');?>
</a> <?php }?>

    </div>
    
    <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div><?php }?>

</div>


            </div>

        </div>

        <?php $_template = new Smarty_Internal_Template("base_footer.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        
        <?php $_template = new Smarty_Internal_Template('_cookies/masterpixel/global.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value); echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php $_template = new Smarty_Internal_Template('_cookies/piwik/global.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value); echo $_template->getRenderedTemplate();?><?php unset($_template);?>

    </body>
</html>
