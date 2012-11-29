<?php

/**
 * Description of CompanyController
 *
 * @author mlaug
 */
class Request_CompanyController extends Default_Controller_RequestBase {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.04.2011 (refactored)
     * @return json
     */
    public function codeAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $company = $this->session->company;

        if (is_null($company) || !is_object($company)) {
            return false;
        }

        if ($this->getCustomer()->isEmployee()) {
            if ($this->getCustomer()->getCompany()->getId() == $company->getId()) {
                $codeVariant = $this->getRequest()->getParam('codeVariant', null);
                $needCode = $this->getRequest()->getParam('needCode', null);

                $company->setData(array(
                    'code' => $needCode == 'true' ? true : false,
                    'codeVariant' => (integer) $codeVariant
                ))->save();

                echo json_encode(array(
                    'result' => 'success',
                    'msg' => __('Projektcodeeinstellungen wurden erfolgreich geändert.')
                ));
                return;
            } else {
                echo json_encode(array(
                    'result' => 'error',
                    'msg' => __('Sie haben keine Rechte, diese Firma zu bearbeiten.')
                ));
                return;
            }
        }
        return true;
    }

    public function removecostcenterAtion() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        if ($this->getCustomer()->isEmployee()) {
            $id = $this->getRequest()->getParam('id', null);
            if (!is_null) {
                $department = new Yourdelivery_Model_Department($id);
                $department->delete();
            }
        }
    }

    /**
     * invokes newPassAdmin() und displays the result
     * @author Allen Frank <frank@lieferando.de>
     * @since 18-04-11
     * @see Yourdelivery_Model_Customer->newPassAdmin()
     */
    public function newpassemployeeAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $id = $this->getRequest()->getParam('id', null);
        if (is_null($id)) {
            echo json_encode(array(
                'result' => false,
                'msg' => __('Fehler!')
            ));
            return;
        }

        if ($this->getCustomer()->isEmployee()) {

            $cust = new Yourdelivery_Model_Customer($id);
            $state = $cust->newPassAdmin();

            $msg = null;
            switch ($state) {
                default:
                    echo json_encode(array(
                        'result' => false,
                        'msg' => __('Fehler!')
                    ));
                    return;
                    break;
                case 0:
                    echo json_encode(array(
                        'result' => true,
                        'msg' => __('Ihr Passwort wurde geändert und an die angegebene Email-Adresse gesendet.')
                    ));
                    return;
                    break;
                case 1:
                    echo json_encode(array(
                        'result' => false,
                        'msg' => __('Geben Sie eine gültige Email-Adresse ein.')
                    ));
                    return;
                    break;
                case 2:
                    echo json_encode(array(
                        'result' => false,
                        'msg' => __('Die von Ihnen eingegebene Email-Adresse existiert nicht.')
                    ));
                    return;
                    break;
            }
        }
    }

    /**
     * invokes newPassAdmin() und displays the result
     * @author Allen Frank <frank@lieferando.de>
     * @since 18-04-11
     * @see Yourdelivery_Model_Customer->newPassAdmin()
     * @return json
     */
    public function newpassescompAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $compId = $this->getRequest()->getParam('id', null);
        if (is_null($compId)) {
            echo json_encode(array(
                'result' => false,
                'msg' => __('Fehler!')
            ));
            return;
        }
        if ($this->getCustomer()->isEmployee()) {
            $company = null;
            try {
                $company = new Yourdelivery_Model_Company($compId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
                $this->error(__('Company konnte nicht erstellt werden'));
            }

            $msgError = null;
            $msgSuccess = null;

            foreach ($company->getEmployees() as $mem) {

                // get Customer from BudgetGroup
                try {
                    $cust = new Yourdelivery_Model_Customer($mem->getId());
                    $status = $cust->newPassAdmin();

                    switch ($status) {
                        default:
                            $msgError .= __('Fehler bei Mitarbeiter %s - * andere Mitarbeiter sind nicht betroffen', $cust->getFullname()) . '; ';
                            break;
                        case 0:
                            $cust->createPersistentMessage('success', __('Ihr Passwort wurde vom Firmenadmin zurückgesetzt'));
                            break;
                        case 1:
                            $msgError .= __('Fehler bei Mitarbeiter %s, (Keine gültige Email-Adresse) - * andere Mitarbeiter sind nicht betroffen', $cust->getFullname()) . '; ';
                            break;
                        case 2:
                            $msgError .= __('Fehler bei Mitarbeiter %s, (Email-Adresse existiert nicht) - * andere Mitarbeiter sind nicht betroffen', $cust->getFullname()) . '; ';
                            break;
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $msgError .= $e->getMessage();
                }
            }

            if (is_null($msgError)) {
                echo json_encode(array(
                    'result' => true,
                    'msg' => __('Alle Passwörter wurden geändert und an die jeweils zugehörige Email-Adresse gesendet ')
                ));
                return;
            } else {
                echo json_encode(array(
                    'result' => false,
                    'msg' => $msgError
                ));
                return;
            }
        }
    }

    /**
     * invokes newPassAdmin() und displays the result
     * @author Allen Frank <frank@lieferando.de>
     * @since 18-04-11
     * @see Yourdelivery_Model_Customer->newPassAdmin()
     * @return json
     */
    public function newpassesbudgetAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $budgetId = $this->getRequest()->getParam('id', null);
        if (is_null($budgetId)) {
            echo json_encode(array(
                'result' => false,
                'msg' => __('Fehler!')
            ));
            return;
        }
        if ($this->getCustomer()->isEmployee()) {
            try {
                $budget = new Yourdelivery_Model_Budget($budgetId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__('Model Budget konnte nicht erstellt werden'));
            }
            $msgError = null;
            $msgSuccess = null;
            foreach ($budget->getMembers() as $mem) {
                $status = $mem->newPassAdmin();
                switch ($status) {
                    default:
                        $msgError .= __(' Fehler bei Mitarbeiter %s - * andere Mitarbeiter sind nicht betroffen', $mem->getName());
                        break;
                    case 0:
                        $mem->createPersistentMessage('success', __('Ihr Passwort wurde vom Firmenadmin zurückgesetzt'));
                        break;
                    case 1:
                        $msgError .= __(' Fehler bei Mitarbeiter %s (Keine gültige Email-Adresse.) - * andere Mitarbeiter sind nicht betroffen', $mem->getName());
                        break;
                    case 2:
                        $msgError .= __(' Fehler bei Mitarbeiter %s (Email-Adresse existiert nicht.) - * andere Mitarbeiter sind nicht betroffen', $mem->getName());
                        break;
                } // end switch
            }
            if (is_null($msgError)) {
                echo json_encode(array(
                    'result' => true,
                    'msg' => __('Alle Passwörter wurden geändert und an die jeweils zugehörige Email-Adresse gesendet.')
                ));
            } else {
                echo json_encode(array(
                    'result' => false,
                    'msg' => $msgError
                ));
            }
        }
    }

    public function removebudgettimeAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $budgetId = $this->getRequest()->getParam('budgetId', null);
        $budgetTimeId = $this->getRequest()->getParam('budgetTimeId', null);

        if (is_null($budgetId) || is_null($budgetTimeId)) {
            return false;
        }

        $budget = null;
        try {
            $budget = new Yourdelivery_Model_Budget($budgetId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            echo 0;
            return;
        }

        $check = $budget->removeBudgetTime($budgetTimeId);

        echo $check ? 1 : 0;
        return;
    }

}
