<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/google/analytics.htm" */ ?>
<?php /*%%SmartyHeaderCode:130037954050994686e26f61-87318046%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a8158007f2b45cd4729fc9111c2bd1621e73a8f5' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/google/analytics.htm',
      1 => 1351185367,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '130037954050994686e26f61-87318046',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'GADocumentReady' => 0,
    'googleAccounts' => 0,
    'ua' => 0,
    'config' => 0,
    'cust' => 0,
    'GATrackPageview' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50994686e5ecb1_58259355',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50994686e5ecb1_58259355')) {function content_50994686e5ecb1_58259355($_smarty_tpl) {?><!-- Google Analytics -->
<script type="text/javascript">
    var _gaq = _gaq || [];
    
    <?php if ($_smarty_tpl->tpl_vars['GADocumentReady']->value){?>$(document).ready(function() { <?php }?>
    
    <?php  $_smarty_tpl->tpl_vars['ua'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ua']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['googleAccounts']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ua']->key => $_smarty_tpl->tpl_vars['ua']->value){
$_smarty_tpl->tpl_vars['ua']->_loop = true;
?>
        log('adding analytics for <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['ua']->value, ENT_QUOTES, 'UTF-8');?>
');
        
        <?php if ($_smarty_tpl->tpl_vars['config']->value->domain->base=='eat-star.de'){?>
            _gaq.push(['_setDomainName', '.eat-star.de']);
        <?php }?>
        
        _gaq.push(['_setAccount', '<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['ua']->value, ENT_QUOTES, 'UTF-8');?>
']);
        
        <?php if (is_object($_smarty_tpl->tpl_vars['cust']->value)&&$_smarty_tpl->tpl_vars['cust']->value->isLoggedIn()){?>
            _gaq.push(['_setCustomVar', 4, 'has_logged_in', 'yes', 1]);
        <?php }?>
        
        if(typeof(ydABTestGroup) != 'undefined' && typeof(ydABTestGroupName) != 'undefined') {
            _gaq.push(['_setCustomVar', 5, ydABTestGroupName, ydABTestGroup, 2]);
        }
        
        _gaq.push(['_trackPageview'<?php if (isset($_smarty_tpl->tpl_vars['GATrackPageview']->value)){?>, '<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['GATrackPageview']->value, ENT_QUOTES, 'UTF-8');?>
'<?php }?>]);
    <?php } ?>
    
    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
    
    <?php if ($_smarty_tpl->tpl_vars['GADocumentReady']->value){?> } ); <?php }?>
</script>


<?php }} ?>