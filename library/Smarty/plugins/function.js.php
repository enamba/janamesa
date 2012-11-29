<?php
/**
 * @author vpriem
 * @since 20.10.2010
 */
function smarty_function_js ($params, $smarty, $template) {

    if (empty($params['dirname'])) {
        trigger_error("js: missing 'dirname' parameter", E_USER_WARNING);
        return;
    }
    $dirname = $params['dirname'];

    if (APPLICATION_ENV == "production") {
        if (file_exists(APPLICATION_PATH . "/../public/media/js/" . $dirname . "/build.js")) {
            return '<script type="text/javascript" src="/media/js/' . $dirname . '/build.js"></script>';
        }
        // @todo: merge files together
    }
    
    $files = @scandir(APPLICATION_PATH . "/../public/media/js/" . $dirname);
    if (!is_array($files)) {
        trigger_error("js: bad directory", E_USER_WARNING);
        return;
    }

    $scripts = "";
    foreach ($files as $file) {
        if (substr($file, -3) == ".js") {
            $scripts .= '<script type="text/javascript" src="/media/js/' . $dirname . '/' . $file . '"></script>' . LF;
        }
    }
    return $scripts;

}
