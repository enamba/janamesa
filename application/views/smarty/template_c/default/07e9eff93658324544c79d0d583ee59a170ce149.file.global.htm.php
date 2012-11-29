<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:03
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/sociomatic/global.htm" */ ?>
<?php /*%%SmartyHeaderCode:810559382509946870c0bf8-91471778%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '07e9eff93658324544c79d0d583ee59a170ce149' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/sociomatic/global.htm',
      1 => 1344946031,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '810559382509946870c0bf8-91471778',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'config' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_509946870cf699_66389356',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_509946870cf699_66389356')) {function content_509946870cf699_66389356($_smarty_tpl) {?><?php if ($_smarty_tpl->tpl_vars['config']->value->domain->base=='lieferando.de'){?>
<script id="sonar" type="text/javascript">
    /*<![CDATA[*/
    
    if (ydState.maybeLoggedIn()) {
        var sonar_customer = {
            identifier: $.cookie('YD_UID')
        };
    }
    
    // called by $(document).ready()    
    var track_sociomantic = function(){
        var s=document.createElement('script');
        s.id= 'sonar-adpan';
        s.type= 'text/javascript';
        s.async;
        s.src=('https:'==document.location.protocol?'https://':'http://')
            +'eu-sonar.sociomantic.com/js/2010-07-01/adpan/lieferando-de';
        var x=document.getElementById('sonar');
        x.parentNode.insertBefore(s,x);
    };  
    
    $(document).ready(function(){
        if (typeof no_sociomantic_auto_track == "undefined") {
            track_sociomantic();
        }      
    });
    
    /* ]]> */
</script>
<?php }?>
<?php }} ?>