<?php /* Smarty version Smarty-3.0.7, created on 2012-08-09 13:42:26
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/sidebar/payment.htm" */ ?>
<?php /*%%SmartyHeaderCode:4835230495023e8726377f8-92010274%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '37115fad77fda232f32c2dcd3e1855d4f16e1c05' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/order/_includes/sidebar/payment.htm',
      1 => 1342711276,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4835230495023e8726377f8-92010274',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!$_smarty_tpl->getVariable('service')->value||!$_smarty_tpl->getVariable('service')->value->isOnlycash()){?>
<div class="yd-box br-box">
    <img src="/media/css/www.janamesa.com.br/images/sidebar-payment.png" alt="paymentLogo" />
</div>
<?php }?>