<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:22:43
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/settings.htm" */ ?>
<?php /*%%SmartyHeaderCode:17278843395024c5f231f0f7-25810158%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6510ba32b76b8cbc4e7b53663da15af85ecd04c5' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/settings.htm',
      1 => 1344338315,
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
      1 => 1344611266,
      2 => 'file',
    ),
    '0ac1966404ba8a23198c844c1f464fb6444d6a4d' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_header.htm',
      1 => 1344338315,
      2 => 'file',
    ),
    '09802563734216776cde66933d8bd52815ab1c96' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_include/profileimage.htm',
      1 => 1341907317,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17278843395024c5f231f0f7-25810158',
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
        
        <?php $_template = new Smarty_Internal_Template('_cookies/janamesa/abtest.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        
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
$_template->assign('active',1);$_template->assign('active_title',__('Meine Daten'));$_template->properties['nocache_hash']  = '17278843395024c5f231f0f7-25810158';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:22:43
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

    <div class="yd-profile-body settings">

        <h1 class="<?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?> br-hidden <?php }?>"><?php echo __('Profilbild ändern');?>
</h1>

        <?php $_template = new Smarty_Internal_Template('user/_include/profileimage.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('redirect','settings');$_template->properties['nocache_hash']  = '17278843395024c5f231f0f7-25810158';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:22:43
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_include/profileimage.htm" */ ?>
<div class="yd-profile-picture">
    <div class="window"></div>
    
    <p>
        <?php echo __('Bitte lade hier Dein Profilbild hoch.<br />
        Das Profilbild wird neben Deinen Bewertungen zu sehen sein.<br /><br />
        Für das Profilbild erhältst Du 8 Treuepunkte.');?>

    </p>
    
    <form action="/user/profile?redirect=<?php echo (($tmp = @$_smarty_tpl->getVariable('redirect')->value)===null||$tmp==='' ? '' : $tmp);?>
" enctype="multipart/form-data" method="post">
        <input class="upload yd-empty-text" type="file" name="img" title="<?php echo __('Bild hochladen');?>
" />
        <input class="uniformbutton" type="submit" value="OK" />
    </form>
    
    <?php if ($_smarty_tpl->getVariable('cust')->value->hasProfileImage()){?>
    <img src="<?php echo $_smarty_tpl->getVariable('cust')->value->getProfileImage();?>
" />
    <a class="button" href="/user/profile/delete/<?php echo md5(time());?>
?redirect=<?php echo (($tmp = @$_smarty_tpl->getVariable('redirect')->value)===null||$tmp==='' ? '' : $tmp);?>
" id="yd-delete-profile-image"><?php echo __('Bild entfernen');?>
</a>
    <script type="text/javascript">
        <!--
        $('#yd-delete-profile-image').live('click',function(){
            if ( confirm("<?php echo __('Bist Du Dir sicher, dass Du dein Profilbild löschen willst?');?>
") ){
                return true;
            }
            return false;
        });
        -->
    </script>
    <?php }?>
    <em><?php echo __('Klicke hier, um Dein<br />Profilbild hochzuladen!');?>
</em>
</div>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_include/profileimage.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>

        <form action="/user/settings" class="yd-profile-form yd-clearfix fm yd-validation" method="post" id="yd-user-settings-form" enctype="multipart/form-data">

            <h1>
                <i><?php echo __('Um Daten zu ändern klicke bitte in das jeweilige Feld.');?>
</i>
                <?php echo __('Nutzername und Passwort');?>

            </h1>

            <ul>
                <li class="onelabel">
                    <label><?php echo __('E-Mail-Adresse');?>
</label> <input type="text" name="email" value="<?php echo $_smarty_tpl->getVariable('cust')->value->getEmail();?>
" class="validate[custom[email]]" id="email" <?php if ($_smarty_tpl->getVariable('cust')->value->isEmployee()){?>disabled="disabled"<?php }?> />
                </li>
                <li class="twolabel">
                    <label><?php echo __('Passwort');?>
</label> <input type="password" name="newpw" value="" class="yd-empty-text validate[length[0,20]]" id="newpw" autocomplete="off" title="******" />
                    <label><?php echo __('Passwort wiederholen');?>
</label> <input type="password" name="newpwagain" value="" class="yd-empty-text validate[confirm[newpw]]" id="newpwagain" autocomplete="off" title="******" />
                </li>

                <!--
                <li class="onelabel facebook">
                    <div class="fb-login-button" data-show-faces="false" data-width="600" data-max-rows="1"></div>
                    <span class="desc">Du kannst Dich jetzt auch mit deinem Facebook Acoount bei Lieferando anmelden.</span>
                </li>
                -->
            </ul>

            <h1>
                <i><?php echo __('Um Daten zu ändern, klicke bitte in das jeweilige Feld');?>
</i>
                <?php echo __('Persönliche Daten');?>

            </h1>

            <ul>
                <li class="twolabel">
                    <label><?php echo __('Vorname');?>
</label> <input type="text" name="prename" value="<?php echo $_smarty_tpl->getVariable('cust')->value->getPrename();?>
" class="validate[length[2,25]]" id="prename" />
                    <label><?php echo __('Nachname');?>
</label> <input type="text" name="name" value="<?php echo $_smarty_tpl->getVariable('cust')->value->getName();?>
" class="validate[length[2,25]]" id="name" />
                </li>
                <li class="twolabel" style="position:relative;">
                    <label><?php echo __('Geburtsdatum');?>
</label>
                    <select class="birthday_day" name="birthday_day">
                        <?php if ($_smarty_tpl->getVariable('cust')->value->getBirthday()=='0000-00-00'){?>
                        <option value="0" selected="selected">-</option>
                        <?php }?>
                        <?php unset($_smarty_tpl->tpl_vars['smarty']->value['section']['day']);
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['name'] = 'day';
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start'] = (int)1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['loop'] = is_array($_loop=32) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'] = ((int)1) == 0 ? 1 : (int)1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['loop'];
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start'] < 0)
    $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start'] = max($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'] > 0 ? 0 : -1, $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['loop'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start']);
else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start'] = min($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'] > 0 ? $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['loop'] : $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['loop']-1);
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['total'] = min(ceil(($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'] > 0 ? $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['loop'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start'] : $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start']+1)/abs($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'])), $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['max']);
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']['day']['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']['day']['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']['day']['total']);
?>
                        <option <?php if ($_smarty_tpl->getVariable('cust')->value->getBirthday()!='0000-00-00'&&date('d',strtotime($_smarty_tpl->getVariable('cust')->value->getBirthday()))==$_smarty_tpl->getVariable('smarty')->value['section']['day']['index']){?>selected="selected"<?php }?>>
                            <?php echo $_smarty_tpl->getVariable('smarty')->value['section']['day']['index'];?>

                        </option>
                        <?php endfor; endif; ?>
                    </select>
                    <select class="birthday_month" name="birthday_month">
                        <?php if ($_smarty_tpl->getVariable('cust')->value->getBirthday()=='0000-00-00'){?>
                        <option value="0" selected="selected">-</option>
                        <?php }?>
                        <?php unset($_smarty_tpl->tpl_vars['smarty']->value['section']['month']);
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['name'] = 'month';
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start'] = (int)1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['loop'] = is_array($_loop=13) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'] = ((int)1) == 0 ? 1 : (int)1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['loop'];
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start'] < 0)
    $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start'] = max($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'] > 0 ? 0 : -1, $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['loop'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start']);
else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start'] = min($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'] > 0 ? $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['loop'] : $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['loop']-1);
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['total'] = min(ceil(($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'] > 0 ? $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['loop'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start'] : $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start']+1)/abs($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'])), $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['max']);
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']['month']['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']['month']['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']['month']['total']);
?>
                        <option <?php if ($_smarty_tpl->getVariable('cust')->value->getBirthday()!='0000-00-00'&&date('m',strtotime($_smarty_tpl->getVariable('cust')->value->getBirthday()))==$_smarty_tpl->getVariable('smarty')->value['section']['month']['index']){?>selected="selected"<?php }?>>
                            <?php echo $_smarty_tpl->getVariable('smarty')->value['section']['month']['index'];?>

                        </option>
                        <?php endfor; endif; ?>
                    </select>
                    <select class="birthday_year" name="birthday_year">
                        <?php if ($_smarty_tpl->getVariable('cust')->value->getBirthday()=='0000-00-00'){?>
                        <option value="0" selected="selected">--</option>
                        <?php }?>
                        <?php unset($_smarty_tpl->tpl_vars['smarty']->value['section']['year']);
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['name'] = 'year';
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start'] = (int)1920;
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['loop'] = is_array($_loop=2011) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'] = ((int)1) == 0 ? 1 : (int)1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['loop'];
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start'] < 0)
    $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start'] = max($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'] > 0 ? 0 : -1, $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['loop'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start']);
else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start'] = min($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'] > 0 ? $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['loop'] : $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['loop']-1);
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['total'] = min(ceil(($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'] > 0 ? $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['loop'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start'] : $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start']+1)/abs($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'])), $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['max']);
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']['year']['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']['year']['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']['year']['total']);
?>
                        <option <?php if ($_smarty_tpl->getVariable('cust')->value->getBirthday()!='0000-00-00'&&date('Y',strtotime($_smarty_tpl->getVariable('cust')->value->getBirthday()))==$_smarty_tpl->getVariable('smarty')->value['section']['year']['index']){?>selected="selected"<?php }?>>
                            <?php echo $_smarty_tpl->getVariable('smarty')->value['section']['year']['index'];?>

                        </option>
                        <?php endfor; endif; ?>
                    </select>
                    <label style="position: absolute;top:6px;left:352px;"><?php echo __('Ich bin');?>
</label>
                    <select name="sex" style="position: absolute;top:0;left:516px;">
                        <?php if ($_smarty_tpl->getVariable('cust')->value->getSex()=='n'){?>
                            <option style="width: 157px;" value="n" <?php if ($_smarty_tpl->getVariable('cust')->value->getSex()=='n'){?>selected="selected"<?php }?>><?php echo __('keine Angaben');?>
</option>
                        <?php }?>
                        <option style="width: 157px;" value="m" <?php if ($_smarty_tpl->getVariable('cust')->value->getSex()=='m'){?>selected="selected"<?php }?>><?php echo __('Männlich');?>
</option>
                        <option style="width: 157px;" value="w" <?php if ($_smarty_tpl->getVariable('cust')->value->getSex()=='w'){?>selected="selected"<?php }?>><?php echo __('Weiblich');?>
</option>
                    </select>
                </li>
                <li class="twolabel">
                    <label><?php echo __('Handynummer/Telefon');?>
</label> <input type="text" name="tel" value="<?php echo $_smarty_tpl->getVariable('cust')->value->getTel();?>
" id="tel" class="yd-form-invalid"/>
                </li>
            </ul>

            <h1>
                <?php echo __('Newsletter');?>

            </h1>

            <ul>
                <li class="onelabel">
                    <p>
                        <?php echo __('Hast Du schon unseren Newsletter abonniert? Halte Dich auf dem Laufenden über Aktionen, Gutscheine und andere Infos rund um Lieferando.');?>

                        <br /><br />
                        <label style="text-align:left"><?php echo __('Newsletter abonniert?');?>
</label> <input type="checkbox" value="1" name="newsletter" <?php if ($_smarty_tpl->getVariable('cust')->value->getNewsletter()){?>checked="checked"<?php }?> />  
                        <div id="yd-lang-confirm-newsletter-delete" class="hidden"><?php echo __('Bist Du Dir sicher, dass Du unseren Newsletter abbestellen möchtest?');?>
</div>
                    </p>
                </li>
            </ul>

            <input type="submit" class="button" value="<?php echo __('Änderungen speichern');?>
" />
            <br /><br /><br /><br />
		</form>

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
