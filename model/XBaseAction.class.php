<?php
/**
 * description
 *
 * Filename: XBaseAction.class.php
 *
 * @author liyan
 * @since 2014 8 19
 */
abstract class XBaseAction extends BaseAction {

    protected $arrHeader;

    function __construct() {
        parent::__construct();
        $this->arrHeader = array();
    }

    protected function displayTemplate($template) {
        $template = PRJ.'template/'.$template;
        return $this->display($template);
    }

    protected function display($template) {
        if (MDict::D('is_debug')) {
            $this->displayDebug();
            return;
        }

        foreach ($this->arrHeader as $key => $value) {
            header($key.":".$value);
        }

        return parent::display($template);
    }

    protected function displayJson(MJsonRespond $jsonRespond) {
        $this->addHeader('Content-Type', 'application/json');
        $this->assign('jsonRespond', $jsonRespond);
        $this->display(dirname(__FILE__).'/../template/jsonrespond.tpl.php');
    }

    protected function displayJsonSuccess($data = null) {
        $jsonRespond = MJsonRespond::respondSuccess('success', $data);
        $this->displayJson($jsonRespond);
    }

    protected function displayJsonFail($data = null) {
        $jsonRespond = MJsonRespond::respondFail('fail', $data);
        $this->displayJson($jsonRespond);
    }

    protected function displayDebug() {
        printa($this->tplData);
    }

    protected function addHeader($key, $value) {
        DAssert::assert(is_string($key) && is_string($value), 'header must be string',
            __FILE__, __LINE__);
        $this->arrHeader[$key] = $value;
    }

    protected function setExpire($timestamp) {
        $gmtime = date("r", $timestamp);
        $this->addHeader('Expires', $gmtime);
    }

}

