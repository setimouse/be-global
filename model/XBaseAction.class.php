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

    protected function displayTemplate($template) {
        $template = PRJ.'template/'.$template;
        return $this->display($template);
    }

    protected function display($template) {
        if (MDict::D('is_debug')) {
            $this->displayDebug();
            return;
        }
        return parent::display($template);
    }

    protected function displayJson(MJsonRespond $jsonRespond) {
        $this->addHeader('Content-Type', 'application/json');
        $this->assign('jsonRespond', $jsonRespond);
        $this->display(dirname(__FILE__).'/../template/jsonrespond.tpl.php');
    }

    protected function displayJsonSuccess($data = null) {
        $jsonRespond = MJsonRespond::respondSuccess($data);
        $this->displayJson($jsonRespond);
    }

    protected function displayJsonFail($data = null) {
        $jsonRespond = MJsonRespond::respondFail($data);
        $this->displayJson($jsonRespond);
    }

    protected function displayDebug() {
        printa($this->tplData);
    }

}

