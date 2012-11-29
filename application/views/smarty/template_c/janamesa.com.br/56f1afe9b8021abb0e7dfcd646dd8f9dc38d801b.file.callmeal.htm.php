<?php /* Smarty version Smarty-3.0.7, created on 2012-08-10 12:15:11
         compiled from "/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/request/order/callmeal.htm" */ ?>
<?php /*%%SmartyHeaderCode:16184138095025257fcdded3-24667597%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '56f1afe9b8021abb0e7dfcd646dd8f9dc38d801b' => 
    array (
      0 => '/home/ydadmin/htdocs/lieferando/application/views/smarty/template/janamesa.com.br/request/order/callmeal.htm',
      1 => 1344497434,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16184138095025257fcdded3-24667597',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_counter')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/function.counter.php';
if (!is_callable('smarty_modifier_truncate')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.truncate.php';
if (!is_callable('smarty_modifier_escape')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.escape.php';
if (!is_callable('smarty_modifier_inttoprice')) include '/home/ydadmin/htdocs/lieferando/library/Smarty/plugins/modifier.inttoprice.php';
?><?php echo smarty_function_counter(array('start'=>0,'print'=>false),$_smarty_tpl);?>

<form method="post" action="#" onsubmit="return false;">

    <!-- input fields with meal data -->
    <input type="hidden" name="mealId" value="<?php echo $_smarty_tpl->getVariable('meal')->value->getId();?>
" />
    <input type="hidden" name="mealName" value="<?php echo smarty_modifier_escape(smarty_modifier_truncate($_smarty_tpl->getVariable('meal')->value->getName(),40,'...'));?>
" />
    <input type="hidden" name="exMinCost" value="<?php echo $_smarty_tpl->getVariable('meal')->value->getExcludeFromMinCost()||$_smarty_tpl->getVariable('meal')->value->getCategory()->getExcludeFromMinCost();?>
" />
    <input type="hidden" name="minCount" value="<?php echo $_smarty_tpl->getVariable('meal')->value->getMinAmount();?>
" />
    <input type="hidden" class="yd-meal-cost" id="yd-meal-cost-base" value="<?php echo $_smarty_tpl->getVariable('meal')->value->getCost();?>
" />

    <div class="yd-dialogs yd-dialogs-green callmeal"><a class="yd-dialogs-close"></a>

        <div class="yd-dialogs-head">

            <div class="yd-clearfix">

                <input type="text" value="1" name="count" id="yd-meal-count" class="yd-only-nr" maxlength="3" />

                <span class="name">
                    <?php echo $_smarty_tpl->getVariable('meal')->value->getName();?>


                    <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?><div class="br-meal-imgs"><?php }?>
                        <?php if ($_smarty_tpl->getVariable('meal')->value->hasAttribute('vegetarian')){?><img title="<?php echo __('vegetarisch');?>
" src="<?php echo $_smarty_tpl->getVariable('config')->value->domain->static;?>
/images/yd-frontend-step3/menu-vegetarian.png" alt="" /><?php }?>
                        <?php if ($_smarty_tpl->getVariable('meal')->value->hasAttribute('garlic')){?><img title="<?php echo __('mit Knoblauch');?>
" src="<?php echo $_smarty_tpl->getVariable('config')->value->domain->static;?>
/images/yd-frontend-step3/menu-garlic.png" alt="" /><?php }?>
                        <?php if ($_smarty_tpl->getVariable('meal')->value->hasAttribute('bio')){?><img title="<?php echo __('Bioware');?>
" src="<?php echo $_smarty_tpl->getVariable('config')->value->domain->static;?>
/images/yd-frontend-step3/menu-bio.png" alt="" /><?php }?>
                        <?php if ($_smarty_tpl->getVariable('meal')->value->hasAttribute('spicy')){?><img title="<?php echo __('scharf gewürzt');?>
" src="<?php echo $_smarty_tpl->getVariable('config')->value->domain->static;?>
/images/yd-frontend-step3/menu-spicy.png" alt="" /><?php }?>
                        <?php if ($_smarty_tpl->getVariable('meal')->value->hasAttribute('fish')){?><img title="<?php echo __('mit Fisch');?>
" src="<?php echo $_smarty_tpl->getVariable('config')->value->domain->static;?>
/images/yd-frontend-step3/menu-fish.png" alt="" /><?php }?>
                        <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?></div><?php }?>

                </span>

                <span class="yd-dialogs-head-price">
                    <span class="yd-current-meal-price">
                        <?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('meal')->value->getCost());?>

                    </span>
                    &nbsp;
                    <sup><?php echo __('€');?>
</sup>
                </span>

                <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
                <div class="description"><?php echo $_smarty_tpl->getVariable('meal')->value->getDescription();?>
</div>
                <?php }?>

            </div>

            <small><?php echo $_smarty_tpl->getVariable('meal')->value->getDescription();?>
</small>

        </div>

        <div class="yd-dialogs-body">

            <?php $_smarty_tpl->tpl_vars['mealSizes'] = new Smarty_variable($_smarty_tpl->getVariable('meal')->value->getSizes(), null, null);?>
            <?php if (count($_smarty_tpl->getVariable('mealSizes')->value)>1){?>
            <div class="yd-dialogs-box yd-dialogs-green">


                <div class="yd-dialogs-box-head">
                    <span class="yd-dbh-number"><?php echo smarty_function_counter(array(),$_smarty_tpl);?>
.</span>
                    <span class="yd-dbh-choose"><?php echo __('Wähle jetzt Deine Größe');?>
</span>
                    <span class="yd-dbh-icon"></span>
                    <span class="yd-dbh-choise"></span>
                </div>

                <div class="yd-dialogs-box-body">
                    <ul class="yd-clearfix">
                        <?php  $_smarty_tpl->tpl_vars['size'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('meal')->value->getSizes(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['size']->key => $_smarty_tpl->tpl_vars['size']->value){
?>
                        <input type="hidden" name="sizes_<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
_name" value="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['size']->value['name']);?>
" />
                        <input type="hidden" name="sizes_<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
_cost" value="<?php echo $_smarty_tpl->tpl_vars['size']->value['cost'];?>
" />
                        <li>
                            <input type="radio" name="sizeId" value="<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
" class="yd-change-size" id="yd-change-size-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
" <?php if ($_smarty_tpl->getVariable('meal')->value->getCurrentSize()==$_smarty_tpl->tpl_vars['size']->value['id']){?>checked="checked"<?php }?> />
                                   <label for="yd-change-size-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
">
                                &nbsp;&nbsp;<?php echo __('%s für %s €',$_smarty_tpl->tpl_vars['size']->value['name'],smarty_modifier_inttoprice($_smarty_tpl->tpl_vars['size']->value['cost']));?>

                            </label>
                        </li>
                        <?php }} ?>
                    </ul>
                </div>

            </div>
            <?php }?>

            <input type="hidden" name="sizeId" value="<?php echo $_smarty_tpl->getVariable('meal')->value->getCurrentSize();?>
" />
            <input type="hidden" id="yd-meal-cost-hidden" name="sizeCost" value="<?php echo $_smarty_tpl->getVariable('meal')->value->getCost();?>
" />

            <?php if ($_smarty_tpl->getVariable('meal')->value->hasOptions()){?>
            <?php $_smarty_tpl->tpl_vars['mealOptionsFast'] = new Smarty_variable($_smarty_tpl->getVariable('meal')->value->getOptionsFast(), null, null);?>
            <?php  $_smarty_tpl->tpl_vars['optrow'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('mealOptionsFast')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['optrow']->key => $_smarty_tpl->tpl_vars['optrow']->value){
?>

            <div class="yd-dialogs-box optrow" id="choices-<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
" data-min-choices="<?php echo $_smarty_tpl->tpl_vars['optrow']->value['minChoices'];?>
" data-max-choices="<?php echo $_smarty_tpl->tpl_vars['optrow']->value['choices'];?>
">

                <div class="yd-dialogs-box-head yd-change-options" id="yd-change-options-<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
">
                    <span class="yd-dbh-number"><?php echo smarty_function_counter(array(),$_smarty_tpl);?>
.</span>
                    <span class="yd-dbh-choose"><?php echo __('Wähle jetzt Dein/e %s',$_smarty_tpl->tpl_vars['optrow']->value['name']);?>
</span>
                    <span class="yd-dbh-change yd-change-options hidden" id="yd-change-options-<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
"><?php echo __('ändern');?>
</span>
                    <span class="yd-dbh-price">
                        <span class="yd-option-choices-selected">0</span>
                        <span class="yd-option-choices-needed"><?php if ($_smarty_tpl->tpl_vars['optrow']->value['minChoices']<=$_smarty_tpl->tpl_vars['optrow']->value['choices']){?> <?php echo __('gewählt von maximal');?>
 <?php echo $_smarty_tpl->tpl_vars['optrow']->value['choices'];?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['optrow']->value['choices'];?>
<?php }?></span>
                    </span>
                    <span class="yd-dbh-icon"></span>
                    <span class="yd-dbh-choise"></span>
                </div>

                <div class="yd-dialogs-box-body" id="yd-change-options-box-<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
">
                    <ul class="yd-clearfix">
                        <?php  $_smarty_tpl->tpl_vars['optitem'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['optrow']->value['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['optitem']->key => $_smarty_tpl->tpl_vars['optitem']->value){
?>
                        <input type="hidden" name="options_<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
_<?php echo $_smarty_tpl->tpl_vars['optitem']->value['oid'];?>
_name" value="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['optitem']->value['name']);?>
" />
                        <input type="hidden" name="options_<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
_<?php echo $_smarty_tpl->tpl_vars['optitem']->value['oid'];?>
_cost" value="<?php echo $_smarty_tpl->tpl_vars['optitem']->value['cost'];?>
" />
                        <li>
                            <input type="checkbox" name="options[]" value="<?php echo $_smarty_tpl->tpl_vars['optitem']->value['oid'];?>
" id="row-<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
-<?php echo $_smarty_tpl->tpl_vars['optitem']->value['oid'];?>
" class="yd-option-row-checkbox yd-check-option yd-option-item-<?php echo $_smarty_tpl->tpl_vars['optitem']->value['oid'];?>
" <?php if ($_smarty_tpl->getVariable('meal')->value->hasCurrentOptionAppend($_smarty_tpl->tpl_vars['optitem']->value['oid'])){?>checked="checked"<?php }?>/>
                                   <label for="row-<?php echo $_smarty_tpl->tpl_vars['optrow']->value['id'];?>
-<?php echo $_smarty_tpl->tpl_vars['optitem']->value['oid'];?>
-<?php echo $_smarty_tpl->tpl_vars['optrow']->value['choices'];?>
">
                                <?php echo $_smarty_tpl->tpl_vars['optitem']->value['name'];?>
 <?php if ($_smarty_tpl->tpl_vars['optitem']->value['cost']>0){?><span class="price">(<?php echo __('%s €',smarty_modifier_inttoprice($_smarty_tpl->tpl_vars['optitem']->value['cost']));?>
)</span><?php }?>
                            </label>
                        </li>
                        <?php }} ?>
                    </ul>
                </div>

            </div>
            <?php }} ?>
            <?php }?>

            <?php if ($_smarty_tpl->getVariable('meal')->value->hasAnyExtras()){?>
            <h2><?php echo __('Lust auf Extras?');?>
</h2>

            <?php  $_smarty_tpl->tpl_vars['size'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('mealSizes')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['size']->key => $_smarty_tpl->tpl_vars['size']->value){
?>
            <?php if ($_smarty_tpl->getVariable('meal')->value->hasExtras($_smarty_tpl->tpl_vars['size']->value['id'])){?>
            <div class="yd-dialogs-box yd-current-extras" id="yd-current-extras-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
" <?php if ($_smarty_tpl->tpl_vars['size']->value['id']!=$_smarty_tpl->getVariable('meal')->value->getCurrentSize()){?>style="display:none;"<?php }?>>

                 <div class="yd-dialogs-box-head">
                    <span class="yd-dbh-choose"><?php echo __('Wähle jetzt Deine Extras');?>
</span>
                    <span class="yd-dbh-extras"></span>
                </div>
                <div class="yd-dialogs-box-body">
                    <div class="yd-extra-group">
                        <?php $_smarty_tpl->tpl_vars['extras'] = new Smarty_variable($_smarty_tpl->getVariable('meal')->value->getExtrasFast($_smarty_tpl->tpl_vars['size']->value['id']), null, null);?>
                        <?php  $_smarty_tpl->tpl_vars['extraGroups'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('extras')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['extraGroups']->key => $_smarty_tpl->tpl_vars['extraGroups']->value){
?>
                        <span><?php echo $_smarty_tpl->tpl_vars['extraGroups']->value['groupName'];?>
</span>
                        <ul class="yd-clearfix">
                            <?php  $_smarty_tpl->tpl_vars['extra'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['extraGroups']->value['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['extra']->key => $_smarty_tpl->tpl_vars['extra']->value){
?>
                            <li>
                                <input type="hidden" name="extrasId" value="<?php echo $_smarty_tpl->tpl_vars['extra']->value['id'];?>
" class="yd-extra-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
" />
                                <input type="hidden" name="extras_<?php echo $_smarty_tpl->tpl_vars['extra']->value['id'];?>
-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
_name" value="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['extra']->value['name']);?>
" />
                                <input type="hidden" name="extras_<?php echo $_smarty_tpl->tpl_vars['extra']->value['id'];?>
-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
_cost" value="<?php echo $_smarty_tpl->tpl_vars['extra']->value['cost'];?>
" />
                                <input type="hidden" name="extras_<?php echo $_smarty_tpl->tpl_vars['extra']->value['id'];?>
-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
_count" value="<?php if ($_smarty_tpl->getVariable('meal')->value->hasCurrentExtraAppend($_smarty_tpl->tpl_vars['extra']->value['id'])){?><?php echo $_smarty_tpl->tpl_vars['extra']->value['count'];?>
<?php }else{ ?>0<?php }?>" />
                                <button href="#" id="meal-extras-<?php echo $_smarty_tpl->tpl_vars['extra']->value['id'];?>
-<?php echo $_smarty_tpl->tpl_vars['size']->value['id'];?>
" class="yd-extras"><?php echo $_smarty_tpl->tpl_vars['extra']->value['name'];?>
 <?php if ($_smarty_tpl->tpl_vars['extra']->value['cost']>0){?>(<?php echo __('%s&nbsp;€',smarty_modifier_inttoprice($_smarty_tpl->tpl_vars['extra']->value['cost']));?>
)<?php }?></button>
                            </li>
                            <?php }} ?>
                        </ul>
                        <?php }} ?>
                    </div>
                </div>
            </div>
            <?php }?>
            <?php }} ?>
            <?php }?>

            <?php if ($_smarty_tpl->getVariable('config')->value->domain->base=='janamesa.com.br'){?>
            <div class="strip-text">
                <span>Desejos Adicionais</span>
            </div>
            <?php }?>

            <div class="yd-dialogs-comment">

                <div class="yd-dialogs-comment-head">
                    <span><?php echo __('Bemerkung zur Bestellung schreiben');?>
</span>
                </div>

                <div class="yd-dialogs-comment-body hidden">
                    <textarea name="special"></textarea>
                </div>

            </div>

            <div class="yd-dialogs-price">

                <strong>
                    <?php echo __('Dein aktueller Preis:');?>

                    <span>
                        <span class="yd-current-meal-price">
                            <?php echo smarty_modifier_inttoprice($_smarty_tpl->getVariable('meal')->value->getCost());?>

                        </span>
                        <sup><?php echo __('€');?>
</sup>
                    </span>
                </strong>

                <?php if ($_smarty_tpl->getVariable('update')->value){?>
                <div class="br-submit"><input type="button" value="<?php echo __('Aktualisieren');?>
" class="yd-button-190 yd-update-to-card" id=""/></div>
                <div class="br-submit"><input type="hidden" type="button" value="<?php echo __('In den Warenkorb');?>
" class="yd-button-190 yd-add-to-card" /></div>
                <?php }else{ ?>
                <div class="br-submit"><input type="button" value="<?php echo __('In den Warenkorb');?>
" class="yd-button-190 yd-add-to-card" /></div>
                <?php }?>
            </div>

        </div> <!-- /yd-dialogs-body -->

        <div class="yd-dialogs-footer">

            <div id="yd-error-mincount" class="hidden">
                <?php ob_start();?><?php echo $_smarty_tpl->getVariable('meal')->value->getMinCount();?>
<?php $_tmp1=ob_get_clean();?><?php echo __('Achtung, Mindestmenge! Von dieser Speise musst Du mindestens %s Portionen zum Warenkorb hinzufügen.',$_tmp1);?>

            </div>

        </div>

    </div> <!-- /yd-dialogs -->
</form>
