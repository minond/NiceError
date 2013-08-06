<?php

namespace util\error;

require_once 'src/util/error/ErrorOutput.php';
require_once 'src/util/error/FancyTemplateOutput.php';
require_once 'src/util/error/NiceError.php';

$niceerror = new NiceError(
    new FancyTemplateOutput(
        __DIR__ . '/resources/nice_error.phtml'));

ini_set('display_errors', false);
set_error_handler([ $niceerror, 'handleError' ]);
set_exception_handler([ $niceerror, 'handleException' ]);
register_shutdown_function([ $niceerror, 'handleShutdown' ]);

return $niceerror;

