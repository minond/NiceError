<?php

namespace util\error;

require_once 'src/util/error/Error.php';
require_once 'src/util/error/NiceError.php';
require_once 'src/util/error/Output.php';
require_once 'src/util/error/output/FancyTemplateOutput.php';
require_once 'src/util/error/output/PrettyTextOutput.php';

if (enabled()) {
    $handler = new NiceError;

    // browser output
    if (php_sapi_name() !== 'cli' && acceptsHtml()) {
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

/**
 * can we send html output?
 * @return boolean
 */
function acceptsHtml()
{
    return isset($_SERVER['HTTP_ACCEPT']) &&
        strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false;
}

/**
 * is NiceError enabled?
 * @return boolean
 */
function enabled()
{
    // xxx: why do I need to check $_ENV and getenv????
    return (isset($_ENV['NICE_ERRORS']) && $_ENV['NICE_ERRORS']) ||
        getenv('NICE_ERRORS');
}

