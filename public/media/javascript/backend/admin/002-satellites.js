var id;
var color1;
var color2;            
var editor, html = '';
        
function ydchange() {
   
    color1=$(".gradient-editor-color-stop").eq(0).children(".gradient-editor-color").css("background-color");
    color2=$(".gradient-editor-color-stop").eq(1).children(".gradient-editor-color").css("background-color");
    $(".color_picker").eq(id).css("background","-moz-linear-gradient(center top ,"+color1+","+color2+")").css("background", "-webkit-gradient(linear, left top, left bottom, from("+color1+"), to("+color2+"))");
    return; 
}

function reset(){
    $(".color_picker").removeClass("color_picker");  
    $(".click").draggable("destroy");
    $(".click").resizable("destroy");
    $("#accordion").remove();
    $("#gradientEditor").remove();
    $("#dialog").remove();
}
           
function showcolorpicker(element){
    $("body").append("<div id=dialog><div id=gradientEditor style=position:absolute;z-index:1050;></div></div>");
    $( "#dialog" ).dialog({
        position: ['right','top'] ,
        height: 335 , 
        width: 551, 
        buttons: {
            "transparent": function() {
                $(element).css("background", "none");
            }   
        },
        close: function(event, ui) {
            $(this).remove();
            var style=$(".color_picker").eq(id).attr("style");
            $(".color_picker").eq(id).attr("style", style+" background: -webkit-gradient(linear, left top, left bottom, from("+color1+"), to("+color2+"); background:"+color1+";background: -moz-linear-gradient(center top ,"+color1+","+color2+")");
            $(ui).dialog('destroy');
    }
    });
}
            
function newelement(){            
    $("body").append('<div id="dialog" title="Drag and Drop your Element"></div>');
    $( "#dialog" ).dialog({
        draggable: false,
        position: ['right','top'] ,
        height: 288 , 
        width: 551, 
        close: function(event, ui) {
            $(this).remove();
            $(ui).dialog('destroy');
        }
    });
    $("#dialog").html("<img src=/media/images/yd-background/yd-loading-animation.gif />");
    
    $("#dialog").load('/request_administration_satellite/template?s=elements', function() {
        $("ui-dialog").draggable( "destroy" );
    //$("#dialog").html("<div id=accordion class=ui-widget-content ui-corner-all style=position:absolute;z-index:1001><h3 class=ui-widget-header ui-corner-all>Element Box</h3></div>");
    });
            
    $("#accordion").css("top","0");
    $("#accordion").css("min-height","100px").css("width","100%");
}

function writecontextmenu(){
    var html=("<div class=contextMenu id=myMenu style=display:none><ul><li id=size>Size</li> <li id=cut> Cut</li><li id=drag> Drag</li><li id=backgroundcolor> Background Color</li><li id=shadow> Shadow</li> <li id=elementbox> Neues Element</li><li id=back>In den Hintergrund</li><li id=editor>Html Editor</li><li id=copy>Copy</li><li id=corners>Ecken</li><li id=border>Border</li><li id=backgroundImage>Background Image</li><li id=image>Image</li></ul></div>");
    $("body").append(html);
}
            
            
                
function contextmenu(){
    $('.click').contextMenu('myMenu', {
        onShowMenu: function(e, menu) {
            if ($(e.target).parent().attr("id") == "firstclick") {
                     
                $('#drag, #cut, #size, #backgroundcolor, #shadow, #back,  #editor, #copy, #corners, #border, #backgroundImage, #image ', menu).remove();
                            
            }
                        
            if ($(e.target).css("z-index") == "auto") {
                     
                $('#back', menu).remove();
                            
            }
            
            if ($(e.target).hasClass("smarty") || $(e.target).parents().hasClass("smarty")) {
                     
                $('#size, #editor, #copy, #image', menu).remove();
                            
            }
         
            
            return menu;
                        
        },

        bindings: {

            'drag': function(t) {
                reset();
                $(t).draggable();
                $(t).css("z-index","10");
            },

            'cut': function(t) {
                reset();
                $(t).remove();

            },

            'size': function(t) {
                reset();
                $(t).resizable();
                //Dialog for Size
                $("body").append('<div id="dialog" title="Größe Einstellen dialog"><form><label for="width">Breite</label><input  name="width" id="width" class="text ui-widget-content ui-corner-all" /><label for="height">Höhe</label><input  name="height" id="height" class="text ui-widget-content ui-corner-all" /></div>');
                $( "#dialog" ).dialog({
                    position: ['right','top'] ,
                    buttons: {
                        "groeße setzen": function() {
                            $(t).css("width", $("#dialog #width").val());
                            $(t).css("height", $("#dialog #height").val());
                                  
                        }
                    },
                    close: function(event, ui) {
                        $(this).remove();
                        $(ui).dialog('destroy');
                    }
                              
                });

            },

            'backgroundcolor': function(t) {
                reset();
                showcolorpicker(t);
                $(t).addClass("color_picker");
                $("#gradientEditor").toggle();
                $("#gradientEditor").gradientEditor();
                id=$(".color_picker").index(t);
                            
                            

            },
                        
            'shadow': function(t) {
                reset();
                $("body").append('<div id="dialog" title="Shadow dialog"><label for="color">Farbe</label><input  name="color" id="color" class="text ui-widget-content ui-corner-all" /><label for="horizontal">horizontal</label><input  name="horizontal" id="horizontal" class="text ui-widget-content ui-corner-all" /><label for="vertical">vertical</label><input  name="vertical" id="vertical" class="text ui-widget-content ui-corner-all" /><label for="size">size</label><input  name="size" id="size" class="text ui-widget-content ui-corner-all" /></div>');
                $( "#dialog" ).dialog({
                    position: ['right','top'] ,
                    buttons: {
                        "shadow setzen": function() {
                            $(t).css("box-shadow", +$("#dialog #horizontal").val()+"px "+$("#dialog #vertical").val()+"px "+$("#dialog #size").val()+"px #"+$("#dialog #color").val()); 
                            $(this).remove();
                        }
                    },
                    close: function(event, ui) {
                        $(this).remove();
                        $(ui).dialog('destroy');
                    }
                              
                });
                            
                $('#dialog #color').ColorPicker({
                    onSubmit: function(hsb, hex, rgb, el) {
                                    
                        $(el).val(hex);
                        $(el).ColorPickerHide();
                    },
                    onBeforeShow: function () {
                        $(this).ColorPickerSetColor(this.value);
                    },
                    onShow: function(){
                        $(".colorpicker").css("z-index","3000");
                    }
                })
                .bind('keyup', function(){
                    $(this).ColorPickerSetColor(this.value);
                });
                            
            },
                        
            'elementbox': function(t) {
                reset();
                newelement();
                                
                        
            },   
                        
            'back': function(t) {
                reset();
                $(t).css("z-index","0");
            },
                        
            'editor': function(t) {
                reset();
                var html2=$(t).html();
                createEditor(html2,t);
            },
            'copy': function(t) {
                reset();
                var html2=$(t).clone().wrap("<div></div>").parent().html();
                $(html2).appendTo('#yd-satellite-content');
                contextmenu();
                            

            },
            'corners': function(t) {
                reset();
                            
                $("body").append('<div id="dialog" title="Runde_Ecken dialog"><label for="corners">Radius</label><input  name="corners" id="corners" class="text ui-widget-content ui-corner-all" /></div>');
                $( "#dialog" ).dialog({
                    position: ['right','top'] ,
                    buttons: {
                        "Ecken setzen": function() {
                            $(t).css("border-radius", $("#dialog #corners").val()+"px");
                        }
                    },
                    close: function(event, ui) { 
                        $(this).remove();
                        $(ui).dialog('destroy');

                    }
                              
                });

            },
            'border': function(t) {
                reset();
                            
                $("body").append('<div id="dialog" title="Border dialog"><label for="color">Farbe</label><input  name="color" id="color" class="text ui-widget-content ui-corner-all" /><label for="size">Größe</label><input  name="size" id="size" class="text ui-widget-content ui-corner-all "/><br/><lable for="art">Art</lable><br/><select name="art" id="art"><option value="solid">Solid</option><option value="dotted">Dotted</option><option value="dashed">dashed</option><option value="double">double</option></select></div>');
                $( "#dialog" ).dialog({
                    position: ['right','top'] ,
                    buttons: {
                        "Border setzen": function() {
                            $(t).css("border", $("#dialog #size").val()+"px "+$("#dialog #art").val()+" #"+$("#dialog #color").val());
                        }
                    },
                    close: function(event, ui) { 
                        $(this).remove();
                        $(ui).dialog('destroy');
           
                    }
                              
                });
                            
                $('#dialog #color').ColorPicker({
                    onSubmit: function(hsb, hex, rgb, el) {
                                    
                        $(el).val(hex);
                        $(el).ColorPickerHide();
                    },
                    onBeforeShow: function () {
                        $(this).ColorPickerSetColor(this.value);
                    },
                    onShow: function(){
                        $(".colorpicker").css("z-index","3000");
                    }
                })
                .bind('keyup', function(){
                    $(this).ColorPickerSetColor(this.value);
                });
            },
            'backgroundImage': function(t){
                reset();
                $("body").append('<div id=dialog title="Upload your Background Image"><div id="upload_button" >Upload File</div><span id="status_message" ></span><ul id="files_list" ></ul><label for="repat">Repat</label><select name="repat" id="repeat" class="text ui-widget-content ui-corner-all" ><option value="repeat">ja</option><option value="no-repeat">nein</option><option value="repeat-y">repeat-y</option><option value="repeat-x">repeat-x</option></select><div id="positionshow" style="display:none"><label for="x">Position X</label><input  name="x" id="x" class="text ui-widget-content ui-corner-all" type="text" /><label for="y">Position Y</label><input type="text" name="y" id="y" class="text ui-widget-content ui-corner-all" /></div></div>');
                $( "#upload_button" ).button();
                $( "#dialog" ).dialog({
                    position: ['right','top'] ,
                    buttons: {
                        "Optionen übernehmen": function() {
                            //if( $("#positionshow").show()){
                            $(t).css("background-repeat", $('#repeat').val());
                            
                            $(t).css("background-position", $('#x').val()+"px "+ $('#y').val()+"px");
                        //$("#y").
                        //$("#repeat").
                        // };
                        }
                    },
                    close: function(event, ui) { 
                        $(this).remove();   
                        $(ui).dialog('destroy');
                    }
     
                });
                $("#repeat").bind('change', function(){
                    $("#positionshow").show();

                });
              
                
                var object=$(t).index();
                var btnUpload=$('#upload_button');
                var status=$('#status_message');
                var templatename=$("#header ul li a.active").attr("id");
                new AjaxUpload(btnUpload, {
                    action: '/request_administration_satellite/backgroundimage?id='+satelliteId+'&object='+object+'&templatename='+templatename,
                    name: 'uploadfile',
                    onSubmit: function(file, ext){
                        if (! (ext && /^(png)$/.test(ext))){
                            // extension is not allowed
                            status.text('Only PNG files are allowed');
                            return false;
                        }
                        status.text('Uploading...');
                    },
                    onComplete: function(file, response){
                        var jetzt = new Date();
                        //On completion clear the status
                        status.text('');
                        //Add uploaded file to list
                        if(response==="success"){
                            $('#files_list').html('');
                            $('<li></li>').appendTo('#files_list').html('<img style="width:100px;height:100px;" src="/storage/satellites/'+satelliteId+'/backgroundimages/'+templatename+'/'+object+'.png?date='+jetzt.getTime()+' alt="" /><br />'+object+'.png').addClass('success');
                        } else{
                            $('<li></li>').appendTo('#files_list').text(file).addClass('error');
                        }
                        
                        $(t).css("background-image","url(/storage/satellites/"+satelliteId+"/backgroundimages/"+templatename+"/"+object+".png?date="+jetzt.getTime()+")");
                       
                    }
                });
            },
            
            'image': function(t){
                reset();
                $("body").append('<div id=dialog title="Upload your Image"><div id="upload_button" >Upload File</div><span id="status_message" ></span><ul id="files_list" ></ul></div>');
                $( "#upload_button" ).button();
                $( "#dialog" ).dialog({
                    position: ['right','top'] ,
                    close: function(event, ui) { 
                        $(this).remove();      
                        $(ui).dialog('destroy');
                    }
     
                });
                var object=$(t).index();
                var btnUpload=$('#upload_button');
                var status=$('#status_message');
                var templatename=$("#header ul li a.active").attr("id");
                new AjaxUpload(btnUpload, {
                    action: '/request_administration_satellite/image?id='+satelliteId+'&object='+object+'&templatename='+templatename,
                    name: 'uploadfile',
                    onSubmit: function(file, ext){
                        if (! (ext && /^(png)$/.test(ext))){
                            // extension is not allowed
                            status.text('Only PNG files are allowed');
                            return false;
                        }
                        status.text('Uploading...');
                    },
                    onComplete: function(file, response){
                        var jetzt = new Date();
                        //On completion clear the status
                        status.text('');
                        //Add uploaded file to list
                        if(response==="success"){
                            $('#files_list').html('');
                            $('<li></li>').appendTo('#files_list').html('<img style="width:100px;height:100px;" src="/storage/satellites/'+satelliteId+'/images/'+templatename+'/'+object+'.png?date='+jetzt.getTime()+' alt="" /><br />'+object+'.png').addClass('success');
                        } else{
                            $('<li></li>').appendTo('#files_list').text(file).addClass('error');
                        }
                        
                        $('<img style="width:100px;height:100px;" src="/storage/satellites/'+satelliteId+'/images/'+templatename+'/'+object+'.png?date='+jetzt.getTime()+' alt="" />').appendTo(t);
                       
                    }
                });
            }
            
                    
        }
    });
                    
}
           

function createEditor(html,pos)
{
            
                
    $("body").append('<div id="dialog"><form id="editorform" method="post" action="somepage"><textarea id="edit" name="content" class="tinymce" style="width:70%";>'+html+'</textarea></div>');
    $("#gethtml").click(function(){
        $(pos).html($('#edit').html());
    });
    $( "#dialog" ).dialog({ 
        position: ['right','top'] ,
        height: 350 , 
        width: 700,
        buttons:{
            "Uebernehmen": function(){
                $(pos).html($('#edit').html());
            }
        },
        close: function(event, ui){
            $(this).remove();
            $('#edit').tinymce().hide();
            $('#editorform').remove();
            $(ui).dialog('destroy');
                    
        }
                
            
    });
    $('textarea.tinymce').tinymce({
        // Location of TinyMCE script
        script_url : 'jscripts/tiny_mce/tiny_mce.js',

        // General options
        theme : "advanced",
        plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
        content_css : "css/content.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "lists/template_list.js",
        external_link_list_url : "lists/link_list.js",
        external_image_list_url : "lists/image_list.js",
        media_external_list_url : "lists/media_list.js"
                       
                    
    });
              
             
               
}
    
function sideload(url){             
    $("#yd-satellite-content").load(url, function(){
        $("#yd-satellite-content").children().addClass("click");
        $("body").append('<a id="buttonsave" style="position:absolute;top:5px;left:110px;">Save</a>');
        $("#buttonsave").button();
        $("body").append('<div class="click" id="firstclick" style="position:absolute;top:5px;left:10px;">Right Click</div>');
        $("#firstclick").button();
        $("body").append('<div id="back" style="position:absolute;top:5px;left:500px;">Back zum Adminbackend</div>');
        $("#back").button();
        $("body").append('<div id="delete" style="position:absolute;top:5px;left:180px;">Alle Elemente löschen</div>');
        $("#delete").button();
        $("#delete").click(function(){
            $("#yd-satellite-content").html("");
        });
        $("#back").click(function(){
            window.location = "/administration_satellite/edit/satelliteId/"+satelliteId;
        });
        $("#buttonsave").click(function(){
            reset();
            writeHTML();
        });
        writecontextmenu();
        contextmenu();  
        $("body").append('<div id="informationdialog" title="Informationen">*Elemente müssen von OBEN nach UNTEN angeordnet werden d.h. ein Element von unten darf nicht einfach nach ganz oben gezogen werden ! Und andersrum!<br/><br/>*Als Bildformat darf nur PNG verwendet werden!<br/><br/>*Manche Elemente können nicht mit dem HTML Editor bearbeitet werden, diese enthalten Code aus der Datenbank.<br/><br/>*Auf den Tabs oben kann das Template gechanged werde zb von Home zum Impressum, wenn das Template noch nicht existiert werden die Elemente übernommen. Man sieht an dem hervorstehenden Tab wo man sich befindet!<br/><b>**Speichern nicht vergessen**</b></div>');
        $( "#informationdialog" ).dialog({
            modal: true,
            close: function(event, ui) { 
                $(this).remove();     
                $(ui).dialog('destroy');
            }
     
        });
        
    }); 
}

function newsideload(url){
        
    $("#yd-satellite-content").load(url , function(){
        $("#yd-satellite-content").children().addClass("click");
        contextmenu(); 
    });  
}

function writeHTML(){
    
    var savehtml= $("#yd-satellite-content").html();
    var templatename=$("#header ul li a.active").attr("id");
    $.ajax({
        url:'/request_administration_satellite/save?id='+satelliteId+'&templatename='+templatename,
        type:'post',
        data: {
            template : savehtml
        },
        success: function(){
            alert("success save");
        }
         
    });           
                
    //Json Obj for Smarty Tags
    var length=$(".smarty").length;
    var type;
    var test = [];
    for(var i=0; i<length;i++){
        var styles = $('.smarty').eq(i).attr('style').split(';');  
        for(var z=styles.length-1;z>=0;z--){
            type=styles[z].split(":");
            test[z] = ('" ' + type[0] + ' " : " ' + type[1] + ' "');
            alert(test[z]);
        }
    }
                
}

$(document).ready(function(){
    
    /**
             * @author vpriem
             * @since 16.06.2011
             */
    $(':text.yd-color-picker').each(function(){
        var input = this;
        $(this)
        .ColorPicker({
            onSubmit: function(hsb, hex, rgb) {
                input.value = "#" + hex;
                $(input).ColorPickerHide();
            },
            onBeforeShow: function () {
                $(this).ColorPickerSetColor(this.value);
            },
            onChange: function (hsb, hex, rgb, el) {
                input.value = "#" + hex;
            }
        })
        .keyup(function(){
            $(this).ColorPickerSetColor(this.value);
        });    
    }); 
    
    if ( $('#yd-satellite-content').length > 0 ){
        var jetzt = new Date();
        var templatename=$("#header ul li a.active").attr("id");
        sideload('/storage/satellites/'+satelliteId+'/template/'+templatename+'.html?date='+jetzt.getTime());    
    
    
        $("#header ul li a").click(function(){
            $("#header ul li a").removeClass("active");
            $(this).addClass("active");
            var jetzt = new Date();
            var templatename=$("#header ul li a.active").attr("id");
            newsideload('/storage/satellites/'+satelliteId+'/template/'+templatename+'.html?date='+jetzt.getTime());
        }
        );
    }
    
});