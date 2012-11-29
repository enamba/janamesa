<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/veinteractive/global.htm" */ ?>
<?php /*%%SmartyHeaderCode:86557395850994686e63fd8-19321076%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6a432181e5d4dc04593373c268fce2a2f3f603db' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/veinteractive/global.htm',
      1 => 1338548803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '86557395850994686e63fd8-19321076',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'journeycode' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50994686e69f85_93837310',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50994686e69f85_93837310')) {function content_50994686e69f85_93837310($_smarty_tpl) {?>    <script type="text/javascript">
    /*<![CDATA[*/
    var journeycode='<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['journeycode']->value, ENT_QUOTES, 'UTF-8');?>
';
    var captureConfigUrl='rcs.veinteractive.com/CaptureConfigService.asmx/CaptureConfig';
    (function(){
    var ve = document.createElement('script');
    ve.type = 'text/javascript';
    ve.async = true;
    ve.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'config1.veinteractive.com/vecapturev6.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(ve, s);
    })();
    /* ]]> */
    </script>

<?php }} ?>