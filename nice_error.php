<?php

namespace util\error;

define('NICE_ERROR_BASE_DIR', getenv('NICE_ERROR_BASE_DIR') ?: __DIR__);
$config = parse_ini_file('config/nice_error.ini', true);

if (!class_exists('NiceError')) {
    require 'src/util/error/NiceError.php';
}

if ($domainconfig = getenv('NICE_ERROR_CONFIG')) {
    $config = array_replace_recursive(
        $config,
        parse_ini_file($domainconfig, true)
    );
}

if ($config['NiceError']['enabled']) {
    $niceerror = new NiceError($config);

    ini_set('display_errors', false);
    set_error_handler([ $niceerror, 'handleerror' ]);
    set_exception_handler([ $niceerror, 'handleexception' ]);
    register_shutdown_function([ $niceerror, 'handleshutdown' ]);
}

