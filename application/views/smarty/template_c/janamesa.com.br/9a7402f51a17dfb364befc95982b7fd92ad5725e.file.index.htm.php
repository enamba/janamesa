<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:22:37
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:5415750735024c5e85fa052-70576680%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9a7402f51a17dfb364befc95982b7fd92ad5725e' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/index.htm',
      1 => 1338548803,
      2 => 'file',
    ),
    '500ab6653ea4acc5cbc2ba27f15f67d57314f1eb' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_base.htm',
      1 => 1338548803,
      2 => 'file',
    ),
    '625186780f2b60433d8c6e4e515e1828215ad902' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_base/janamesa.com.br.htm',
      1 => 1344611266,
      2 => 'file',
    ),
    '0ac1966404ba8a23198c844c1f464fb6444d6a4d' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_header.htm',
      1 => 1344338315,
      2 => 'file',
    ),
    '09802563734216776cde66933d8bd52815ab1c96' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_include/profileimage.htm',
      1 => 1341907317,
      2 => 'file',
    ),
    'eb6b21ca364d8d1130f2f74d1caf22cc62fd82e7' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/service/services.htm',
      1 => 1344442021,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '5415750735024c5e85fa052-70576680',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="pt" xml:lang="pt" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="pt" xml:lang="pt" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="pt" xml:lang="pt" class="ie8"> <![endif]-->
<!--[if gt IE 8 ]> <html lang="pt" xml:lang="pt" class="ie9"> <![endif]-->
<!--[!IE]>   <!--> <html lang="pt" xml:lang="pt">         <!--<![endif]-->
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex, follow" />
        <meta name="viewport" content="width=1100">

        <link rel="stylesheet" type="text/css" href="/media/css/yd-frontend-user-fonts.css" />
        <link rel="stylesheet" type="text/css" href="/media/css/yd-frontend-step2.css" />
        <link rel="stylesheet" type="text/css" href="/media/css/yd-frontend-user-uniform.css" />
        <link rel="stylesheet" type="text/css" href="/media/css/compiled/frontend-<?php echo (($tmp = @$_smarty_tpl->getVariable('extra_css')->value)===null||$tmp==='' ? 'no' : $tmp);?>
-<?php echo $_smarty_tpl->getVariable('REVISION')->value;?>
.css" />

        <script type="text/javascript" src="/media/javascript/compiled/frontend-test.js"></script>
        
        <?php $_template = new Smarty_Internal_Template('_cookies/janamesa/abtest.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        
        <title>
            <?php if ($_smarty_tpl->getVariable('cust')->value->isLoggedIn()){?>
                <?php echo __('Profil von %s bei %s',$_smarty_tpl->getVariable('cust')->value->getFullname(),$_smarty_tpl->getVariable('config')->value->domain->base);?>

            <?php }else{ ?>
                <?php echo __('Profil von %s bei %s','Gast',$_smarty_tpl->getVariable('config')->value->domain->base);?>

            <?php }?>
        </title>

        <script>
            $(function(){
                $(".yd-profile-picture input, .yd-profile-picture textarea, .yd-profile-picture select, .yd-profile-picture button").uniform();
            });
        </script>

        <?php $_template = new Smarty_Internal_Template('_cookies/google/analytics.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php if ($_smarty_tpl->getVariable('trackGoogleEcomerce')->value){?>
            <?php $_template = new Smarty_Internal_Template('_cookies/google/ecommerce.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php }?>
    </head>

    <body class="br yd-user-content">

        <div id="fb-root"></div>
        
        <script>
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1&appId=APP_ID";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        

        <div id="yd-nonfooter">

            <?php $_template = new Smarty_Internal_Template("notification.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
            
            <?php $_template = new Smarty_Internal_Template("base_header.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>

            <div id="yd-content" class="yd-clearfix">

                

<div class="yd-profile yd-clearfix">

	<?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div class="br-inner-box"><?php }?>

    <?php $_template = new Smarty_Internal_Template('user/_header.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('active',0);$_template->assign('active_title',__('Profilübersicht'));$_template->properties['nocache_hash']  = '5415750735024c5e85fa052-70576680';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:22:37
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_header.htm" */ ?>
<div class="yd-profile-head">

    <a href="/order_private/start" class="yd-button-140" ><?php echo __('Essen bestellen');?>
</a>

    <ul>
        <li><a class="<?php if ($_smarty_tpl->getVariable('active')->value==0){?>active<?php }?>" href="/user/index"><?php echo $_smarty_tpl->getVariable('cust')->value->getFullname();?>
</a></li>
        <li>
            <a class="active">
                <?php echo $_smarty_tpl->getVariable('active_title')->value;?>

                <span class="hidden yd-user-form-error"><?php echo __('Bitte klicke in die rot markierten Felder und überprüfe sie.');?>
</span>
            </a>
        </li>
    </ul>

</div>

<div class="yd-profile-nav yd-box br-box">

    <ul>
    	<li><a href="/user/index" class="yd-pn0 <?php if ($_smarty_tpl->getVariable('active')->value==0){?>active<?php }?>"><span><?php echo __('Perfil');?>
</span></a></li>
        <li><a href="/user/settings" class="yd-pn1 <?php if ($_smarty_tpl->getVariable('active')->value==1){?>active<?php }?>"><span><?php echo __('Meine Daten');?>
</span></a></li>
        <li><a href="/user/socialnetworks" class="yd-pn7 <?php if ($_smarty_tpl->getVariable('active')->value==7){?>active<?php }?>"><span><?php echo __('Soziale Netzwerke');?>
</span></a></li>
        <li><a href="/user/locations" class="yd-pn2 <?php if ($_smarty_tpl->getVariable('active')->value==2){?>active<?php }?>"><span><?php echo __('Lieferadressen');?>
</span></a></li>
        <li><a href="/user/favourites" class="yd-pn3 <?php if ($_smarty_tpl->getVariable('active')->value==3){?>active<?php }?>"><span><?php echo __('Favoriten');?>
</span></a></li>
        <li><a href="/user/orders/perPageuser_orders/25" class="yd-pn4 <?php if ($_smarty_tpl->getVariable('active')->value==4){?>active<?php }?>"><span><?php echo __('Vorherige Bestellungen');?>
</span></a></li>
        <li><a href="/user/ratings" class="yd-pn5 <?php if ($_smarty_tpl->getVariable('active')->value==5){?>active<?php }?>"><span><?php echo __('Restaurant bewerten');?>
</span></a></li>
        <li><a href="/user/fidelity" class="yd-pn6 <?php if ($_smarty_tpl->getVariable('active')->value==6){?>active<?php }?>"><span><?php echo __('Treuepunkte');?>
</span></a></li>
    </ul>

    <!-- <a class="yd-profile-button"><?php echo __('Jetzt Freunde einladen');?>
</a> -->

</div>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/user/_header.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>

    <div class="yd-profile-body index">

        <h1 class="<?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?> br-hidden <?php }?>"><?php echo __('Profil');?>
</h1>

        <?php $_template = new Smarty_Internal_Template('user/_include/profileimage.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->properties['nocache_hash']  = '5415750735024c5e85fa052-70576680';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:22:37
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_include/profileimage.htm" */ ?>
<div class="yd-profile-picture">
    <div class="window"></div>
    
    <p>
        <?php echo __('Bitte lade hier Dein Profilbild hoch.<br />
        Das Profilbild wird neben Deinen Bewertungen zu sehen sein.<br /><br />
        Für das Profilbild erhältst Du 8 Treuepunkte.');?>

    </p>
    
    <form action="/user/profile?redirect=<?php echo (($tmp = @$_smarty_tpl->getVariable('redirect')->value)===null||$tmp==='' ? '' : $tmp);?>
" enctype="multipart/form-data" method="post">
        <input class="upload yd-empty-text" type="file" name="img" title="<?php echo __('Bild hochladen');?>
" />
        <input class="uniformbutton" type="submit" value="OK" />
    </form>
    
    <?php if ($_smarty_tpl->getVariable('cust')->value->hasProfileImage()){?>
    <img src="<?php echo $_smarty_tpl->getVariable('cust')->value->getProfileImage();?>
" />
    <a class="button" href="/user/profile/delete/<?php echo md5(time());?>
?redirect=<?php echo (($tmp = @$_smarty_tpl->getVariable('redirect')->value)===null||$tmp==='' ? '' : $tmp);?>
" id="yd-delete-profile-image"><?php echo __('Bild entfernen');?>
</a>
    <script type="text/javascript">
        <!--
        $('#yd-delete-profile-image').live('click',function(){
            if ( confirm("<?php echo __('Bist Du Dir sicher, dass Du dein Profilbild löschen willst?');?>
") ){
                return true;
            }
            return false;
        });
        -->
    </script>
    <?php }?>
    <em><?php echo __('Klicke hier, um Dein<br />Profilbild hochzuladen!');?>
</em>
</div>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/user/_include/profileimage.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>

        <ul class="yd-profile-infos-1">
            <li class="br-fullname"><?php echo $_smarty_tpl->getVariable('cust')->value->getFullname();?>
</li>
            <?php $_smarty_tpl->tpl_vars['pLoc'] = new Smarty_variable($_smarty_tpl->getVariable('cust')->value->getLocations(null,true), null, null);?>
            <?php if ($_smarty_tpl->getVariable('pLoc')->value instanceof Yourdelivery_Model_Location){?>
            <li class="br-location"><?php echo $_smarty_tpl->getVariable('pLoc')->value->getStreet();?>
 <?php echo $_smarty_tpl->getVariable('pLoc')->value->getHausnr();?>
, <?php echo $_smarty_tpl->getVariable('pLoc')->value->getPlz();?>
 <?php echo $_smarty_tpl->getVariable('pLoc')->value->getCity()->getCity();?>
</li>
            <?php }else{ ?>
            <li class="br-location"><a href="/user/locations"><?php echo __('Lege jetzt Deine primäre Adresse fest');?>
</a></li>
            <?php }?>
            <li class="br-email"><?php echo $_smarty_tpl->getVariable('cust')->value->getEmail();?>
</li>
            <li class="br-tel"><?php echo $_smarty_tpl->getVariable('cust')->value->getTel();?>
</li>
        </ul>
		
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div class="br-line-info"><?php }?>
        
        <ul class="yd-profile-infos-2">
        	<?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
            	<li class="br-pedidos"><strong><?php echo $_smarty_tpl->getVariable('ordered')->value;?>
</strong><?php echo __('Bestellungen');?>
</li>
                <li class="br-favoritos"><strong><?php echo $_smarty_tpl->getVariable('rated')->value;?>
</strong><?php echo __('Bewertungen');?>
</li>
                <li class="br-points"><strong><?php echo $_smarty_tpl->getVariable('cust')->value->getFidelity()->getPoints();?>
</strong><?php echo __('Treuepunkte');?>
</li>
            <?php }else{ ?>
            	<li><?php echo __('Bestellungen:');?>
 <strong><?php echo $_smarty_tpl->getVariable('ordered')->value;?>
</strong></li>
                <li><?php echo __('Bewertungen:');?>
 <strong><?php echo $_smarty_tpl->getVariable('rated')->value;?>
</strong></li>
                <li><?php echo __('Treuepunkte:');?>
 <strong><?php echo $_smarty_tpl->getVariable('cust')->value->getFidelity()->getPoints();?>
</strong></li>
            <?php }?>            
        </ul>

        <p class="yd-profile-infos-3">
            <a href="/user/fidelity#available"><?php echo __('Sammle jetzt noch <strong>%d</strong> Treuepunkte für Gratisessen.',$_smarty_tpl->getVariable('cust')->value->getFidelity()->getOpenActionPoints());?>
</a> <!--<?php echo __('Du kannst <a>hier noch %d Treuepunkte</a> mehr bekommen.',50);?>
-->
        </p>
        
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div><?php }?>
		
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div class="br-favorite-list"><div class="br-inner"><?php }?>
        
        <?php $_smarty_tpl->tpl_vars['favs'] = new Smarty_variable($_smarty_tpl->getVariable('cust')->value->getFavouriteRestaurants(), null, null);?>
        <h1><?php echo __('Deine Top %d Favoriten',count($_smarty_tpl->getVariable('favs')->value));?>
</h1>

        <?php  $_smarty_tpl->tpl_vars['fav'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('favs')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['fav']->key => $_smarty_tpl->tpl_vars['fav']->value){
?>
            <?php $_template = new Smarty_Internal_Template('order/_includes/service/services.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('r',$_smarty_tpl->tpl_vars['fav']->value);$_template->assign('isFavourite',1);$_template->properties['nocache_hash']  = '5415750735024c5e85fa052-70576680';
$_tpl_stack[] = $_smarty_tpl; $_smarty_tpl = $_template;?>
<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:22:37
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/service/services.htm" */ ?>
<?php if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
if (!is_callable('smarty_modifier_inttoprice')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.inttoprice.php';
?><div id="yd-service-<?php echo $_smarty_tpl->getVariable('r')->value->getId();?>
-<?php echo $_smarty_tpl->getVariable('r')->value->getType();?>
" class="yd-searchable yd-service-container yd-service-type-<?php echo $_smarty_tpl->getVariable('r')->value->getType();?>
">          

    <form action="/<?php echo $_smarty_tpl->getVariable('r')->value->getDirectLink();?>
" method="get" id="yd-service-submit-<?php echo $_smarty_tpl->getVariable('r')->value->getId();?>
-<?php echo $_smarty_tpl->getVariable('r')->value->getType();?>
" class="yd-service-submit <?php if (!$_smarty_tpl->getVariable('r')->value->isOnline()){?>service-offline<?php }?>">
        <?php if (!empty($_smarty_tpl->getVariable('isFavourite',null,true,false)->value)&&$_smarty_tpl->getVariable('r')->value->getCurrentCityId()){?><input type="hidden" name="cityId" value="<?php echo $_smarty_tpl->getVariable('r')->value->getCurrentCityId();?>
" /><?php }?>
        <ul class="yd-sv3
            <?php if ($_smarty_tpl->getVariable('r')->value->isNew()){?>
                new
            <?php }?>
            yd-service-select">
            <li class="yd-sv3-1" style="background: url(<?php echo $_smarty_tpl->getVariable('r')->value->getImg();?>
);">
              	
                <?php if ($_smarty_tpl->getVariable('withdelete')->value){?>
                    <a class="yd-delete-favourite td-delete" title="<?php echo __('Favorit entfernen');?>
" id="yd-delete-favourite-<?php echo $_smarty_tpl->getVariable('r')->value->getId();?>
"></a>
                <?php }?>
               
                <?php if ($_smarty_tpl->getVariable('r')->value->getRating()->hasRating()){?>
                <span class="yd-rated" id="yd-rate-<?php echo $_smarty_tpl->getVariable('r')->value->getId();?>
">
                    <span class="yd-rated-<?php echo sprintf('%02d',($_smarty_tpl->getVariable('r')->value->getRating()->getAverage()*2));?>
"></span>
                    
                    <span class="yd-rated-total">(<?php echo $_smarty_tpl->getVariable('r')->value->getRating()->count();?>
)</span>
                </span>                
                <?php }else{ ?>
                <span class="yd-rated" id="yd-rate-<?php echo $_smarty_tpl->getVariable('r')->value->getId();?>
">
                    <span class="yd-rated-10"></span>
                    <span class="yd-rated-average"></span>
                    <span class="yd-rated-total"><?php echo __('noch keine Bewertungen');?>
</span>
                </span>
                <?php }?>

                <a class="yd-button-140 yd-service-select <?php if (!$_smarty_tpl->getVariable('r')->value->isOnline){?>offline<?php }?>" href="/<?php echo $_smarty_tpl->getVariable('r')->value->getDirectLink();?>
">
                    <?php if ($_smarty_tpl->getVariable('r')->value->getType()=="rest"){?><?php echo __('zur Speisekarte');?>
<?php }?>
                    <?php if ($_smarty_tpl->getVariable('r')->value->getType()=="great"){?><?php echo __('zur Auswahl');?>
<?php }?>
                    <?php if ($_smarty_tpl->getVariable('r')->value->getType()=="cater"){?><?php echo __('zu den Caterern');?>
<?php }?>
                </a>

                <?php if ($_smarty_tpl->getVariable('r')->value->getMenuIsNewUntil()!==null){?>
                    <span class="yd-sv3-1-new"></span>
                <?php }?>

            </li>
            <li class="yd-sv3-2 yd-service-info" id="yd-service-info-<?php echo $_smarty_tpl->getVariable('r')->value->getId();?>
">
                <?php echo $_smarty_tpl->getVariable('r')->value->getName();?>

                <?php if ($_smarty_tpl->getVariable('r')->value->getSpecialComment()){?>
                    <span class="special-comment">
                        <span class="special-comment-truncate"><?php echo smarty_modifier_truncate($_smarty_tpl->getVariable('r')->value->getSpecialComment(),23," ...");?>
</span>
                        <span class="special-comment-full" style="display:none;"><?php echo $_smarty_tpl->getVariable('r')->value->getSpecialComment();?>
</span>
                    </span>
                <?php }?>
            </li>
            <li class="yd-sv3-3"><?php echo implode($_smarty_tpl->getVariable('r')->value->getTagsWithMaxStringlength(25),', ');?>
</li>
            <li class="yd-sv3-4 <?php if ($_smarty_tpl->getVariable('r')->value->isOnline()){?>hidden<?php }?>"><?php echo __('Online-Bestellung momentan nicht möglich.');?>
</li>
            <?php if ($_smarty_tpl->getVariable('r')->value->isOnline()){?>
                <?php if ($_smarty_tpl->getVariable('r')->value->getType()!='rest'){?>
                    <li class="yd-sv3-4"><?php echo __('Vorlaufzeit: ');?>
<?php echo $_smarty_tpl->getVariable('r')->value->getDeliverTimeFormated();?>
<br /><?php echo $_smarty_tpl->getVariable('this')->value->formatOpenings($_smarty_tpl->getVariable('r')->value->getOpening()->getIntervalOfDay(time()));?>
</li>
                <?php }else{ ?>   
                    <li class="yd-sv3-4 yd-service-open"><?php echo $_smarty_tpl->getVariable('this')->value->formatOpenings($_smarty_tpl->getVariable('r')->value->getOpening()->getIntervalOfDay(time()));?>
</li>
                    <li class="yd-sv3-4 yd-service-open-in hidden"></li>
                <?php }?>
                <li class="yd-sv3-5">
                    <div class="br-item-sv3-5 first"><div><?php echo __('Mindestbestellwert');?>
</div><?php echo __('%s €',smarty_modifier_inttoprice($_smarty_tpl->getVariable('r')->value->getMinCost()));?>
</div>
                    <div class="br-item-sv3-5">
                        <?php if ($_smarty_tpl->getVariable('r')->value->isOnlyPickup()){?>
                            <div><?php echo __('Nur Abholung!');?>
</div>
                        <?php }elseif($_smarty_tpl->getVariable('r')->value->getDeliverCost()>0){?>
                            <div><?php echo __('Lieferkosten');?>
</div><?php echo __('%s €',smarty_modifier_inttoprice($_smarty_tpl->getVariable('r')->value->getDeliverCost()));?>

                        <?php }else{ ?>
                            <div><?php echo __('Lieferkosten');?>
</div><?php echo __('kostenlos');?>

                        <?php }?>
                    </div>
                </li>
                <?php if ($_smarty_tpl->getVariable('r')->value->isOnlycash()||$_smarty_tpl->getVariable('r')->value->isNoContract()){?>
                <li class="yd-sv3-61"><span><?php echo __('Nur Barzahlung möglich');?>
</span></li>
                <?php }elseif(!$_smarty_tpl->getVariable('r')->value->isPaymentbar()){?>
                <li class="yd-sv3-62"><span><?php echo __('Nur online Bezahlung möglich');?>
</span></li>
                <?php }else{ ?>
                <li class="yd-sv3-63"><span><?php echo __('Bar oder online bezahlen');?>
</span></li>
                <?php }?>
            <?php }?>

        </ul>
        <div class="menu-top3 hidden">
            <table id="yd-service-menu-<?php echo $_smarty_tpl->getVariable('r')->value->getId();?>
" class="yd-service-menu-results">
                <tbody><tr class="even hidden"><td colspan="3"></td></tr></tbody>
            </table>
        </div>

    </form>
</div>
<?php $_smarty_tpl->updateParentVariables(0);?>
<?php /*  End of included template "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/service/services.htm" */ ?>
<?php $_smarty_tpl = array_pop($_tpl_stack);?><?php unset($_template);?>
        <?php }} else { ?>
            <div class="br-no-fav" style="padding:15px 30px;">
                <?php echo __('Du hast im Moment kein Restaurant als Favorit markiert.');?>

            </div>
        <?php } ?>
        
        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div></div><?php }?>

        <br /><br />

        <h1><?php echo __('Deine lieferando News');?>
</h1>

        <br /><br />

        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='lieferando.de'||$_smarty_tpl->getVariable('config')->value->domain->base=='taxiresto.fr'){?>
        <div class="yd-profile-news">

            <?php  $_smarty_tpl->tpl_vars['new'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('news')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['new']->key => $_smarty_tpl->tpl_vars['new']->value){
?>
            <div class="yd-clearfix">
                <!-- <img src="http://www.fotocommunity.de/gfx/profile/profile_m.gif" /> --->
                <small></small>
                <h1><?php echo $_smarty_tpl->tpl_vars['new']->value['title'];?>
</h1>
                <p><?php echo $_smarty_tpl->tpl_vars['new']->value['description'];?>
</p>
            </div>
            <?php }} ?>

            <a href="http://blog.<?php echo $_smarty_tpl->getVariable('config')->value->domain->base;?>
" class="button"><?php echo __('Weitere News anzeigen');?>
</a>

        </div>
        <?php }?>

    </div>
    
    <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div><?php }?>

</div>


            </div>

        </div>

        <?php $_template = new Smarty_Internal_Template("base_footer.htm", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        
        <?php $_template = new Smarty_Internal_Template('_cookies/masterpixel/global.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value); echo $_template->getRenderedTemplate();?><?php unset($_template);?>
        <?php $_template = new Smarty_Internal_Template('_cookies/piwik/global.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('uadomain',$_smarty_tpl->getVariable('domain_base')->value); echo $_template->getRenderedTemplate();?><?php unset($_template);?>

    </body>
</html>
