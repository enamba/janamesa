<?php

/**
 * @author alex
 * @sinces 16.05.2011
 */
class Administration_Stats_AccesslogController extends Default_Controller_AdministrationBase {

    /**
     * @author alex
     * @sinces 16.05.2011
     */
    public function indexAction() {
        set_time_limit(0);

        $fromDate = date('Ymd', time());
        $untilDate = date('Ymd', time());
        $searched = "";
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            $fromDate = substr($post['fromD'], 6, 4) . substr($post['fromD'], 3, 2) . substr($post['fromD'], 0, 2);
            $untilDate = substr($post['untilD'], 6, 4) . substr($post['untilD'], 3, 2) . substr($post['untilD'], 0, 2);
            $searched = $post['searched'];

            $fromInt = intval($fromDate);
            $untilInt = intval($untilDate);

            $dataArr = array();
            for ($i = 0; $i <= 23; $i++) {
                $dataArr[$i] = array();
                for ($j = 1; $j <= 7; $j++) {
                    $dataArr[$i][$j] = 0;
                }
            }       

            $daysCount = array();
            for ($i = 1; $i <= 7; $i++) {
                $daysCount[$i] = 0;
            }
            
            $config = Zend_Registry::get('configuration');
            $logDirectory = $config->log->directory;

            for ($ind = $fromInt; $ind <= $untilInt; $ind++) {
                $fh = fopen($logDirectory . "access_log." . $ind, "r");

                if (!$fh) {
                    continue;
                }
                                                
                while (!feof($fh)) {
                   $line = fgets($fh);
                   if (strpos($line, "GET /" . $searched . " ") !== false) {
                        $datepart = current(explode(" ", substr($line, strpos($line, "[")+1)));

                        $wd = intval(date('N', strtotime(str_replace("/", " ", substr($datepart, 0, strpos($datepart, ":"))))));
                        $hour = intval(substr($datepart, strpos($datepart, ":") + 1, 2));

                        $dataArr[$hour][$wd]++;
                    }
                }
                fclose($fh);
            }
        }
        
        $this->view->assign('from', date("d.m.Y", strtotime($fromDate)));
        $this->view->assign('until', date("d.m.Y", strtotime($untilDate)));
        $this->view->assign('searched', $searched);
        $this->view->assign('data', $dataArr);
    }
}
