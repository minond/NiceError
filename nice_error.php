<?php

namespace util\error\autoload;

use util\error\NiceError;
require 'src/util/error/NiceError.php';

$config = parse_ini_file('config/nice_error.ini', true);
$config['base_dir'] = __DIR__;
$niceerror = new NiceError($config);

error_reporting($config['error']['report']);
ini_set('display_errors', 'off');

set_error_handler([ $niceerror, 'handleerror' ]);
set_exception_handler([ $niceerror, 'handleexception' ]);
register_shutdown_function([ $niceerror, 'handleshutdown' ]);

