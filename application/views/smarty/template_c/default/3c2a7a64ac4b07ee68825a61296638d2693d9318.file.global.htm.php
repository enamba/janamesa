<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:03
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/piwik/global.htm" */ ?>
<?php /*%%SmartyHeaderCode:211987357650994687112c78-92206761%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3c2a7a64ac4b07ee68825a61296638d2693d9318' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/piwik/global.htm',
      1 => 1338548803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '211987357650994687112c78-92206761',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'GATrackPageview' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_509946871207b6_62187826',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_509946871207b6_62187826')) {function content_509946871207b6_62187826($_smarty_tpl) {?><!-- Piwik -->
<script type="text/javascript">
    try{   
        _paq.push(['trackPageView'<?php if (isset($_smarty_tpl->tpl_vars['GATrackPageview']->value)){?>, '<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['GATrackPageview']->value, ENT_QUOTES, 'UTF-8');?>
'<?php }?>]);
        var d=document,
        g=d.createElement('script'),
        s=d.getElementsByTagName('script')[0];
        g.type='text/javascript';
        g.defer=true;
        g.async=true;
        g.src=pkBaseURL+'piwik.js';
        s.parentNode.insertBefore(g,s);
        log('logging piwik');
    }
    catch ( err ){
        log('error logging with piwik ' + err);
    }
</script><?php }} ?>