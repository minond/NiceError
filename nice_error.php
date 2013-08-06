<?php

namespace util\error;

if (!class_exists('NiceError')) {
    require 'src/util/error/NiceError.php';
}

$niceerror = new NiceError(__DIR__);

ini_set('display_errors', false);
set_error_handler([ $niceerror, 'handleError' ]);
set_exception_handler([ $niceerror, 'handleException' ]);
register_shutdown_function([ $niceerror, 'handleShutdown' ]);

return $niceerror;

