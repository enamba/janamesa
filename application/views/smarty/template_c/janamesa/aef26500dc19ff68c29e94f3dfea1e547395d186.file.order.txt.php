<?php /* Smarty version Smarty-3.0.7, created on 2012-08-21 18:46:21
         compiled from "/home/ydadmin/htdocs/lieferando/application/templates/janamesa/ecletica/order.txt" */ ?>
<?php /*%%SmartyHeaderCode:129818745033bb5d5c0366-70706799%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'aef26500dc19ff68c29e94f3dfea1e547395d186' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/templates/janamesa/ecletica/order.txt',
      1 => 1345567572,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '129818745033bb5d5c0366-70706799',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_inttoprice')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.inttoprice.php';
?>[DADOS CLIENTE]
<?php $_smarty_tpl->tpl_vars['tel'] = new Smarty_variable(preg_replace('/(^0|\D)/','',$_smarty_tpl->getVariable('order')->value->getLocation()->getTel()), null, null);?>
<?php if (strlen($_smarty_tpl->getVariable('tel')->value)>9){?>
<?php $_smarty_tpl->tpl_vars['tel1'] = new Smarty_variable(substr($_smarty_tpl->getVariable('tel')->value,0,2), null, null);?>
<?php $_smarty_tpl->tpl_vars['tel2'] = new Smarty_variable(substr($_smarty_tpl->getVariable('tel')->value,2), null, null);?>
<?php }else{ ?>
<?php $_smarty_tpl->tpl_vars['tel1'] = new Smarty_variable('11', null, null);?>
<?php $_smarty_tpl->tpl_vars['tel2'] = new Smarty_variable($_smarty_tpl->getVariable('tel')->value, null, null);?>
<?php }?>
0<?php echo $_smarty_tpl->getVariable('tel1')->value;?>
|<?php echo $_smarty_tpl->getVariable('tel2')->value;?>
|<?php echo $_smarty_tpl->getVariable('order')->value->getCustomer()->getFullname();?>
|<?php echo $_smarty_tpl->getVariable('order')->value->getCustomer()->getEmail();?>
|Rua|<?php echo $_smarty_tpl->getVariable('order')->value->getLocation()->getStreet();?>
|<?php echo $_smarty_tpl->getVariable('order')->value->getLocation()->getHausnr();?>
|0|<?php $_smarty_tpl->tpl_vars['city_verbose'] = new Smarty_variable($_smarty_tpl->getVariable('order')->value->getLocation()->getCity()->getVerboseInformation(), null, null);?><?php if ($_smarty_tpl->getVariable('city_verbose')->value){?><?php echo $_smarty_tpl->getVariable('city_verbose')->value[0]['neighbour'];?>
<?php }?>|<?php echo $_smarty_tpl->getVariable('order')->value->getLocation()->getCity()->getCity();?>
|SP|<?php echo $_smarty_tpl->getVariable('order')->value->getLocation()->getPlz();?>
|C
[FIM DADOS CLIENTE]
[DADOS PEDIDO]
<?php $_smarty_tpl->tpl_vars['card'] = new Smarty_variable($_smarty_tpl->getVariable('order')->value->getCard(), null, null);?>
<?php  $_smarty_tpl->tpl_vars['custItems'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['custId'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('card')->value['bucket']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['custItems']->key => $_smarty_tpl->tpl_vars['custItems']->value){
 $_smarty_tpl->tpl_vars['custId']->value = $_smarty_tpl->tpl_vars['custItems']->key;
?>
<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['hash'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['custItems']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
 $_smarty_tpl->tpl_vars['hash']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
<?php echo $_smarty_tpl->getVariable('item')->value['meal']->getNr();?>
|<?php echo $_smarty_tpl->getVariable('item')->value['meal']->getName();?>
|<?php echo $_smarty_tpl->tpl_vars['item']->value['count'];?>
|<?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('item')->value['meal']->getAllCosts(),2,",");?>

<?php  $_smarty_tpl->tpl_vars['option'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('item')->value['meal']->getCurrentOptions(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['option']->key => $_smarty_tpl->tpl_vars['option']->value){
?>
0|<?php echo $_smarty_tpl->getVariable('option')->value->getName();?>
|1|<?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('option')->value->getCost(),2,",");?>

<?php }} ?>
<?php  $_smarty_tpl->tpl_vars['extra'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('item')->value['meal']->getCurrentExtras(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['extra']->key => $_smarty_tpl->tpl_vars['extra']->value){
?>
0|<?php echo $_smarty_tpl->getVariable('extra')->value->getName();?>
|1|<?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('extra')->value->getCost(),2,",");?>

<?php }} ?>
<?php }} ?>
<?php }} ?>
[FIM DADOS PEDIDO]
[INICIO PAGAMENTO]
<?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('order')->value->getAbsTotal(),2,",");?>
|<?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('order')->value->getChange(),2,",");?>
|<?php if ($_smarty_tpl->getVariable('order')->value->getPaymentAddition()=='vr'){?><?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('order')->value->getAbsTotal(),2,",");?>
<?php }else{ ?>0<?php }?>|0|0|<?php if ($_smarty_tpl->getVariable('order')->value->getPaymentAddition()=='creditCardAtHome'){?><?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('order')->value->getAbsTotal(),2,",");?>
<?php }else{ ?>0<?php }?>|0|<?php if ($_smarty_tpl->getVariable('order')->value->getPayment()!='bar'){?><?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('order')->value->getAbsTotal(),2,",");?>
<?php }else{ ?>0<?php }?>|0|<?php if ($_smarty_tpl->getVariable('order')->value->getPayment()=='bar'&&!$_smarty_tpl->getVariable('order')->value->getPaymentAddition()){?><?php echo smarty_modifier_inttoprice(($_smarty_tpl->getVariable('order')->value->getChange()-$_smarty_tpl->getVariable('order')->value->getAbsTotal()),2,",");?>
<?php }else{ ?>0<?php }?>|0|<?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('order')->value->getDeliverCost(),2,",");?>
|1|*|D|<?php if ($_smarty_tpl->getVariable('order')->value->getPayment()!='bar'){?>ja pago online<?php }?>

[FIM PAGAMENTO]
[INICIO DETALHE BANDEIRA]
[FIM DETALHE BANDEIRA]
