<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/monetate/global.htm" */ ?>
<?php /*%%SmartyHeaderCode:188701536450994686e1a9e4-95985340%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3b7bc2a7f8812df7d96439303c232c028f373ded' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/monetate/global.htm',
      1 => 1351185367,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '188701536450994686e1a9e4-95985340',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'config' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50994686e24516_41181248',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50994686e24516_41181248')) {function content_50994686e24516_41181248($_smarty_tpl) {?><?php if ($_smarty_tpl->tpl_vars['config']->value->domain->base=='lieferando.de'){?>
<!-- Begin Monetate tag v6. Place at start of document head. DO NOT ALTER. -->
<script type="text/javascript">
var monetateT = new Date().getTime();
(function() {
    var p = document.location.protocol;
    if (p == "http:" || p == "https:") {
        var m = document.createElement('script'); m.type = 'text/javascript'; m.async = true; m.src = (p == "https:" ? "https://s" : "http://") + "b.monetate.net/js/1/a-57ad1f4f/p/lieferando.de/" + Math.floor((monetateT + 2163535) / 3600000) + "/g";
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(m, s);
    }
})();
</script>
<!-- End Monetate tag. -->
<?php }?>
<?php }} ?>