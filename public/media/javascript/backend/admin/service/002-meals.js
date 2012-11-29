$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 26.06.2012
     */
    $('.yd-service-meals-types-select').live('change',function(){
        var parentId = $(this).intVal();
        var level = parseInt(this.id.split('-')[5]);
        
        if (!parentId || level > 3) {
            return;            
        }
        
        $.ajax({
            url: "/administration_request_service_meals_types/get",
            data: {
                parentId: parentId
            },
            dataType: "json",
            success: function(data){
                if (data.error) {
                    return notification('error', data.error);
                }
                
                $('.yd-service-meals-types-select').each(function(){
                    var l = this.id.split('-')[5];
                    if (l > level) {
                        $(this).html("");
                    }
                });
                
                var $target = $('#yd-service-meals-types-select-' + (level + 1)).html('<option value="0">...</otion>');
                if (data.children) {
                    $.each(data.children, function(k, v) {
                        $target.append('<option value="' + v.id + '">' + v.name + '</otion>');
                    });
                }
            }
        });
    });
    
    /**
     * @author vpriem
     * @since 26.06.2012
     */
    $('.yd-service-meals-types-add').live("click", function(){
        var level = this.id.split('-')[5];
        
        var parentId = 0;
        if (level > 1) {
            parentId = $('#yd-service-meals-types-select-' + (level - 1)).intVal();
            if (!parentId) {
                return false;
            }
         }

        var $input = $('#yd-service-meals-types-name-' + level);
        
        $.ajax({
            url: "/administration_request_service_meals_types/add",
            data: {
                parentId: parentId,
                name: $input.val()
            },
            type: "POST",
            dataType: "json",
            success: function(data){
                if (data.error) {
                    return notification('error', data.error);
                }
                
                if (data.id) {
                    $('#yd-service-meals-types-select-' + level).append('<option value="' + data.id + '">' + $input.val() + '</option>')
                                                                .val(data.id);
                    $input.val("");
                    $('#yd-service-meals-types-autocomplete').trigger("refresh");
                }
            }
        });
        
        return false;
    });
    
    /**
     * @author vpriem
     * @since 27.06.2012
     */
    $("#yd-service-meals-types-form").ajaxForm({
        dataType: "json",
        success: function (data, status, xhr, $form) {
            if (data.meals) {
                $.each(data.meals, function(id, v) {
                    $("#yd-meals-grid-types-" + id)
                        .html(v)
                        .addClass("yd-bold");
                });
                
                $(".yd-service-meals-types-select").val(0);
                $(".yd-service-meals-types-autocomplete").val("");
                
                $("html").scrollTop($("#grid").offset().top);
            }
        }
    });
    
    /**
     * update grid ingredients column
     * @author Alex Vait 
     * @since 27.06.2012
     */
    $("#yd-service-meals-ingredients-form").ajaxForm({
        dataType: "json",
        success: function (data) {
            if (data.meals) {
                $.each(data.meals, function(id, v) {
                    if (v.ingredients != null) {
                        $("#yd-meal-grid-ingredients-" + id)
                            .html(v.ingredients)
                            .addClass("yd-bold");
                    }
                    else if (v.attributes != null) {
                        $("#yd-meal-grid-attributes-" + id)
                            .html(v.attributes)
                            .addClass("yd-bold");
                    }
                });
                
                $('#yd-ingredients-list-delete-all').click();
                $('.yd-ingredient, .yd-attribute').prop("checked", false);
                
                $("html").scrollTop($("#grid").offset().top);
            }
        }
    });    
    
    /**
     * add new ingredient to the specified group
     * @author Alex Vait 
     * @since 26.06.2012
     */
    $(".yd-add-ingredient").live('click',function() {
        
        var groupId = this.id.split('-')[4];
        var $input = $('#yd-add-ingredient-togroup-input-' + groupId);

        $.ajax({
            url: "/administration_request_service_meals_ingredients/add",
            data: {
                groupId : groupId,
                name : $input.val()
            },
            type: "POST",
            dataType: "json",
            success: function(data){
                if (data.error) {
                    return notification('error', data.error);
                }
                
                if (data.id) {
                    $('#yd-ingredients-groupbox-' + groupId).append('<div><input class="yd-ingredient yd-checkbox-2" id="yd-ingredient-' + data.id + '" type="checkbox" name="ingredientsId[]" value="' + data.id + '"/> ' + $input.val() + '</div>');
                    $input.val("");
                    $('#yd-service-meals-ingredients-autocomplete').trigger("refresh");
                }
            }
        });

        this.blur();
        return false;
    });
    
    /**
     * add ingredient on when <enter> was klicked in the input field
     * @author Alex Vait
     * @since 27.06.2012
     */
    $('.yd-add-ingredient-togroup-input').live('keyup',function(e){
        var ingredientsGroupId = this.id.split('-')[5];

        if (e.keyCode != 13) {
            return;
        }

        $('#yd-add-ingredient-togroup-' + ingredientsGroupId).click();
    });    
    
    /**
     * @author vpriem
     * @since 26.06.2012
     */
    $('#yd-service-meals-types-autocomplete').bind("refresh", function(){
        $(this).autocomplete("destroy")
               .bautocomplete('/administration_request_autocomplete_meals/types');
    }).trigger("refresh");
    
    /**
     * @author vpriem
     * @since 27.06.2012
     */
    $(".yd-service-meals-search-add").live("click", function() {
        $(this).closest("div.yd-service-meals-search")
               .clone()
               .insertAfter($(".yd-service-meals-search:last"))
               .find(":text").val("");
               
        this.blur();
        return false;
    });

    /**
     * @author vpriem
     * @since 10.09.2012
     */
    $(".yd-service-meals-search-delete").live("click", function() {
        if ($("div.yd-service-meals-search").length < 2) {
            this.blur();
            return false;
        }
        
        $(this).closest("div.yd-service-meals-search")
               .remove();
               
        this.blur();
        return false;
    });
    
    /**
     * @author vpriem
     * @since 27.06.2012
     */
    $("#yd-service-meals-check-state").click(function(){
       $("#yd-service-meals-state").html('<img src="/media/images/ajax-loader-16-white.gif" />')
                                   .load(this.href);
       
       this.blur();
       return false;
    });
    

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 20.06.2012
     */
    $("#yd-service-meals-ingredients-autocomplete").bind("refresh", function(){
        $(this).autocomplete("destroy")
               .bautocomplete('/administration_request_autocomplete_meals/ingredients', function(item){
            if (item.id > 0) {        
                //element
                var ingredient = document.createElement('strong');
                ingredient.appendChild(document.createTextNode(item.label));
                $(ingredient).prop('id', 'yd-quickingredient-'+ item.id);

                //a tag for closing
                var ingredientDelete = document.createElement('a');
                ingredientDelete.appendChild(document.createTextNode('x'));
                $(ingredientDelete).addClass('yd-quickingredient-delete');
                ingredient.appendChild(ingredientDelete);

                $('#yd-ingredients-list').append(ingredient);

                var ingredientWrapper =  $('#yd-ingredients-list');

                //input tag
                var ingredientInput = document.createElement('input');
                $(ingredientInput).prop('type', 'hidden');
                $(ingredientInput).val(item.id)
                $(ingredientInput).prop('name', 'quickaddingredientIds[]');
                ingredientWrapper.append(ingredientInput);
                ingredientWrapper.parent().show();
                $("#yd-service-meals-ingredients-autocomplete").val('');
                $('#yd-ingredients-list-delete-all').show();

                $("#yd-service-copy-to").val('');
            }
        });
    }).trigger("refresh");
    
    $('#yd-ingredients-list').on('click', '.yd-quickingredient-delete', function(event) {
        var span = $(this).parent('strong');    
        $(span[0]).next().remove();
        $(span[0]).remove();
        if (!$('#yd-ingredients-list').children().length) {
            $('#yd-ingredients-list-delete-all').hide();
            $('#yd-ingredients-list').parent().hide();
        }               
    });
    
    $('#yd-ingredients-list-delete-all').click(function(){
        $('#yd-ingredients-list').empty();
        $('#yd-ingredients-list-delete-all').hide();
        $('#yd-ingredients-list').parent().hide();
    });
    
});
