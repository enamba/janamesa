<?php
/**
 * SEO SEM controller
 * @author vpriem
 * @since 17.09.2010
 */
class Administration_Seo_SemController extends Default_Controller_AdministrationBase {

    /**
     * Init
     * @author vpriem
     * @since 17.09.2010
     */
    public function init(){
        parent::init();

        if (!is_dir(APPLICATION_PATH . '/../public/sem')) {
            mkdir(APPLICATION_PATH . '/../public/sem');
        }

        $storage = new Default_File_Storage();
        $storage->setSubFolder('sem/images/backgrounds');
        $storage->setSubFolder('../foregrounds');
        $storage->setSubFolder('../buttons');

    }

    /**
     * Index
     * @author vpriem
     * @since 17.09.2010
     */
    public function indexAction(){

        // assign pages
        $pages = @scandir(APPLICATION_PATH . '/../public/sem');
        if ($pages) {
            foreach ($pages as $key => $page) {
                if ($page == "." || $page == "..") {
                    unset($pages[$key]);
                }
            }
            $this->view->assign('pages', $pages);
        }

    }

    /**
     * Create sem page
     * @author vpriem
     * @since 17.09.2010
     */
    public function createAction(){

        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            // cancel
            if (isset($post['cancel'])) {
                $this->_redirect('/administration_seo_sem');
            }

            // form
            $form = new Yourdelivery_Form_Administration_Seo_Sem_Create();
            if ($form->isValid($post)) {
                // set values
                $values = $form->getValues();

                // upload
                if ($form->_background->isUploaded()) {
                    $values['background'] = $form->_background->getFileName(null, false);
                }
                if ($form->_foreground->isUploaded()) {
                    $values['foreground'] = $form->_foreground->getFileName(null, false);
                }
                if ($form->_button->isUploaded()) {
                    $values['button'] = $form->_button->getFileName(null, false);
                }

                // save
                $config = $this->config;
                $smarty = new Smarty();
                $smarty->template_dir = $config->smarty->template_dir . "/default";
                $smarty->compile_dir  = $config->smarty->compile_dir . "/default";
                $smarty->config_dir   = $config->smarty->config_dir;
                $smarty->cache_dir    = $config->smarty->cache_dir;
                $smarty->caching      = false;
                $smarty->assign('APPLICATION_ENV', APPLICATION_ENV);
                $smarty->assign('page', $values);
                $html = $smarty->fetch("administration/seo/sem/skeleton.htm");
                
                // write
                $filename = APPLICATION_PATH . "/../public/sem/" . $values['url'];
                $dirname = dirname($filename);
                if (!file_exists($dirname)) {
                    mkdir($dirname, 0777, true);
                }
                file_put_contents($filename, $html);

                // redirect
                $this->_redirect('/administration_seo_sem');

            // error
            } else {
                $this->error($form->getMessages());
            }

        }

        // assign backgrounds
        $backgrounds = @scandir(APPLICATION_PATH . '/../storage/sem/images/backgrounds');
        if ($backgrounds) {
            foreach ($backgrounds as $key => $background) {
                if (!in_array(pathinfo($background, PATHINFO_EXTENSION), array("jpg", "gif", "png"))) {
                    unset($backgrounds[$key]);
                }
            }
            $this->view->assign('backgrounds', $backgrounds);
        }

        // assign foregrounds
        $foregrounds = @scandir(APPLICATION_PATH . '/../storage/sem/images/foregrounds');
        if ($foregrounds) {
            foreach ($foregrounds as $key => $foreground) {
                if (!in_array(pathinfo($foreground, PATHINFO_EXTENSION), array("jpg", "gif", "png"))) {
                    unset($foregrounds[$key]);
                }
            }
            $this->view->assign('foregrounds', $foregrounds);
        }

        // assign buttons images
        $buttons = @scandir(APPLICATION_PATH . '/../storage/sem/images/buttons');
        if ($buttons) {
            foreach ($buttons as $key => $button) {
                if (!in_array(pathinfo($button, PATHINFO_EXTENSION), array("jpg", "gif", "png"))) {
                    unset($buttons[$key]);
                }
            }
            $this->view->assign('buttons', $buttons);
        }

    }

}
