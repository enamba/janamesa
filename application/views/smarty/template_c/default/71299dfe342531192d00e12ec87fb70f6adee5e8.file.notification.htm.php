<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:03
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/notification.htm" */ ?>
<?php /*%%SmartyHeaderCode:3642680655099468708c070-85563531%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '71299dfe342531192d00e12ec87fb70f6adee5e8' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/notification.htm',
      1 => 1338548803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3642680655099468708c070-85563531',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'notifications' => 0,
    'type' => 0,
    'domain_static' => 0,
    'notification' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_509946870b9760_08842254',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_509946870b9760_08842254')) {function content_509946870b9760_08842254($_smarty_tpl) {?><div id="yd-notifications">
    
    <!-- Javascript enabled? -->
    <noscript>
        <div class="notification error">
            <ul>
                <li id="notification-error-1000000">
                    <?php echo htmlspecialchars(__('Sie haben Javascript deaktiviert. Diese Seite ist nur mit aktiviertem Javascript nutzbar. Bitte aktivieren Sie Javascript in Ihrem Browser und rufen Sie diese Seite erneut auf.'), ENT_QUOTES, 'UTF-8');?>

                </li>
            </ul>
        </div>
    </noscript>
    
    <?php if ($_smarty_tpl->tpl_vars['notifications']->value){?>
        <?php  $_smarty_tpl->tpl_vars['notification'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['notification']->_loop = false;
 $_smarty_tpl->tpl_vars['type'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['notifications']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['notification']->key => $_smarty_tpl->tpl_vars['notification']->value){
$_smarty_tpl->tpl_vars['notification']->_loop = true;
 $_smarty_tpl->tpl_vars['type']->value = $_smarty_tpl->tpl_vars['notification']->key;
?>
            <div class="notification <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['type']->value, ENT_QUOTES, 'UTF-8');?>
">
                <a href="#" class="closeNotification" id="notification-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['type']->value, ENT_QUOTES, 'UTF-8');?>
">
                    <img src="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['domain_static']->value, ENT_QUOTES, 'UTF-8');?>
/images/yd-background/notification-close.png" alt="close" />
                </a>
                <ul>
                <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['notification']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['noteIter']['iteration']=0;
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['noteIter']['iteration']++;
?>
                    <li id="notification-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['type']->value, ENT_QUOTES, 'UTF-8');?>
-<?php echo htmlspecialchars($_smarty_tpl->getVariable('smarty')->value['foreach']['noteIter']['iteration'], ENT_QUOTES, 'UTF-8');?>
">
                        <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['item']->value, ENT_QUOTES, 'UTF-8');?>

                    </li>
                <?php } ?>
                </ul>
            </div>
        <?php } ?>
    <?php }?>
    
</div>
<?php }} ?>