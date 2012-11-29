<?php /* Smarty version Smarty-3.0.7, created on 2012-08-09 13:36:41
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/_header/login.htm" */ ?>
<?php /*%%SmartyHeaderCode:19581559435023e719d93982-95854492%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '21f7aeade05861cc9d13dbe529b95a0cb6d5a138' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/_header/login.htm',
      1 => 1344436321,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19581559435023e719d93982-95854492',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<div class="yd-login-wrapper yd-logged-out">

    <div class="yd-login-window hidden">
        <form name="login" action="/user/login" method="post" id="login">
            <input type="hidden" name="login" value="1" />
            <input class="yd-empty-text" type="text" name="user" value="" title="<?php echo __('E-Mail-Adresse');?>
" />
            <input class="yd-empty-text" type="password" name="pass" value="" title="<?php echo __('Passwort');?>
" />
            <input class="" type="submit" name="yd-login" value="<?php echo __('Login');?>
" /> 
            <?php $_template = new Smarty_Internal_Template('order/_includes/form_order.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('postfix','login'); echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        </form>

        <div class="br-register"><a title="Crie sua conta" href="/user/register">Crie sua conta</a></div>
        <div class="br-label-login">Login</div>
        <div class="br-login-left">
            <a class="yd-facebook-login" id="fb-login"><img src="/media/css/www.janamesa.com.br/images/facebookconnect.png"/></a><br/>
        </div>

        <a class="yd-forgotten-pass"><?php echo __('Passwort vergessen?');?>
</a>

    </div>
</div>
<div id="fb-root"></div>
<div id="logout" class="yd-logged-in hidden">
    <span class="yd-customer-name"></span><em class="yd-customer-company hidden"></em>

    | <a href="/user/index" class="yd-icon-account"><?php echo __('Account');?>
</a>

    | <a href="/user/logout" class="yd-icon-logout yd-log-out" id="yd-logout"><?php echo __('Abmelden');?>
</a>

    <div id="yd-customer-company-admin" class="hidden">
        <a href="/company" class="yd-icon-company"><?php echo __('Firmenadmin');?>
</a>
    </div>

</div>
