<?php

/**
 * escape string so that it won't give any
 * compiling errors during latex => pdf generation
 * @author mlaug, vpriem
 * @since 18.08.2010
 * @param string $string
 * @param boolean $accents
 * @return string
 */
function smarty_modifier_escape_latex($string, $accents = true)
{
    
    $string = stripslashes($string);
    // replace special characters
    $sr = array(
        "\\" => "",
        '$'  => "\\$",
        "%"  => "\\%",
        "_"  => "\\_",
        "}"  => "\\}",
        "&"  => "\\&",
        "#"  => "\\#",
        "{"  => "\\{",
        '´'  => "'",
        '`'  => "'",
        '"'  => "'",
        "^"  => "\\^{}",
        "~"  => "\\~{}",
        "€"  => "{\\euro} ",
    );
    $string = str_replace(array_keys($sr), array_values($sr), $string);
    
    // check if we want to change accents
    if ($accents) {
        $sr = array(
            "À" => "\\`{A}", 
            "Á" => "\\'{A}", 
            "Â" => "\\^{A}", 
            "Ã" => "\\~{A}", 
            "Ä" => '\\"{A}', 
            "Å" => "{\\AA}", 
            "Æ" => "{\\AE}",
            "Ç" => "\\c{C}", 
            "È" => "\\`{E}", 
            "É" => "\\'{E}", 
            "Ê" => "\\^{E}", 
            "Ë" => '\\"{E}', 
            "Ì" => "\\`{I}", 
            "Í" => "\\'{I}", 
            "Î" => "\\^{I}", 
            "Ï" => '\\"{I}', 
            "Ñ" => "\\~{N}", 
            "Ò" => "\\`{O}", 
            "Ó" => "\\'{O}", 
            "Ô" => "\\^{O}", 
            "Õ" => "\\~{O}", 
            "Ö" => '\\"{O}', 
            "Ø" => "{\\O}",  
            "Œ" => "{\\OE}",
            "Ù" => "\\`{U}", 
            "Ú" => "\\'{U}", 
            "Û" => "\\^{U}", 
            "Ü" => '\\"{U}', 
            "Ý" => "\\'{Y}", 
            "ß" => "{\ss}",
            "à" => "\\`{a}", 
            "á" => "\\'{a}", 
            "â" => "\\^{a}", 
            "ã" => "\\~{a}", 
            "ä" => '\\"{a}', 
            "å" => "{\\aa}",
            "æ" => "{\\ae}",            
            "ç" => "\\c{c}", 
            "è" => "\\`{e}", 
            "é" => "\\'{e}", 
            "ê" => "\\^{e}", 
            "ë" => '\\"{e}', 
            "ì" => "\\`{i}", 
            "í" => "\\'{i}", 
            "î" => "\\^{i}", 
            "ï" => '\\"{i}', 
            "ñ" => "\\~{n}", 
            "ò" => "\\`{o}", 
            "ó" => "\\'{o}", 
            "ô" => "\\^{o}",             
            "õ" => "\\~{o}", 
            "ö" => '\\"{o}', 
            "ø" => "{\\o}",
            "œ" => "{\\oe}",            
            "ù" => "\\`{u}", 
            "ú" => "\\'{u}", 
            "û" => "\\^{u}", 
            "ü" => '\\"{u}', 
            "ý" => "\\'{y}", 
            "ÿ" => '\\"{y}', 
            // pl_PL
            "Ć" => "\\'{C}", 
            "Ł" => "{\\L}",
            "Ś" => "\\'{S}", 
            "Ź" => "\\'{Z}", 
            "Ż" => "\\.Z",   
            "ą" => "\\k{a}", 
            "ć" => "\\'{c}", 
            "ę" => "\\k{e}", 
            "ł" => "{\\l}",    
            "ń" => "\\'{n}", 
            "ś" => "\\'{s}", 
            "ź" => "\\'{z}", 
            "ż" => "\\.z",   
            
        );
        $string = str_replace(array_keys($sr), array_values($sr), $string);
    }

    $string = trim($string);
    $string = preg_replace("/[\n\r]/", "", $string);
    return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string);

}

?>
