<?php
/**
 * trace delegate
 *
 * Filename: LibSAETrace.class.php
 *
 * @author liyan
 * @since 2014 7 28
 */
class LibSAETrace implements ITraceDelegate {

    public function write($msg, $level, $file = '', $line = '') {
        // sae_debug($msg);
    }

}

if (!function_exists('sae_debug')) {
    function sae_debug($msg) {
        printbr($msg);
        //  nothing
    }
}