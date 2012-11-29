<?php

class Yourdelivery_Log extends Zend_Log {

    /**
     * @todo: extend for restaurant backend as well
     * @author mlaug
     * @param  string  $method  priority name
     * @param  string  $params  message to log
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __call($method, $params) {
        $priority = strtoupper($method);
        $session_admin = new Zend_Session_Namespace('Administration');
        $session_restaurant = new Zend_Session_Namespace('Restaurant');

        //extend the logging message for admins
        if (( strstr($method, 'admin') && is_object($session_admin) && is_object($session_admin->admin) ) ||
                (strstr($method, 'admin') && is_object($session_restaurant) && (is_object($session_restaurant->admin) || is_object($session_restaurant->masterAdmin)) )) {
            $method = strtolower(substr($method, 5));
            $priority = array_search(strtoupper($method), $this->_priorities);
            if ($priority === false) {
                return;
            }
            $message = array_shift($params);
            $extras = null;
            if (count($params) > 1) {
                $extras = array_shift($params);
            }

            if (is_object($session_admin->admin)) {
                $admin = &$session_admin->admin;
                $message .= sprintf(" #Supporter %s (%d)", $admin->getName(), $admin->getId());
            } elseif (is_object($session_restaurant)) {
                $masterAdmin = $session_restaurant->masterAdmin;
                $admin = $session_restaurant->admin;

                if (is_object($masterAdmin)) {
                    $message .= sprintf(" #R# Supporter %s (%d) from admin backend", $masterAdmin->getName(), $masterAdmin->getId());
                } elseif (is_object($admin)) {
                    $message .= sprintf(" #R# Supporter %s %s (%d)", $admin->getPrename(), $admin->getName(), $admin->getId());
                }
            }

            try {
                $this->log($message, $priority, $extras);
            } catch (Zend_Log_Exception $e) {
                // do nothing
            }
        } elseif (($priority = array_search($priority, $this->_priorities)) !== false) {

            switch (count($params)) {
                case 0:
                    //throw no error here, why should...
                    break;
                case 1:
                    $message = array_shift($params);
                    $extras = null;
                    break;
                default:
                    $message = array_shift($params);
                    $extras = array_shift($params);
                    break;
            }

            try {
                $this->log($message, $priority, $extras);
            } catch (Zend_Log_Exception $e) {
                // do nothing
            }
        } else {
            //throw no error here, why should...
        }
    }

    /**
     * @author mlaug
     * @since 05.01.2012
     * @param string $message
     * @param string $priority
     * @param string $extras 
     */
    public function log($message, $priority, $extras = null) {

        $config = Zend_Registry::get('configuration');
        $nodename = Zend_Registry::get('node');

        $prepend = $nodename . ' serving ' . $config->domain->base;
        if (!IS_PRODUCTION) {
            $prepend .= '.testing';
        }

        $append = '';
        if ($priority == Zend_Log::CRIT || $priority == Zend_Log::ERR) {
            foreach (debug_backtrace() as $k => $v) {
                if ($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require") {
                    $append .= "#" . $k . " " . $v['function'] . "(" . $v['args'][0] . ") called at [" . $v['file'] . ":" . $v['line'] . "]\n";
                } else {
                    $append .= "#" . $k . " " . $v['function'] . "() called at [" . $v['file'] . ":" . $v['line'] . "]\n";
                }
            }
        }

        $data = parent::log($prepend . ' ' . $message . ' ' . $append, $priority, $extras);
    }

}