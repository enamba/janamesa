<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          here you get global settings for application
 *      </li>
 *      <li>
 *          you get important payment enabled- / disabled- information
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          index - get global settings for application
 *      </li>
 *      <li>
 *          delete - disallowed - 403
 *      </li>
 *      <li>
 *          get - disallowed - 403
 *      </li>
 *      <li>
 *          post - disallowed - 403
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 11.01.2012
 *
 * @modified , 08.01.2012
 */
class Get_SettingsController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get information about global settings</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      none
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <settings>
     *              <payment>                               (global settings for payments that are enabled for API)
     *                  <bar>BOOLEAN</bar>                  (bar payment allowed?)
     *                  <credit>BOOLEAN</credit>            (credit payment allowed?)
     *                  <paypal>BOOLEAN</paypal>            (paypal payment allowed?)
     *                  <ebanking>BOOLEAN</ebanking>        (ebanking payment allowed?)
     *              </payment>
     *              <fidelity>                              (settingsfor fidelity system)
     *                  <enabled>INTEGER</enabled>          (fidelity system global enabled or disabled)
     *                  <cashinneed>INTEGER</cashinneed>    (amount of points needed to cash-in fidelity-points)
     *                  <cashinlimit>INTEGER</cashinlimit>  (amount fo cents for which customer can cash-in fidelity points)
     *              </fidelity>
     *              <content>                               (some dynamic content what we will provide in runtime)
     *                  <sitenotice>LONGTEXT</sitenotice>   (text for site notice of iPhoneApp - presented in HTML)
     *                  <dynamicStartUpHTML>
     *                      LONGTEXT                        (dynamic content to show on top of "Suchen" Screen in iPhoneApp V2 - presented in HTML)
     *                  </dynamicStartUpHTML>
     *                  <dynamicFaqHTML>
     *                      LONGTEXT                        (dynamic html for faq)
     *                  </dynamicFaqHTML>
     *                  <dynamicManualHTML>
     *                      LONGTEXT                        (dynamic html for manual / Anleitung)
     *                  </dynamicManualHTML>
     *                  <dynamicNewsHTML>
     *                      LONGTEXT                        (dynamic html for news section)
     *                  </dynamicNewsHTML>
     *              </content>
     *          </settings>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              http://www.lieferando.de/get_settings
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <respons>
     *          <version>1.0</version>
     *          <settings>
     *              <payment>
     *                  <paypal>1</paypal>
     *                  <credit>1</credit>
     *                  <ebanking>1</ebanking>
     *                  <bar>1</bar>
     *              </payment>
     *              <fidelity>
     *                  <enabled>1</enabled>
     *                  <cashinneed>100</cashinneed>
     *                  <cashinlimit>800</cashinlimit>
     *              </fidelity>
     *              <content>
     *                  <sitenotice>
     *                      Pizza, Pasta, Sushi und vieles mehr! Bei der Online-Bestellplatfform lieferando.de aus über 4000 Lieferdiensten in ganz Deutschland
     *                      wählen und Lieblingsessen bequem nach Hause liefern lassen. <br /><br /> Neben der Barzahlung kann auch bargeldlos bezahlt werden. Außerdem mit
     *                      jeder Bestellung Treuepunkte sammeln und ab 100 Punkten ein Essen kostenlos bekommen <br /><br />
     *                      yd. yourdelivery GmbH <br />
     *                      Chausseestraße 86 <br />
     *                      D-10115 Berlin <br />
     *                      Tel.: 030 288 85 67 0 <br />
     *                      Fax: 0800 202 07 702 <br />
     *                      <a href="mailto:info@lieferando.de">info@lieferando.de</a>
     *                  </sitenotice>
     *                  <dynamicStartUpHTML>
     *                      <img src="http://image.yourdelivery.de/logo/lieferando.de-400-0.jpg" />
     *                      <p>Here is some dynamic content</p>
     *                  </dynamicStartUpHTML>
     *                  <dynamicFaqHTML>
     *                      <p>here you will get some dynamic FAQ / Hilfe content</p>
     *                  </dynamicFaqHTML>
     *                  <dynamicManualHTML>
     *                      <p>here you will get some dynamic content for manual / Anleitung</p>
     *                  </dynamicManualHTML>
     *                  <dynamicNewsHTML>
     *                      <p>here you will get some dynamic content for news section</p>
     *                  </dynamicNewsHTML>
     *              </content>
     *          </settings>
     *          <success>true</success>
     *          <message></message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>16</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - success</li>
     *  </ul>
     *
     *
     * -------------------------------------------------------------------------
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 11.01.2012
     *
     * @return HTTP-RESONSE-CODE
     */
    public function indexAction() {
        $config = Zend_Registry::get('configuration');
        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        $settings = $this->doc->createElement('settings');
        $payment = $this->doc->createElement('payment');
        $payment->appendChild(create_node($this->doc, 'paypal', (integer) (boolean) $config->payment->paypal->enabled));
        $payment->appendChild(create_node($this->doc, 'credit', (integer) (boolean) $config->payment->credit->enabled));
        $payment->appendChild(create_node($this->doc, 'ebanking', (integer) (boolean) $config->payment->ebanking->enabled));
        $payment->appendChild(create_node($this->doc, 'bar', (integer) (boolean) $config->payment->bar->enabled));
        $settings->appendChild($payment);
        $fidelity = $this->doc->createElement('fidelity');
        $fidelity->appendChild(create_node($this->doc, 'enabled', (integer) (boolean) $fidelityConfig->fidelity->enabled));
        $fidelity->appendChild(create_node($this->doc, 'cashinneed', (integer) $fidelityConfig->fidelity->cashin->need));
        $fidelity->appendChild(create_node($this->doc, 'cashinlimit', (integer) $fidelityConfig->fidelity->cashin->maxcost));
        $settings->appendChild($fidelity);

        // some dynamic content we spread in the world
        $content = $this->doc->createElement('content');
        
        $content->appendChild(create_node($this->doc, 'sitenotice', $this->getDynamicContent('sitenotice')));
        $content->appendChild(create_node($this->doc, 'dynamicStartUpHTML', $this->getDynamicContent('start')));
        $content->appendChild(create_node($this->doc, 'dynamicFaqHTML', $this->getDynamicContent('faq')));
        $content->appendChild(create_node($this->doc, 'dynamicManualHTML', $this->getDynamicContent('manual')));
        $content->appendChild(create_node($this->doc, 'dynamicNewsHTML', $this->getDynamicContent('news')));
        
        $settings->appendChild($content);
        $this->xml->appendChild($settings);
    }

    /**
     * the get method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function getAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the post method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function postAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * fetch template and get dynamic content from html
     *
     * @param string $view
     * @return string
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.02.2012
     */
    private function getDynamicContent($view) {
        try {
            $this->view->config = $config = Zend_Registry::get('configuration');
            $this->view->setLayout('iphone/'.$config->domain->base, true);
            $this->view->fidelityConfig = $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
            return $this->view->fetch($view . '.htm');
        } catch (Exception $e) {
            return '';
        }
    }

}
