<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 21.06.2012 
 */
class Administration_Request_Blacklist_ValueController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 21.06.2012 
     */
    public function deleteAction() {

        $this->_disableView();

        $request = $this->getRequest();
        $id = $request->getParam('id');
        
        try {
            $value = new Yourdelivery_Model_Support_Blacklist_Values($id);
            $value->setDeleted(!$value->getDeleted());
            $value->save();

            if ($value->getDeleted()) {
                $this->logger->adminInfo(sprintf("Successfully delete blacklist entry #%s %s %s", $value->getId(), $value->getType(), $value->getValue()));
                return $this->_json(array(
                    'success' => __b("Eintrag wurde erfolgreich gelöscht"),
                    'deleted' => $value->getDeleted(),
                ));
            }

            $this->logger->adminInfo(sprintf("Successfully restore blacklist entry #%s %s %s", $value->getId(), $value->getType(), $value->getValue()));
            return $this->_json(array(
                'success' => __b("Eintrag wurde erfolgreich wiederhergestellt"),
                'deleted' => $value->getDeleted(),
            ));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }

        return $this->_json(array(
            'error' => __b("Eintrag konnte nicht gefunden werden")
        ));
    }

}
