<?php
/**
 * Action基类
 *
 * @author liyan
 * @version 2014 7 17
 */
abstract class JsonBaseAction extends BaseAction {

    /**
     * 渲染json模板
     *
     */
    public function displayJson(MJsonRespond $jsonRespond) {
        $this->addHeader('Content-Type', 'application/json');
        $this->assign('jsonRespond', $jsonRespond);
        $this->display(dirname(__FILE__).'/../template/jsonrespond.tpl.php');
    }

}
