<?php

namespace util\error;

require_once 'src/util/error/Helper.php';
require_once 'src/util/error/Error.php';
require_once 'src/util/error/NiceError.php';
require_once 'src/util/error/Output.php';
require_once 'src/util/error/output/FancyTemplateOutput.php';
require_once 'src/util/error/output/PrettyTextOutput.php';

if (Helper::enabled()) {
    $handler = new NiceError;

    // browser output
    if (php_sapi_name() !== 'cli' && Helper::acceptsHtml()) {
        $handler->addOutput(new output\FancyTemplateOutput);
    }

    // php's server cli output
    if (php_sapi_name() === 'cli-server' || php_sapi_name() === 'cli') {
        $handler->addOutput(new output\PrettyTextOutput);
    }

    // binding
    set_error_handler([ $handler, 'handleError' ]);
    set_exception_handler([ $handler, 'handleException' ]);
    register_shutdown_function([ $handler, 'handleShutdown' ]);

    // disable all other error outputs
    ini_set('display_errors', false);
    ini_set('error_log', '/dev/null');
}

