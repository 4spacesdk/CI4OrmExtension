<?php

use Config\Services;

if(!function_exists('_log')) {

    function _log($msg) {
        Services::logger(true)->log('notice', $msg);
    }

};
