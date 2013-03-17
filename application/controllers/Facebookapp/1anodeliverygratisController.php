<?php
class Facebookapp_1anodeliverygratisController extends Default_Controller_Base {
    
    /**
     * redirect the page to correct page
     * @author enamba
     * @since 07.07.2011
     */
    public function indexAction() {
        list($endoded_sig, $payload)= explode('.', $_REQUEST["signed_request"], 2);
        $fbData = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        if (!empty($fbData)&&!$fbData['page']['liked']) {
          $this->_redirect('/facebookapp_1anodeliverygratis/likeme');
        } else { 
          $this->_redirect('/facebookapp_1anodeliverygratis/shareme');
        }
    }
    
    public function likemeAction() {
        
    }
    public function sharemeAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = $request->getParam('name');
            $email = $request->getParam('email');
            $grid = Default_Helper::getTableGrid();
            $db = Zend_Registry::get('dbAdapter');
            $select = $db->select()->from(array('f1' => 'facebookapp_1anodeliverygratis'), array(
                        'id' => 'id'))
                        ->where("email='$email'");

            $rows = $db->query($select);
            $rows = $rows->fetchAll();
            if (count($rows)>0){
                $this->_redirect('/facebookapp_1anodeliverygratis/recadastro');
            } else {
                try {
                    $db->insert("facebookapp_1anodeliverygratis", array(
                        'name' => $user,
                        'email' => $email
                    ));
                }
                catch (Zend_Db_Statement_Exception $e) { }
            }
            $this->_redirect('/facebookapp_1anodeliverygratis/parabens');
        }
    }
    public function recadastroAction() {}
    public function parabensAction(){}
}
?>



