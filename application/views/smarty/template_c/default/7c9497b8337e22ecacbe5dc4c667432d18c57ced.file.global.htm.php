<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:03
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/masterpixel/global.htm" */ ?>
<?php /*%%SmartyHeaderCode:1525654494509946870d1d72-48681004%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7c9497b8337e22ecacbe5dc4c667432d18c57ced' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/_cookies/masterpixel/global.htm',
      1 => 1338548803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1525654494509946870d1d72-48681004',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'APPLICATION_ENV' => 0,
    'uadomain' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5099468710fcf0_19590602',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5099468710fcf0_19590602')) {function content_5099468710fcf0_19590602($_smarty_tpl) {?><?php if ($_smarty_tpl->tpl_vars['APPLICATION_ENV']->value=="production"){?>

    <?php if ($_smarty_tpl->tpl_vars['uadomain']->value=='lieferando.de'){?>
        <IFRAME src="http://img-cdn.mediaplex.com/0/18421/universal.html?page_name=footer&amp;footer=1&amp;mpuid=<?php echo htmlspecialchars(time(), ENT_QUOTES, 'UTF-8');?>
" height="1" width="1" frameborder="0"></IFRAME>

    <?php }elseif($_smarty_tpl->tpl_vars['uadomain']->value=='lieferando.at'){?>
        <iframe src="http://img-cdn.mediaplex.com/0/18589/universal.html?page_name=at_footer&amp;footer=1&amp;mpuid=<?php echo htmlspecialchars(time(), ENT_QUOTES, 'UTF-8');?>
" height="1" width="1" frameborder="0"></iframe>

    <?php }elseif($_smarty_tpl->tpl_vars['uadomain']->value=='lieferando.ch'){?>
        <iframe src="http://img-cdn.mediaplex.com/0/18590/universal.html?page_name=ch_footer&amp;footer=1&amp;mpuid=<?php echo htmlspecialchars(time(), ENT_QUOTES, 'UTF-8');?>
" height="1" width="1" frameborder="0"></iframe>

    <?php }elseif($_smarty_tpl->tpl_vars['uadomain']->value=='taxiresto.fr'){?>  
    <iframe src="http://img-cdn.mediaplex.com/0/18591/universal.html?page_name=fr_footer&amp;footer=1&amp;mpuid=<?php echo htmlspecialchars(time(), ENT_QUOTES, 'UTF-8');?>
" height="1" width="1" frameborder="0"></iframe>

    <?php }elseif($_smarty_tpl->tpl_vars['uadomain']->value=='elpedido.es'){?>

    <?php }elseif($_smarty_tpl->tpl_vars['uadomain']->value=='appetitos.it'){?>
    
    <?php }elseif($_smarty_tpl->tpl_vars['uadomain']->value=='smakuje.pl'){?>

    <?php }elseif($_smarty_tpl->tpl_vars['uadomain']->value=='pyszne.pl'){?>
    <iframe frameborder="0" src="http://img-cdn.mediaplex.com/0/20585/universal.html?page_name=footer&amp;footer=1&amp;mpuid=<?php echo htmlspecialchars(time(), ENT_QUOTES, 'UTF-8');?>
"></iframe>
    <?php }?>
    
<?php }else{ ?>

    <iframe src="/null.php?page_name=footer&amp;footer=1&amp;mpuid=<?php echo htmlspecialchars(time(), ENT_QUOTES, 'UTF-8');?>
" height="1" width="1" frameborder="0"></iframe>

<?php }?><?php }} ?>