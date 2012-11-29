<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/base_header.htm" */ ?>
<?php /*%%SmartyHeaderCode:169760125850994686e6bd98-54092094%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd89bc824c5aecb8146286aeaa38c5288a456af14' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/base_header.htm',
      1 => 1351185367,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '169760125850994686e6bd98-54092094',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50994686e6f761_93388725',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50994686e6f761_93388725')) {function content_50994686e6f761_93388725($_smarty_tpl) {?><div class="yd-header">
    <div class="yd-inner">
        <a class="yd-logo" href="/"><img src="http://cdn.yourdelivery.de/images/www.lieferando.de/Lieferando-de-Lieferservice-Logo.png" title="Lieferservice online finden mit Lieferando.de" alt="Beim Lieferservice online bestellen mit Lieferando.de" /></a>
        <div class="yd-bubble"></div>

        <?php echo $_smarty_tpl->getSubTemplate ('_header/login.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


    </div>
</div>
<?php }} ?>