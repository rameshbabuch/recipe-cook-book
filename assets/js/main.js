/**
 * Created by rameshpaul on 18/11/16.
 */

$(document).ready(function(){

    $(".add-ingredient").click(function(e){
        var tbody = $(".ingredient-table tbody");
        var length = tbody.children.length;
        var html = '<tr>'+
                    '<td> <input type="text" class="form-control ingredient_name" name="ingredient_name[]" maxlength="255" > </td>'+
                    '<td> <input type="text" class="form-control ingredient_quantity" name="ingredient_quantity[]" maxlength="20" > </td>'+
                    '<td> <a href="javascript:;" class="link delete">Delete</a> </td>'+
                   '</tr>';
        tbody.append(html);
    });

    $(".ingredient-table .delete").on('click', function(e){
       $(this).parent().parent().remove();
    });

    $("#recipe-form").submit(function(e){
        e.preventDefault();
        if(validateForm($("#recipe-form"))) {
            var params = $("#recipe-form").serializeObject();
            console.log(params);

            $.ajax({
                url: '/submit-recipe',
                type: 'post',
                dataType: 'json',
                data: params,
                success: function (response) {
                    console.log("resp", response);
                    window.location = '/';
                },
                error: function () {
                    alert("Could not create recipe !");
                }
            });
        }
        return false;
    });

    var $selectedTr = '';
    var recipeId = 0;
    $(".delete-recipe").on('click', function(e){
        recipeId = $(this).attr("data-recipe-id");
        $selectedTr = $(this).parent().parent();
        $(".bs-example-modal-sm").modal('show');
        //$(".confirm-delete-recipe").attr("data-recipe-id", recipeId);
    });

    $(".confirm-delete-recipe").on('click', function(e){
        //var recipeId = $(this).attr("data-recipe-id");
        console.log("recipe id", recipeId);
        if($selectedTr != '' && parseInt(recipeId) > 0) {
            $.ajax({
                url: '/recipe/' + recipeId,
                type: 'delete',
                dataType: 'json',
                success: function (response) {
                    console.log("resp", response);
                    //$selectedTr.remove()
                    window.location = '/';
                },
                error: function () {
                    alert("Could not delete recipe !");
                }
            });
            $(".bs-example-modal-sm").modal('hide');
        }
    });
    //$(".add-ingredient").trigger('click');

    function validateForm($form){
        var returnValue = true;
        var timeRegex = /^((?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$)/;
        var emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var title = $("#title").val();
        var servingCount = $("#serving_count").val();
        var cookTime = $("#cook_time").val();
        var temperature = $("#cook_temperature").val();
        var instructions = $("#instructions").val();
        var email = $("#chef_email").val();

        if(isEmpty(title)){
            $(".error_title").show();
            returnValue = false;
        } else {
            $(".error_title").hide();
        }

        if(isEmpty(servingCount) || parseInt(servingCount) < 1 || isNaN(parseInt(servingCount))){
            $(".error_serving_count").show();
            returnValue = false;
        } else {
            $(".error_serving_count").hide();
        }

        if(isEmpty(cookTime) || (!timeRegex.test(cookTime))){
            $(".error_cook_time").show();
            returnValue = false;
        } else {
            $(".error_cook_time").hide();
        }

        if(isEmpty(temperature) || parseFloat(temperature) < 1  || isNaN(parseFloat(temperature))){
            $(".error_cook_temperature").show();
            returnValue = false;
        } else {
            $(".error_cook_temperature").hide();
        }

        if(isEmpty(instructions)){
            $(".error_instructions").show();
            returnValue = false;
        } else {
            $(".error_instructions").hide();
        }

        if(isEmpty(email) || (!emailRegex.test(email))){
            $(".error_chef_email").show();
            returnValue = false;
        } else {
            $(".error_chef_email").hide();
        }

        var tbody = $(".ingredient-table tbody tr");
        var length = tbody.length;
        if(length < 1){
            $(".error_ingredient").show();
            returnValue = false;
        } else {
            $(".error_ingredient").hide();
            var nameLength = 0;
            var quantityLength = 0;
            $.each($(".ingredient_name"), function(elm, index){
                if(isEmpty($(elm).val())){
                    nameLength = nameLength+1;
                }
            });

            $.each($(".ingredient_quantity"), function(elm, index){
                if(isEmpty($(elm).val())){
                    quantityLength = quantityLength+1;
                }
            });

            if(nameLength > 0 || quantityLength > 0){
                $(".error_ingredient_content").show();
            } else {
                $(".error_ingredient_content").hide();
            }
        }

        return returnValue;
    }

    function isEmpty(value) {
        if (value != undefined) {
            value = value.replace(/^\s+/, "").replace(/\s+$/, "");
            if (value === "") {
                return true
            } else {
                return false
            }
        } else {
            return true
        }
    }
});