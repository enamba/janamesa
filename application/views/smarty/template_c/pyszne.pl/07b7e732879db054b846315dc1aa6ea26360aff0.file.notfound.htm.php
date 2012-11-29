<?php /* Smarty version Smarty-3.0.7, created on 2012-07-27 07:32:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/pyszne.pl/error/notfound.htm" */ ?>
<?php /*%%SmartyHeaderCode:200917285050126e22a6ac56-25091477%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '07b7e732879db054b846315dc1aa6ea26360aff0' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/pyszne.pl/error/notfound.htm',
      1 => 1338548803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '200917285050126e22a6ac56-25091477',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="pl" xml:lang="pl" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="pl" xml:lang="pl" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="pl" xml:lang="pl" class="ie8"> <![endif]-->
<!--[if gt IE 8 ]> <html lang="pl" xml:lang="pl" class="ie9"> <![endif]-->
<!--[!IE]>   <!--> <html lang="pl" xml:lang="pl">         <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title><?php echo $_smarty_tpl->getVariable('config')->value->domain->base;?>
</title>
        <link rel="stylesheet" type="text/css" href="/media/css/compiled/frontend-<?php echo (($tmp = @$_smarty_tpl->getVariable('extra_css')->value)===null||$tmp==='' ? 'no' : $tmp);?>
-<?php echo $_smarty_tpl->getVariable('REVISION')->value;?>
.css" />
        <script type="text/javascript" src="/media/javascript/compiled/frontend-sem.js"></script>

        <style>
            
            body {
                background: #fff;
            }

            #yd-fail {
                position: relative;
                width: 500px;
                padding: 0 0 0 300px;
                margin: 50px auto;
                background: url(http://cdn.yourdelivery.de/images/yd-background/yd-error.jpg) no-repeat;
            }

            #yd-fail h1 {
                font: 46px FuturaCondensedMediumCE, "trebuchet ms", arial, "nimbus sans l", sans-serif;
            }

            #yd-fail h2 {
                font: 28px FuturaCondensedMediumCE, "trebuchet ms", arial, "nimbus sans l", sans-serif;
                color: #999;
            }

            #yd-fail p {
                margin: 15px 0;
                font-size: 14px;
            }

            #yd-fail p strong {
                display: block;
                font-weight: normal;
                margin: 5px 0 0;
            }

            #yd-fail h3 {
                position: absolute;
                top: 180px;
                left: 298px;
                width: 480px;
                height: 30px;
                text-align: center;
                font-size: 18px;
            }

            #yd-fail #yd-plz-search {
                position: absolute;
                top: 195px;
                left: 295px;
                width: 200px;
                height: 45px;
                line-height: 45px;
            }

            #yd-fail .yd-button-280 {
                position: absolute;
                top: 194px;
                left: 502px;
            }

        </style>

        <?php $_template = new Smarty_Internal_Template('_cookies/google/analytics.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php if ($_smarty_tpl->getVariable('trackGoogleEcomerce')->value){?>
            <?php $_template = new Smarty_Internal_Template('_cookies/google/ecommerce.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php }?>

    </head>

    <body>
        
        <?php $_template = new Smarty_Internal_Template('base_header.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>

        <div id="yd-fail">
            <h1><?php echo __("UUUPS!");?>
</h1>
            <h2><?php echo __("Der Versuch lief ins Leere...");?>
</h2>
            <p>
                <?php echo __("Wenn Du Dich verlaufen hast, hilft Dir unser Support gerne weiter:");?>

                <strong><a href="mailto:<?php echo $_smarty_tpl->getVariable('config')->value->locale->email->support;?>
"><?php echo $_smarty_tpl->getVariable('config')->value->locale->email->support;?>
</a> - <?php echo __("Tel.: %s",$_smarty_tpl->getVariable('config')->value->locale->tel->support);?>
</strong>
            </p>
        </div>
        
        <div class="pl-autocomplete-wrapper">
            <div class="pl-autocomplete-arrow">wpisz adres <br /> dostawy</div>
            <?php $_template = new Smarty_Internal_Template('order/_includes/plz.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        </div>

        <?php $_template = new Smarty_Internal_Template('_cookies/piwik/global.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value); echo $_template->getRenderedTemplate();?><?php unset($_template);?>

    </body>

</html>