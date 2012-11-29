<?php
/**
 * @author mlaug
 */
class Administration_Upselling_StorageController extends Default_Controller_AdministrationBase {

    /**
     * @author vpriem
     * @since 04.07.2011
     */
    public function addAction(){
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Administration_Upselling_Storage_Add();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                $values['orderedAt'] = date(DATE_DB, strtotime($values['orderedAt']));
                $values['deliverEstimation'] = date(DATE_DB, strtotime($values['deliverEstimation']));
                $values['delivered'] = date(DATE_DB, strtotime($values['delivered']));

                $storage = new Yourdelivery_Model_Upselling_Storage();
                $storage->setData($values);
                $storage->setAdminId($this->session->admin->getId());
                $storage->save();

                $this->success(__b("New gooods successfully inserted"));
                $this->_redirect('/administration_upselling_goods/index');
            } else {
                $this->error($form->getMessages());
            }
        }
    }

}
