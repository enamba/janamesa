<?php

/**
 * Description of Picture
 * @package file
 * @subpackage picture
 * @author mlaug
 */
class Default_File_Picture {

    /**
     * we use timthumb beneeth public
     * @param string $file
     * @param int $toHeight
     * @param int $toWidth
     * @param boolean $uncolor
     * @param string $overlay
     * @return mixed boolean|string
     */
    public function resize($file = null, $toHeight = 75, $toWidth = 115, $uncolor = false, $overlay = null){
        return sprintf("/timthumb.php?src=%s&w=%d&h=%d&f=%d",
                $file,
                $toWidth,
                $toHeight,
                $uncolor ? 2 : 0);
    }


}
?>
