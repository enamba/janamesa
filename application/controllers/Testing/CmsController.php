<?php

/**
 * Description of CmsController
 * Testlevel-2 und so
 *
 * @author mpantar,afrank
 * @since 24.3.2011
 *
 */
class Testing_CmsController extends Default_Controller_Base {
    /*
     * Name of the authenticated User
     */

    protected $_user = 'user';
    protected $_password = 'pwd';
    protected $_auth = false;
    /*
     * new tester: insert username(example: <fullPrename>_<firstLetterName>) into $_allowedUser. password will be 'yd-'.username.'2011'
     * with this the user can be authenticated within lieferando.de/testing_cms
     */
    protected $_allowedUser = array('it', 'tester');

    /**
     * authorisation
     * @author Allen Frank <frank@lieferando.de>
     * @since 12.05.2011
     */
    public function preDispatch() {

        if ($this->_auth == false) {
            if (!isset($_SERVER['PHP_AUTH_USER'])) {
                header('WWW-Authenticate: Basic realm="CMS"');
                header('HTTP/1.0 401 Unauthorized');
                exit;
            }
        }

        $this->_user = $_SERVER['PHP_AUTH_USER'];
        $this->_password = $_SERVER['PHP_AUTH_PW'];

        if (!$this->_auth) {
            try {
                $yopesoTesterListLocation = APPLICATION_PATH . '/controllers/Testing/yopesoTester';
                $yopesoTesterList = explode("\n", file_get_contents($yopesoTesterListLocation));
                $internalTesterListLocation = APPLICATION_PATH . '/controllers/Testing/internalTester';
                $internalTesterList = explode("\n", file_get_contents($internalTesterListLocation));
                $this->_allowedUser = array_merge($this->_allowedUser, $internalTesterList);
                $this->_allowedUser = array_merge($this->_allowedUser, $yopesoTesterList);
            } catch (Exception $exc) {
                $this->logger->error('File does not exist');
            }

            if ($this->_user === $this->_allowedUser[0]) {
                if (md5($this->_password) === '64a8f3f6c9676355add01490a76ecaed') {// standard admin-password
                    $this->_auth = true;
                } else {
                    $this->_auth = false;
                    $this->_user = 'user';
                }
            } else {
                if ($this->_password === 'yd-' . $this->_user . '2011') {
                    $this->_auth = true;
                } else {
                    $this->_auth = false;
                    $this->_user = 'user';
                }
            }
        } elseif (isset($_SERVER['PHP_AUTH_USER']) && !in_array($this->_user, $this->_allowedUser)) {
            $this->_user = 'user';
            $this->_redirect('/');
        }
    }

    private function validateUser() {

        if ($this->_user != $this->_allowedUser[0] && in_array($this->_user, $this->_allowedUser)) {
            $this->_redirect('/testing_cms/executorsoverview');
        }
        if ($this->_user == 'user') {
            $this->_redirect('/');
        }
    }

    private function aasort(&$array, $key) {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }

    /**
     * redirects to create
     *
     * @author mpantar, afrank
     * @since 25-03-11
     */
    public function indexAction() {

        $this->validateUser();
        $this->_redirect('testing_cms/overviewall');
    }

    /**
     * creates a testcase and redirects to add
     *
     * @author mpantar, afrank
     * @since 29-03-11
     */
    public function createAction() {

        $this->validateUser();

        $form = new Yourdelivery_Form_Testing_Create();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($form->getValue('tag') && $this->getRequest()->getParam('proceed') == 'false') {
                    $tags = Yourdelivery_Model_Testing_TestCase::searchForTags($form->getValue('tag'));

                    if ($tags) {
                        foreach ($tags as $tag) {
                            $ids .= sprintf('<a href="/testing_cms/overview/id/%s" target="blank">%s</a> ', $tag['id'], $tag['id']);
                        }
                        $this->warn('Tag already in use for testcase ' . $ids);
                        $this->_redirect(vsprintf('testing_cms/create/title/%s/author/%s/description/%s/priority/%s/tag/%s/proceed/true', $form->getValues()));
                    }
                }

                $testCase = new Yourdelivery_Model_Testing_TestCase();
                $testCase->setData($form->getValues());
                $id = $testCase->save();
                $this->success('Testcase successfully created.');
                $this->_redirect('testing_cms/add/id/' . $id);
            } else {
                $this->error($form->getMessages());
            }
        }
        $this->view->post = $this->getRequest()->getParams();
    }

    /**
     * edits a testcase
     *
     * @author mpantar
     * @since 29-03-11
     */
    public function edittestcaseAction() {

        $this->validateUser();

        $form = new Yourdelivery_Form_Testing_Create();
        $id = (integer) $this->getRequest()->getParam('id');
        $this->view->id = $id;

        try {
            $testCase = new Yourdelivery_Model_Testing_TestCase($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Requested site does not exists.');
            $this->_redirect('/testing_cms/');
            return;
        }

        $this->view->testcase = $testCase;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if (!is_null($id)) {

                    $testCase = null;
                    try {
                        $testCase = new Yourdelivery_Model_Testing_TestCase($id);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
                        $this->error('Requested site does not exists.');
                        $this->_redirect('/testing_cms/');
                        return;
                    }
                    if ($form->getValue('tag') && $this->getRequest()->getParam('proceed') == 'false') {
                        $tags = Yourdelivery_Model_Testing_TestCase::searchForTags($form->getValue('tag'));

                        if ($tags) {
                            foreach ($tags as $tag) {
                                $ids .= sprintf('<a href="/testing_cms/overview/id/%s" target="blank">%s</a> ', $tag['id'], $tag['id']);
                            }
                            $this->warn('Tag already in use for testcase ' . $ids);
                            $this->_redirect(sprintf('testing_cms/edittestcase/id/%s/proceed/true', $id ));
                        }
                    }
                    $values = $form->getValues();
                    $testCase->setData($values);
                    $testCase->save();

                    $this->success('Testcase editiert');
                    $this->_redirect('testing_cms/overviewall');
                } else {
                    $this->error('Keine id gefunden!');
                }
            }
        }
    }

    /**
     * edits a testcase
     *
     * @author mpantar
     * @since 29-03-11
     */
    public function editexpectationAction() {

        $this->validateUser();

        $form = new Yourdelivery_Form_Testing_Editexpectation();
        $eid = (integer) $this->getRequest()->getParam('eid');
        $id = (integer) $this->getRequest()->getParam('id');
        $testCaseExp = new Yourdelivery_Model_Testing_TestCaseExpectation($eid);
        $this->view->testCaseExp = $testCaseExp;

        if (isset($_POST['finish'])) {
            $this->_redirect('testing_cms/overview/id/' . $id);
        }

        $eid = (integer) $this->getRequest()->getParam('eid');
        $id = (integer) $this->getRequest()->getParam('id');
        $testCaseExp = new Yourdelivery_Model_Testing_TestCaseExpectation($eid);
        $this->view->testCaseExp = $testCaseExp;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if (!is_null($eid)) {

                    $testCaseExp = null;
                    try {
                        $testCaseExp = new Yourdelivery_Model_Testing_TestCaseExpectation($eid);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
                        $this->error('Requested site does not exists.');
                        $this->_redirect('/testing_cms/editexpectation/id/' . $id);
                        return;
                    }
                    $values = $form->getValues();
                    $values['testCaseId'] = $id;
                    if ($form->getValue('imagePath') != null) {
                        $storage = new Default_File_Storage();
                        $storage->setStorage(APPLICATION_PATH . '/../storage/');
                        $storage->setSubFolder('testing/'.$id.'/');
                        $imageStorageName = 'testCase_' . $id . '_exp_' . $eid . '_' . $form->getValue('imagePath');
                        $storage->store($imageStorageName, file_get_contents($form->imagePath->getFileName()));
                        $values['imagePath'] = $imageStorageName;
                    } else {
                        $values['imagePath'] = $testCaseExp->getImagePath();
                        if (isset($_POST['deleteImage'])) {
                            $values['imagePath'] = null;
                        }
                    }
                    $testCaseExp->setData($values);
                    $testCaseExp->save();
                    $this->success('Expectation editiert');
                    $this->_redirect('testing_cms/overview/id/' . $id);
                } else {
                    $this->error('Keine id gefunden!');
                }
            }
        }
    }

    /**
     * adds a expectation
     * @author mpantar, afrank
     * @since 25-03-11
     */
    public function addAction() {

        $this->validateUser();

        $form = new Yourdelivery_Form_Testing_Add();
        $id = (integer) $this->getRequest()->getParam('id');
        $this->view->id = $id;
        try {
            $testing = new Yourdelivery_Model_Testing_TestCase($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Requested site does not exists.');
            $this->_redirect('/testing_cms/');
            return;
        }
        $this->view->title = $testing->getTitle();
        if (isset($_POST['finish'])) {
            $this->_redirect('testing_cms/overviewall');
        }
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $testCaseExp = new Yourdelivery_Model_Testing_TestCaseExpectation();
                $values = $form->getValues();
                $values['testCaseId'] = $id;
                $testCaseExp->setData($values);
                $testCaseExp->save();
                if ($form->imagePath->isUploaded()) {
                    $storage = new Default_File_Storage();
                    $storage->setStorage(APPLICATION_PATH . '/../storage/');
                    $storage->setSubFolder('testing/'.$id.'/');
                    $imageStorageName = 'testCase_' . $id . '_' . 'exp' . '_' . $testCaseExp->getId() . '_' . $values['imagePath'];
                    $storage->store($imageStorageName, file_get_contents($form->imagePath->getFileName()));
                    $testCaseExp->setImagePath($imageStorageName);
                    $testCaseExp->save();
                }
                $this->success('Expectation added.');
                if (isset($_POST['save'])) {
                    $this->_redirect('testing_cms/overview/id/' . $id);
                }
                if (isset($_POST['add'])) {
                    $this->_redirect('testing_cms/add/id/' . $id);
                } else {
                    $this->logger->info('CMS failed...');
                }
            } else {
                $this->error($form->getMessages());
                $this->_redirect('testing_cms/add/id/' . $id);
            }
        }
    }

    /**
     * response to one specific testcase can be written
     *
     * @author mpantar, afrank
     * @since 25-03-11
     */
    public function responseAction() {

        if ($this->_user == 'user') {
            $this->_redirect('/');
        }

        $form = new Yourdelivery_Form_Testing_Response();
        $testCaseId = (integer) $this->getRequest()->getParam('id');
        $testCaseExpId = (integer) $this->getRequest()->getParam('eid');
        try {
            $testCase = new Yourdelivery_Model_Testing_TestCase($testCaseId);
            $testCaseExpectation = new Yourdelivery_Model_Testing_TestCaseExpectation($testCaseExpId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Requested site does not exists.');
            $this->_redirect('/testing_cms/');
            return;
        }

        $this->view->title = $testCase->getTitle();
        $this->view->testCaseExp = $testCaseExpectation;
        $this->view->testCaseId = $testCaseId;
        $this->view->testCaseExpectationId = $testCaseExpId;
        $this->view->userName = $this->_user;
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $values = $form->getValues();
                $values['testCaseId'] = $testCaseId;
                $values['testCaseExpectationId'] = $testCaseExpId;
                $testCaseExecutor = new Yourdelivery_Model_Testing_TestCaseExecutor();
                $testCaseExecutorsResponse = new Yourdelivery_Model_Testing_TestCaseExecutorsResponse();
                $testCaseExecutor->setData($values);
                $values['testCaseExecutorId'] = $testCaseExecutor->save();
                if (strlen($values['response']) > 0) {
                    $values['additionalInfo'] = $_SERVER['HTTP_USER_AGENT'];
                    $email = new Yourdelivery_Sender_Email_Template('testing_cms_response');
                    $email->setSubject('Testing-CMS-Response');
                    $email->assign('link', sprintf('/testing_cms/responsestats/id/%s/eid/%s', $values['testCaseId'], $values['testCaseExpectationId']));
                    $email->assign('msg', $values['response']);
                    $email->assign('tester', $values['executor']);
                    $email->addTo('haferkorn@lieferando.de');
                    $email->addTo('frank@lieferando.de');
                    $email->send();
                }
                $testCaseExecutorsResponse->setData($values);
                $testCaseExecutorsResponse->save();
                $nextId = $testCase->getNextId($testCaseExpId);
                $this->success('Saved response.');



                if (is_null($nextId)) {
                    $this->_redirect(sprintf('testing_cms/overviewexecutor/id/%d', $testCaseId));
                }
                $this->_redirect(sprintf('testing_cms/response/id/%d/eid/%d', $testCaseId, $nextId['id']));
            } else {
                $this->error($form->getMessages());
                $this->_redirect(sprintf('testing_cms/response/id/%d/eid/%d', $testCaseId, $testCaseExpId));
            }
        }
    }

    /**
     * delete one testcase
     * @author mpantar
     * @since 25-03-11
     */
    public function deletetestcaseAction() {

        $this->validateUser();

        $id = $this->getRequest()->getParam('id', null);
        if (!is_null($id)) {
            $testcase = null;
            try {
                $testcase = new Yourdelivery_Model_Testing_TestCase($id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
                $this->error('Requested site does not exists.');
                $this->_redirect('/testing_cms/');
                return;
            }
            $testcase->delete();

            //Delete all Expectations for a testcase
            $exp = $testcase->getExpectations();
            foreach ($exp as $ex) {
                $idExp = $ex->getId();
                try {
                    $model = new Yourdelivery_Model_Testing_TestCaseExpectation($idExp);
                    $model->delete();
                } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
                    $this->error('Requested site does not exists.');
                    $this->_redirect('/testing_cms/');
                    return;
                }
            }
            $this->success('Erfolgreich gelöscht');
            $this->_redirect('testing_cms/overviewall');
        } else {
            $this->error('No id given');
            $this->logger->warn('No id given in deletetestcaseAction()');
            return;
        }
    }

    /**
     * delete one expectation     
     * @author mpantar
     * @since 25-03-11
     */
    public function deleteexpectationAction() {

        $this->validateUser();

        $id = $this->getRequest()->getParam('id', null);
        if (!is_null($id)) {
            $testcaseExp = null;
            try {
                $testcaseExp = new Yourdelivery_Model_Testing_TestCaseExpectation($id);
            } catch (Exception $e) {
                $this->error('Requested site does not exists.');
                $this->_redirect('/testing_cms/');
                return;
            }
            $testcaseExp->delete();
            $this->success('Erfolgreich gelöscht');
            $this->_redirect(sprintf('testing_cms/overview/id/%d', $testcaseExp->getTestCaseId()));
        } else {
            $this->error('No id given');
            $this->logger->warn('No id given in deleteexpectationAction()');
            return;
        }
    }

    /**
     * overview to all testcases
     * @author mpantar
     * @since 29-03-11
     */
    public function overviewallAction() {

        $this->validateUser();

        $testCase = new Yourdelivery_Model_Testing_TestCase();
        $this->view->testcases = $testCase->getTable()->fetchAll()->toArray();
        $this->view->exp = $testCase->getExpectations();
    }

    public function executorsoverviewAction() {

        if ($this->_user == 'user') {
            $this->_redirect('/');
        }

        $testCase = new Yourdelivery_Model_Testing_TestCase();
        $arr_2 = $testCase->getAllInfos();
        $arr = $testCase->getTable()->fetchAll()->toArray();
        $this->aasort($arr, "priority");
        $tmp = array_merge($arr_2, $arr);
        $this->view->testcases = $tmp;

        $this->view->userName = $this->_user;
    }

    /**
     * overview to one specific testcase wit functions edit and delete
     *
     * @author mpantar, afrank
     * @since 29-03-11
     */
    public function overviewAction() {

        if ($this->_user == 'user') {
            $this->_redirect('/');
        }

        $testCaseId = (integer) $this->getRequest()->getParam('id');
        if ($testCaseId <= 0) {
            $this->_redirect('testing_cms/overviewall');
        }
        try {
            $testCase = new Yourdelivery_Model_Testing_TestCase($testCaseId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Requested site does not exists.');
            $this->_redirect('/testing_cms/');
            return;
        }
        $this->view->testCase = $testCase;
        $this->view->testCaseId = $testCaseId;

        $this->view->assign('missions', $testCase->getExpectations());
    }

    /**
     * overview to one specific testcase for a executor...no delete or edit functions
     *
     * @author mpantar
     * @since 30-03-11
     */
    public function overviewexecutorAction() {

        if ($this->_user == 'user') {
            $this->_redirect('/');
        }

        $testCaseId = (integer) $this->getRequest()->getParam('id');
        try {
            $testCase = new Yourdelivery_Model_Testing_TestCase($testCaseId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Requested site does not exists.');
            $this->_redirect('/testing_cms/executorsoverview');
            return;
        }

        $this->view->testCase = $testCase;

        $this->view->assign('missions', $testCase->getExpectations());
    }

    /**
     * displays the selected image with a back-button
     * @author Allen Frank <frank@lieferando.de>
     * @since 06-04-11
     */
    public function imageAction() {

        if ($this->_user == 'user') {
            $this->_redirect('/');
        }

        $testCaseExpId = (integer) $this->getRequest()->getParam('eid');
        try {
            $testCaseExpectation = new Yourdelivery_Model_Testing_TestCaseExpectation($testCaseExpId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Requested site does not exists.');
            $this->_redirect('/testing_cms/');
            return;
        }
        $this->view->testCaseExp = $testCaseExpectation;
    }

    public function statsAction() {

        $this->validateUser();

        $threshold = $this->getRequest()->getParam('threshold');
        if (!isset($threshold) or is_null($threshold) or strlen($threshold) <= 0) {
            $threshold = '0.75';
        }
        $this->view->threshold = $threshold;
        // make the account tab active
        $this->view->assign('account', 'active');

        // get select
        $db = Zend_Registry::get('dbAdapter');
        // statistics
        $query = $db->fetchAll(
                        'select e.testCaseId id, r.testCaseExpectationId eid, count(*) count, sum(r.responseCheckbox) responses, sum(r.responseCheckbox)/count(*) avg
                            from test_case_executors_response r
                            join test_case_expectation e on r.testCaseExpectationId=e.id
                             group by r.testCaseExpectationId having avg<?', $threshold);

        $links = array();
        $sizeOfQuery = (int) sizeof($query);
        if ($sizeOfQuery >= 19) {
            $sizeOfQuery = 19;
        }

        // print data
        echo
        '<graph
            caption="CMS-Fails"
            showvalues="1"
            decimalPrecision="2"
            decimalSeparator=","
            formatNumberScale="0">
            <categories>
                ';

        for ($i = 0; $i < $sizeOfQuery; $i++) {
            echo sprintf('%s<category name="%s-%s(%s)"/>', "\n\t\t", $query[$i]['id'], $query[$i]['eid'], $query[$i]['count']);
            $links[] = sprintf('<a href="/testing_cms/responsestats/id/%s/eid/%s" target="_blank">%s</a>', $query[$i]['id'], $query[$i]['eid'], $query[$i]['id'] . '-' . $query[$i]['eid']);
        }

        echo "</categories>\n\t\t<dataset  color='f5d18c'>\n\t\t\t";
        foreach ($query as $value) {
            echo
            sprintf('<set value="%s"/>', $value["avg"]);
        }

        echo "\n\t\t</dataset>\n</graph>";

        $this->view->width = $sizeOfQuery * 100;
        $this->view->links = $links;
    }

    public function responsestatsAction() {

        $this->validateUser();

        $testCaseId = (integer) $this->getRequest()->getParam('id');
        $testCaseExpId = (integer) $this->getRequest()->getParam('eid');
        try {
            $testCase = new Yourdelivery_Model_Testing_TestCase($testCaseId);
            $testCaseExpectation = new Yourdelivery_Model_Testing_TestCaseExpectation($testCaseExpId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Requested site does not exists.');
            $this->_redirect('/testing_cms/');
            return;
        }

        $this->view->title = $testCase->getTitle();
        $this->view->testCaseExp = $testCaseExpectation;
        $this->view->testCaseId = $testCaseId;


        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()
                ->from(array('tcer' => 'test_case_executors_response'), array(
                    'executor' => 'tce.executor',
                    'response' => 'tcer.response',
                    'info' => 'tcer.additionalInfo',
                    'Datum' => new Zend_Db_Expr("DATE_FORMAT(tce.created, '" . __("%d.%m.%Y %H:%i") . "')"),
                ))
                ->join(array('tce' => 'test_case_executor'), 'tce.id = tcer.testCaseExecutorId', array())
                ->where('length(tcer.response)>0')
                ->where('tcer.testCaseExpectationId=' . $testCaseExpId)
                ->where('tce.testCaseId=' . $testCaseId)
        ;

        // get grid
        $response = Default_Helper::getTableGrid();
        $response->export = array();
        $response->setSource(new Bvb_Grid_Source_Zend_Select($select));

        // add filter
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('executor')
                ->addFilter('response')
                ->addFilter('info')
                ->addFilter('Datum');
        $response->addFilters($filters);

        $this->view->testCaseExpectationId = $testCaseExpId;
        // deploy grid to view
        $this->view->responses = $response->deploy();
    }

    /**
     * @todo implement logic
     */
    public function searchAction() {
        $this->_redirect('/testing_cms/');
    }

    /**
     * Sends a email with information to us
     */
    public function infoAction() {
        $form = new Yourdelivery_Form_Testing_Specialresponse();
        if ($form->isValid($this->getRequest()->getPost())) {
            $email = new Yourdelivery_Sender_Email_Template('testing_cms_response');
            $email->setSubject('Testing-CMS-Special-Response');
            $email->assign('msg', $form->getValue('response'));
            $email->assign('tester', $form->getValue('executor'));
            $email->addTo('haferkorn@lieferando.de');
            $email->addTo('frank@lieferando.de');
            if ($form->imagePath->isUploaded()) {
                $storage = new Default_File_Storage();
                $storage->setStorage(APPLICATION_PATH . '/../storage/');
                $storage->setSubFolder('testing/tmp/');
                $imageStorageName = time() . '_' . $form->getValue('imagePath');
                $storage->store($imageStorageName, file_get_contents($form->imagePath->getFileName()));
                $email->attachFile(APPLICATION_PATH . '/../storage/testing/tmp/' . $imageStorageName, "image/png", $form->getValue('imagePath'));
                unlink(APPLICATION_PATH . '/../storage/testing/tmp/' . $imageStorageName);
            }

            if ($email->send()) {
                $this->success('Email has been send.');
            } else {
                $this->error('Email failed.');
            }
        } else {
            $this->error($form->getMessages());
        }
        $this->_redirect('/testing_cms/');
    }

}

?>
