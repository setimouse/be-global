<?php
/**
 * description
 *
 * Filename: SmartyBaseAction.class.php
 *
 * @author liyan
 * @since 2014 9 3
 */

require_once GUTIL.'smarty/Smarty.class.php';
require_once GUTIL.'smarty/SmartyBC.class.php';

abstract class SmartyBaseAction extends XBaseAction {

    protected static $smarty;

    function __construct() {
        parent::__construct();
        $this->smarty = new Smarty();
    }

    protected static function getView() {
        if (!self::$smarty) {
            self::$smarty = new Smarty();
        }
        return self::$smarty;
    }

    protected function assign($key, $value) {
        self::getView()->assign($key, $value);
    }

    /**
     * 渲染模板
     *
     * @param string $template
     */
    protected function display($template) {
        $template = TEMPLATE.$template;
        foreach ($this->arrHeader as $key => $value) {
            header($key.":".$value);
        }

        self::getView()->display($template);
    }

}

