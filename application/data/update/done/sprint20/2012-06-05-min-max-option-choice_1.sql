alter table meal_options_rows  add column minChoices INTEGER DEFAULT 0 after id;

update meal_options_rows set minChoices=choices;

