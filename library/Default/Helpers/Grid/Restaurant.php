<?php

/**
 * Helper for Grids in Restaurant Backend
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 03.07.2012
 */
class Default_Helpers_Grid_Restaurant {

    /**
     * options for meal picture
     * @param integer $mealId
     * @param integer $restaurantId
     * 
     * @return string HTML
     * 
     * @author Alex Vait <vait@lieferando.de>
     */
    public static function mealPictureOption($mealId, $restaurantId) {

        if (is_null($mealId) || is_null($restaurantId)) {
            return "";
        }

        $upload = __b("hochladen");

        $fileInputForm = <<<HTML
                <form action="/restaurant_meals/uploadimage" method="POST" enctype="multipart/form-data">
                    <table>
                        <tr>
                            <td>
                                <input type="hidden" name="mealId" value="$mealId"/>
                                <input type="file" name="img"/>
                            </td>
                            <td><input type="submit" value="$upload"/></td>
                        </tr>
                    </table>
                </form>
HTML;


        $file = '/storage/restaurants/' . $restaurantId . "/meals/" . $mealId . "/default.jpg";

        if (!file_exists(APPLICATION_PATH . '/..' . $file)) {
            return $fileInputForm;
        }

        $delete = __b("Soll dieses Bild wirklich gelöscht werden?");

        return
                <<<HTML
        <img src="$file" width="115" height="75">
        &nbsp;&nbsp;
        <a href="/restaurant_meals/removeimage/mealId/$mealId"><img src="/media/images/yd-backend/del-cat.gif" onclick="javascript:return confirm('$delete')"></a>
        <br/><br/>$fileInputForm
HTML;
    }

}

?>
