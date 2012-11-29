<?php /* Smarty version Smarty-3.1.11, created on 2012-11-06 18:19:02
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/form_order.htm" */ ?>
<?php /*%%SmartyHeaderCode:73975034050994686eb2890-31195836%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '95d06f10ef6938c1459865387824278d9b27fa1d' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/default/order/_includes/form_order.htm',
      1 => 1346837385,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '73975034050994686eb2890-31195836',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'order' => 0,
    'newCustomerDiscountError' => 0,
    'card' => 0,
    'custItems' => 0,
    'item' => 0,
    'meal' => 0,
    'hash' => 0,
    'option' => 0,
    'postfix' => 0,
    'extra' => 0,
    'count' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50994687086631_69614898',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50994687086631_69614898')) {function content_50994687086631_69614898($_smarty_tpl) {?><?php if ((is_object($_smarty_tpl->tpl_vars['order']->value)&&!$_smarty_tpl->tpl_vars['order']->value->getId())||$_smarty_tpl->tpl_vars['newCustomerDiscountError']->value){?>
    <?php $_smarty_tpl->tpl_vars['card'] = new Smarty_variable($_smarty_tpl->tpl_vars['order']->value->getCard(), null, 0);?>
    <?php  $_smarty_tpl->tpl_vars['custItems'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['custItems']->_loop = false;
 $_smarty_tpl->tpl_vars['custId'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['card']->value['bucket']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['custItems']->key => $_smarty_tpl->tpl_vars['custItems']->value){
$_smarty_tpl->tpl_vars['custItems']->_loop = true;
 $_smarty_tpl->tpl_vars['custId']->value = $_smarty_tpl->tpl_vars['custItems']->key;
?>
        <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['hash'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['custItems']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['hash']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
            <?php $_smarty_tpl->tpl_vars['meal'] = new Smarty_variable($_smarty_tpl->tpl_vars['item']->value['meal'], null, 0);?>
            <?php $_smarty_tpl->tpl_vars['count'] = new Smarty_variable($_smarty_tpl->tpl_vars['item']->value['count'], null, 0);?>
            <?php $_smarty_tpl->tpl_vars['size'] = new Smarty_variable($_smarty_tpl->tpl_vars['item']->value['size'], null, 0);?>

            <?php if ($_smarty_tpl->tpl_vars['meal']->value->getCurrentOptionsCount()>0){?>
                <?php  $_smarty_tpl->tpl_vars['option'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['option']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['meal']->value->getCurrentOptions(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['option']->key => $_smarty_tpl->tpl_vars['option']->value){
$_smarty_tpl->tpl_vars['option']->_loop = true;
?>
                    <input type="hidden" class="yd-order-option" id="yd-order-option-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option']->value->getId(), ENT_QUOTES, 'UTF-8');?>
-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['postfix']->value, ENT_QUOTES, 'UTF-8');?>
" name="meal[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
][options][]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option']->value->getId(), ENT_QUOTES, 'UTF-8');?>
" />
                    <input type="hidden" class="yd-order-option" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option']->value->getName(), ENT_QUOTES, 'UTF-8');?>
" />
                    <input type="hidden" class="yd-order-option" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option']->value->getCost(), ENT_QUOTES, 'UTF-8');?>
" />
                <?php } ?>
            <?php }?>

            <?php if ($_smarty_tpl->tpl_vars['meal']->value->getCurrentExtrasCount()>0){?>
                <?php  $_smarty_tpl->tpl_vars['extra'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['extra']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['meal']->value->getCurrentExtras(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['extra']->key => $_smarty_tpl->tpl_vars['extra']->value){
$_smarty_tpl->tpl_vars['extra']->_loop = true;
?>
                    <input type="hidden" class="yd-order-extra" id="yd-order-extra-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getId(), ENT_QUOTES, 'UTF-8');?>
-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['postfix']->value, ENT_QUOTES, 'UTF-8');?>
" name="meal[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
][extras][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getId(), ENT_QUOTES, 'UTF-8');?>
][id]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getId(), ENT_QUOTES, 'UTF-8');?>
" />
                    <input type="hidden" class="yd-order-extra" name="meal[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
][extras][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getId(), ENT_QUOTES, 'UTF-8');?>
][count]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getCount(), ENT_QUOTES, 'UTF-8');?>
" />
                    <input type="hidden" class="yd-order-extra" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getName(), ENT_QUOTES, 'UTF-8');?>
" />
                    <input type="hidden" class="yd-order-extra" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getCost(false), ENT_QUOTES, 'UTF-8');?>
" />
                    <input type="hidden" class="yd-order-extra" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['extra']->value->getCount(), ENT_QUOTES, 'UTF-8');?>
" />
                <?php } ?>
            <?php }?>

            <!-- build up post data -->
            <input type="hidden" class="yd-order-meal" name="meal[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
][id]" id="yd-order-meal-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['postfix']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getId(), ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getName(), ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getCost(), ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" name="meal[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
][count]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['count']->value, ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getMinAmount(), ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getExcludeFromMinCost(), ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" name="meal[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
][special]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getSpecial(), ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" name="meal[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hash']->value, ENT_QUOTES, 'UTF-8');?>
][size]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getCurrentSize(), ENT_QUOTES, 'UTF-8');?>
" />
            <input type="hidden" class="yd-order-meal" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['meal']->value->getCurrentSizeName(), ENT_QUOTES, 'UTF-8');?>
" />

        <?php } ?>
    <?php } ?>

    <!-- this will trigger the restoring of the ydOrder object withOUT an update of the view -->
    <input type="hidden" name="serviceId" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['order']->value->getService()->getId(), ENT_QUOTES, 'UTF-8');?>
" />
    <input class="yd-no-update" type="hidden" name="restore" />
    
    <?php if ($_smarty_tpl->tpl_vars['order']->value->getDiscount()){?>
        <input type="hidden" name="discount" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['order']->value->getDiscount()->getCode(), ENT_QUOTES, 'UTF-8');?>
" />
    <?php }?>
    
    <input type="hidden" name="deliverCost" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['order']->value->getService()->getDeliverCost(), ENT_QUOTES, 'UTF-8');?>
" />
    <input type="hidden" name="cityId" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['order']->value->getLocation()->getCityId(), ENT_QUOTES, 'UTF-8');?>
" />
    <input type="hidden" class="floor" name="floor" value="" />
    <input type="hidden" name="floorfee" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['order']->value->getService()->getFloorfee(), ENT_QUOTES, 'UTF-8');?>
" />
    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['order']->value->getMode(), ENT_QUOTES, 'UTF-8');?>
" />
<?php }?>
<?php }} ?>