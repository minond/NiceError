<?php

namespace util\error;

require_once 'src/util/Error.php';
require_once 'src/util/error/ErrorOutput.php';
require_once 'src/util/error/FancyTemplateOutput.php';
require_once 'src/util/error/NiceError.php';

$niceerror = false;

// TODO: handle cli and json requests
if (php_sapi_name() !== 'cli') {
    $headers = getallheaders();
    $accepts = isset($headers['Accept']) ? $headers['Accept'] : '';

    if (strpos($accepts, 'text/html') !== false) {
        $niceerror = new NiceError(new FancyTemplateOutput);
    }
}

if ($niceerror) {
    ini_set('display_errors', false);
    set_error_handler([ $niceerror, 'handleError' ]);
    set_exception_handler([ $niceerror, 'handleException' ]);
    register_shutdown_function([ $niceerror, 'handleShutdown' ]);
}

return $niceerror;

