-- @author alex

alter table meal_sizes_nn add column nr varchar(25) default NULL after hasSpecials;

update meal_sizes_nn mn join meals m on m.id=mn.mealId set mn.nr=m.nr where LENGTH(m.nr)>0;
