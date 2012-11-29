$(document).ready(function(){

    /**
     * More/Less
     * @author oknoblich
     * @since 07.03.2011
     */

    $('.yd-read-more').attr('style', 'width:1020px').after('<a class="yd-read-more-link">Mehr lesen...</a>').hide();

    $(".yd-read-more-link").live('click', function(){
        $(this).siblings(".yd-read-more").slideToggle("fast");
        $(this).text() == 'Mehr lesen...' ? $(this).text('Weniger lesen...') : $(this).text('Mehr lesen...');
    });

    $('.yd-read-more-fr').attr('style', 'width:1020px').after('<a class="yd-read-more-link-fr">Plus...</a>').hide();

    $(".yd-read-more-link-fr").live('click', function(){
        $(this).siblings(".yd-read-more-fr").slideToggle("fast");
        $(this).text() == 'Plus...' ? $(this).text('Ou Moins...') : $(this).text('Plus...');
    });
    
    $('.yd-read-more-pl').attr('style', 'width:1020px').after('<a class="yd-read-more-link-pl">Czytaj dalej...</a>').hide();

    $(".yd-read-more-link-pl").live('click', function(){
        $(this).siblings(".yd-read-more-pl").slideToggle("fast");
        $(this).text() == 'Czytaj dalej...' ? $(this).text('Mniej dalej...') : $(this).text('Czytaj dalej...');
    });
    
    $('.yd-read-more-br').attr('style', 'width:1020px').after('<a class="yd-read-more-link-br">Ler mais...</a>').hide();

    $(".yd-read-more-link-br").live('click', function(){
        $(this).siblings(".yd-read-more-br").slideToggle("fast");
        $(this).text() == 'Ler mais...' ? $(this).text('Ler menos...') : $(this).text('Ler mais...');
    });
    
    $('.yd-read-more-es').attr('style', 'width:1020px').after('<a class="yd-read-more-link-es">Leer más...</a>').hide();

    $(".yd-read-more-link-es").live('click', function(){
        $(this).siblings(".yd-read-more-es").slideToggle("fast");
        $(this).text() == 'Leer más...' ? $(this).text('Leer menos...') : $(this).text('Leer más...');
    });

});