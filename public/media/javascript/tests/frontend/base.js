function getMeal(){
    var ydMeal = new YdMeal();
    ydMeal.id = 1;
    ydMeal.name = "Tomato";
    ydMeal.count = 2;
    ydMeal.minAmount = 1;
    ydMeal.exMinCost = false;
    ydMeal.special = "This is sparta";

    var ydSize = new YdSize(2, "Big", 1000);
    ydMeal.size = ydSize;
    
    var ydOption = new YdOption(3, "Spicy", 500);
    ydMeal.addOption(ydOption);
    
    var ydExtra = new YdExtra(3, "Bones", 200);
    ydMeal.addExtra(ydExtra);  
    return ydMeal;
}