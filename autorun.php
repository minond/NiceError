<?php

namespace util\error;

require_once 'src/util/error/Error.php';
require_once 'src/util/error/NiceError.php';
require_once 'src/util/error/Output.php';
require_once 'src/util/error/output/FancyTemplateOutput.php';

$niceerror = false;

// xxx: why do I need to check $_ENV and getenv????
if ((isset($_ENV['NICE_ERRORS']) && $_ENV['NICE_ERRORS']) || getenv('NICE_ERRORS')) {
    // todo: handle cli and json requests
    if (php_sapi_name() !== 'cli' && isset($_SERVER['HTTP_ACCEPT'])) {
        if (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false) {
            $niceerror = new NiceError(new output\FancyTemplateOutput);
        }
    }

    if ($niceerror) {
        ini_set('display_errors', false);
        set_error_handler([ $niceerror, 'handleError' ]);
        set_exception_handler([ $niceerror, 'handleException' ]);
        register_shutdown_function([ $niceerror, 'handleShutdown' ]);
    }
}

return $niceerror;

