<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_header/login.htm" */ ?>
<?php /*%%SmartyHeaderCode:187480360150994686e71037-59819716%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1b555a8babb5486899fb3d9eebeda2b4627107c5' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_header/login.htm',
      1 => 1351866047,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '187480360150994686e71037-59819716',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50994686eafb25_30713195',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50994686eafb25_30713195')) {function content_50994686eafb25_30713195($_smarty_tpl) {?><div class="yd-login-wrapper yd-logged-out">

    <a class="yd-header-login">
        <?php echo htmlspecialchars(__('Einloggen'), ENT_QUOTES, 'UTF-8');?>

        <span class="yd-facebook-icon"></span>
    </a>
    <a class="yd-header-register" href="/user/register"><?php echo htmlspecialchars(__('Registrieren'), ENT_QUOTES, 'UTF-8');?>
</a>   

    <div class="yd-login-window hidden">

        <div class="yd-login-window-top">

            <div class="yd-login-window-fb hidden">
                <a class="yd-facebook-login" id="fb-login"><?php echo htmlspecialchars(__('Mit Facebook anmelden'), ENT_QUOTES, 'UTF-8');?>
</a>
                <span><?php echo htmlspecialchars(__('oder'), ENT_QUOTES, 'UTF-8');?>
</span>
            </div>

            <div class="yd-login-window-yd">
                <strong><?php echo htmlspecialchars(__('Login mit %s-Account:',Default_Helpers_Web::getDomainName()), ENT_QUOTES, 'UTF-8');?>
</strong>
                <form name="login" action="/user/login" method="post" id="login">
                    <input type="hidden" name="login" value="1" />
                    <input class="yd-empty-text" type="text" name="user" value="" title="<?php echo htmlspecialchars(__('E-Mail-Adresse'), ENT_QUOTES, 'UTF-8');?>
" />
                    <input class="yd-empty-text" type="password" name="pass" value="" title="<?php echo htmlspecialchars(__('Passwort'), ENT_QUOTES, 'UTF-8');?>
" />
                    <input class="" type="submit" name="yd-login" value="<?php echo htmlspecialchars(__('Login'), ENT_QUOTES, 'UTF-8');?>
" /> 
                    <?php echo $_smarty_tpl->getSubTemplate ('order/_includes/form_order.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('postfix'=>'login'), 0);?>

                </form>
                <a class="yd-forgotten-pass"><?php echo htmlspecialchars(__('Passwort vergessen?'), ENT_QUOTES, 'UTF-8');?>
</a>
            </div>

        </div>

        <a class="yd-login-window-toggle"><?php echo htmlspecialchars(__('Fragen zum Login?'), ENT_QUOTES, 'UTF-8');?>
</a>

        <div class="yd-login-window-bottom hidden">
            <p>
                <?php echo htmlspecialchars(__('Verbinde jetzt ganz bequem Dein %s-Profil mit Deinem Facebook-Account und teile Deinen Freunden mit, was Du eben Leckeres bestellt hast.',Default_Helpers_Web::getDomainName()), ENT_QUOTES, 'UTF-8');?>

            </p>
            <p>
                <?php echo htmlspecialchars(__('Du kannst die Verknüpfung mit Facebook jederzeit löschen. Deine Daten werden vertraulich behandelt und nicht an Dritte weitergegeben.'), ENT_QUOTES, 'UTF-8');?>

            </p>
            <p>
                <?php echo __('Hast Du noch Fragen, wie das genau mit dem Facebook-Login funktioniert? Dann klick einfach auf %sdiesen Link%s.','<a href="/faq#facebook">','</a>');?>

            </p>
        </div>

    </div>
</div>
<div id="fb-root"></div>
<div id="logout" class="yd-logged-in hidden">
    <span class="yd-customer-name"></span><em class="yd-customer-company hidden"></em>

    | <a href="/user/index" class="yd-icon-account"><?php echo htmlspecialchars(__('Account'), ENT_QUOTES, 'UTF-8');?>
</a>

    | <a href="/user/logout" class="yd-icon-logout yd-log-out" id="yd-logout"><?php echo htmlspecialchars(__('Abmelden'), ENT_QUOTES, 'UTF-8');?>
</a>

    <div id="yd-customer-company-admin" class="hidden">
        <a href="/company" class="yd-icon-company"><?php echo htmlspecialchars(__('Firmenadmin'), ENT_QUOTES, 'UTF-8');?>
</a>
    </div>

</div>
<?php }} ?>