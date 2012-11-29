<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:13:26
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/_cookies/janamesa/abtest.htm" */ ?>
<?php /*%%SmartyHeaderCode:36096986150252516819f64-33498072%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ffeaf275fdbc2d2f835098a15035eaecce4c486d' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/_cookies/janamesa/abtest.htm',
      1 => 1344611266,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '36096986150252516819f64-33498072',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<script type="text/javascript">
//<![CDATA[

var ydABTestGroup,ydABTestGroupName,i,x,y,ARRcookies=document.cookie.split(/\s*;\s*/);
ydABTestGroupName = 'ab_colorchange'
if(document.cookie.indexOf('yd-abtest=A') != -1) {
    ydABTestGroup="A";
} else if(document.cookie.indexOf('yd-abtest=B') != -1) {
    ydABTestGroup="B";
}
if (!ydABTestGroup) {
    if(Math.random() < 0.5) {
        ydABTestGroup = 'A';
    } else {
        ydABTestGroup = 'B';
    }
    var exdate=new Date("January 1, 2013 00:00:00");
    document.cookie="yd-abtest=" + ydABTestGroup + "; expires="+exdate.toUTCString() + "; path=/";     
}

if (ydABTestGroup == 'B') {
    var cssFile = '/media/css/www.janamesa.com.br/yd-frontend-abtest-b.css';

    if(document.createStyleSheet) {
        document.createStyleSheet(cssFile);
    } else {
        document.write('<link rel="stylesheet" type="text/css" href="' + cssFile + '">');
    }
}
//]]>
</script>