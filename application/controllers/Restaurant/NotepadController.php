<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * restauratn notepad
 *
 * @author alex
 * @since 07.10.2010
 */
class Restaurant_NotepadController extends Default_Controller_RestaurantBase {
    /**
    * show all comments for this restaurant
    * @author alex
    * @since 07.10.2010
    */
    public function indexAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $this->view->assign('comments', Yourdelivery_Model_DbTable_Restaurant_Notepad::getComments($restaurant->getId()));
    }


    /**
     * create new comment
     * @author alex
     */
    public function createAction(){
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $post = $request->getPost();

            if (strlen(trim($post['comment'])) > 0) {
                $madmin = $this->session->masterAdmin;
                $admin = $this->session->admin;

                if ( is_null($madmin) && is_null($admin) ) {
                    $this->error("Kein Admin wurde in der Sitzung gefunden.");
                    $this->_redirect('/restaurant_notepad');
                }
                
                $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();

                if (!is_null($madmin)) {
                    $comment->setMasterAdmin(1);
                    $comment->setAdminId($madmin->getId());
                }
                else {
                    $comment->setMasterAdmin(0);
                    $comment->setAdminId($admin->getId());
                }

                $comment->setRestaurantId($restaurant->getId());
                $comment->setComment($post['comment']);
                $comment->setTime(date("Y-m-d H:i:s", time()));
                $comment->save();
            }
        }
        $this->_redirect('/restaurant_notepad');
    }

    /**
    * init restaurant
    * @return Yourdelivery_Model_Servicetype_Restaurant
    * @author alex
    * @since 02.09.2010
    */
    protected function initRestaurant() {
        if (is_null($this->session->currentRestaurant))
                return null;
        else
            $id = $this->session->currentRestaurant;
        return new Yourdelivery_Model_Servicetype_Restaurant($this->session->currentRestaurant->getId());
    }

}
?>
