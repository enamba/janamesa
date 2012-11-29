<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/loginfailed.htm" */ ?>
<?php /*%%SmartyHeaderCode:29696749050994686d68e41-42114698%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6c4cdc5d4639f4de84867943559f6afd7e09b5c1' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/loginfailed.htm',
      1 => 1344946031,
      2 => 'file',
    ),
    '4f58a2b68d3ce1d14c1e0047210224be7eb87689' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/base.htm',
      1 => 1351185367,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '29696749050994686d68e41-42114698',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'additionalMetatags' => 0,
    'meta' => 0,
    'customTitle' => 0,
    'canonical' => 0,
    'extra_css' => 0,
    'REVISION' => 0,
    'mode' => 0,
    'grid' => 0,
    'jvm_brot_fuer_die_welt' => 0,
    'APPLICATION_ENV' => 0,
    'trackGoogleEcomerce' => 0,
    'config' => 0,
    'this' => 0,
    'dis' => 0,
    'domain_base' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50994686e16d96_28986380',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50994686e16d96_28986380')) {function content_50994686e16d96_28986380($_smarty_tpl) {?><!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="de" xml:lang="de" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="de" xml:lang="de" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="de" xml:lang="de" class="ie8"> <![endif]-->
<!--[if gt IE 8 ]> <html lang="de" xml:lang="de" class="ie9"> <![endif]-->
<!--[!IE]>   <!--> <html lang="de" xml:lang="de">         <!--<![endif]-->
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="author" content="yourdelivery GmbH" />
        <meta name="publisher" content="yourdelivery GmbH" />

        <!-- custom meta tags -->
        <?php  $_smarty_tpl->tpl_vars['meta'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['meta']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['additionalMetatags']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['meta']->key => $_smarty_tpl->tpl_vars['meta']->value){
$_smarty_tpl->tpl_vars['meta']->_loop = true;
?>
        <?php echo $_smarty_tpl->tpl_vars['meta']->value;?>

        <?php } ?>

        <meta name="viewport" content="width=1100">

        <!-- page title -->
        <title><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['customTitle']->value)===null||$tmp==='' ? 'lieferando.de - Lieferservice, Catering, Getränke & Obstkisten ... Online bestellen - Bargeldlos bezahlen' : $tmp), ENT_QUOTES, 'UTF-8');?>
</title>

        <?php if ($_smarty_tpl->tpl_vars['canonical']->value){?>
        <link rel="canoncial" href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['canonical']->value, ENT_QUOTES, 'UTF-8');?>
" />
        <?php }?>

        <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-57x57-precomposed.png" /> <!-- iPhone -->
        <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-72x72-precomposed.png" sizes="72x72" /> <!-- iPad -->
        <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-114x114-precomposed.png" sizes="114x114" /> <!-- iPhone 4+ -->
        <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-144x144-precomposed.png" sizes="144x144" /> <!-- iPad 3+ -->
        <link rel="SHORTCUT ICON" href="/favicon.ico" type="image/x-icon" />

        <link rel="stylesheet" type="text/css" href="/media/css/compiled/frontend-<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['extra_css']->value)===null||$tmp==='' ? 'no' : $tmp), ENT_QUOTES, 'UTF-8');?>
-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['REVISION']->value, ENT_QUOTES, 'UTF-8');?>
.css" />

        <script type="text/javascript" src="/media/javascript/compiled/frontend-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['REVISION']->value, ENT_QUOTES, 'UTF-8');?>
.js"></script>
        <!-- define global state of this page -->
        <script type="text/javascript">
            YdMode = "<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['mode']->value)===null||$tmp==='' ? 'rest' : $tmp), ENT_QUOTES, 'UTF-8');?>
";
            log('CURRENT MODE: ' + YdMode);
            var companyExceptions = [1443];
        </script>

        <?php if ($_smarty_tpl->tpl_vars['grid']->value){?>
        <script type='text/javascript' charset='UTF-8'>
        /* <![CDATA[ */
            <?php echo $_smarty_tpl->tpl_vars['grid']->value->getHeaderScript();?>

        /* ]]> */
        </script>
        <?php }?>

        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/monetate/global.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


        <?php if ($_smarty_tpl->tpl_vars['jvm_brot_fuer_die_welt']->value){?>
        <script language="javascript">AC_FL_RunContent = 0;</script>
        <script src="/media/extern/jvm/brot-fuer-die-welt/AC_RunActiveContent.js" language="javascript"></script>
        <?php }?>

        <?php if ($_smarty_tpl->tpl_vars['APPLICATION_ENV']->value=="production"){?>
        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/wingify/vwo.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

        <?php }?>
        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/google/analytics.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

        <?php if ($_smarty_tpl->tpl_vars['trackGoogleEcomerce']->value){?>
        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/google/ecommerce.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

        <?php }?>
        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/veinteractive/global.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('journeycode'=>$_smarty_tpl->tpl_vars['config']->value->veinteractive->code), 0);?>


        <link href="https://plus.google.com/114849467427479787318" rel="publisher" />

        
        <script type="text/javascript" src="https://apis.google.com/js/plusone.js">
            {lang: 'de'}
        </script>
        

    </head>

    <body class="de" itemscope itemtype="http://schema.org/WebPage">

        <div id="yd-group-fade" class="hidden"></div>
        <a name="anchor-0"></a>
        <div id="yd-nonfooter">

            <?php echo $_smarty_tpl->getSubTemplate ('base_header.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


            <div id="yd-content" class="yd-clearfix">

                <?php echo $_smarty_tpl->getSubTemplate ('notification.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


                

<div class="yd-login-failed yd-box">

    <strong><?php echo htmlspecialchars(__('Ungültige E-Mail / Passwort Kombination'), ENT_QUOTES, 'UTF-8');?>
</strong>
    <p><?php echo htmlspecialchars(__('Unsere Passwörter beachten die Groß- und Kleinschreibung. Bitte überprüfe auch die Shifttaste/Feststelltaste.'), ENT_QUOTES, 'UTF-8');?>
</p>
    
    <form class="fo" action="/user/login" method="post" name="login" >
        <input type="hidden" name="login" value="1" />
        <ul>
            <li>
                <a class="yd-facebook-login" id="fb-login"><?php echo htmlspecialchars(__('Mit Facebook anmelden'), ENT_QUOTES, 'UTF-8');?>
</a>
            </li>
            <li>
                <label><?php echo htmlspecialchars(__('E-Mail-Adresse:'), ENT_QUOTES, 'UTF-8');?>
</label>
                <input id="email" type="text" value="" name="user" />
            </li>
            <li>
                <label><?php echo htmlspecialchars(__('Passwort:'), ENT_QUOTES, 'UTF-8');?>
</label>
                <input id="pass" type="password" value="" name="pass" />
            </li>
            <li>
                <input class="yd-button-280" type="submit" value="<?php echo htmlspecialchars(__('Einloggen'), ENT_QUOTES, 'UTF-8');?>
" />
            </li>
            <li>
                <a class="yd-forgotten-pass"><?php echo htmlspecialchars(__('Passwort vergessen?'), ENT_QUOTES, 'UTF-8');?>
</a>
            </li>
        </ul>
    </form>

</div>



            </div>

        </div>

        <?php echo $_smarty_tpl->getSubTemplate ("base_footer.htm", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


        <script type="text/javascript">
            <!--
            <?php  $_smarty_tpl->tpl_vars['dis'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['dis']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['this']->value->getDisabledElements(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['dis']->key => $_smarty_tpl->tpl_vars['dis']->value){
$_smarty_tpl->tpl_vars['dis']->_loop = true;
?>
            if ( $("#<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dis']->value, ENT_QUOTES, 'UTF-8');?>
").length > 0 ){
                $("#<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dis']->value, ENT_QUOTES, 'UTF-8');?>
").hide();
            }
            <?php } ?>
            -->
        </script>

        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/sociomatic/global.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/masterpixel/global.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('uadomain'=>$_smarty_tpl->tpl_vars['domain_base']->value), 0);?>

        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/piwik/global.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('uadomain'=>$_smarty_tpl->tpl_vars['domain_base']->value), 0);?>

        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/avazu/global.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

        <?php echo $_smarty_tpl->getSubTemplate ('_cookies/google/adwords-remarketing.htm', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


    </body>
</html>
<?php }} ?>