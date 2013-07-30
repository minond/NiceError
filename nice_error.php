<?php

namespace util\error;

/**
 * source contents getter
 * @param string file
 * @param int $line
 * @param int $offset - optional, default = 10
 * @param int $show - optional, default = true
 * @return string
 */
function getsource($file, $line, $offset = 10, $show = true)
{
    $source = [];
    $lines = null;

    if ($show) {
        $lines = explode(PHP_EOL, file_get_contents($file));
        $i = $line - $offset;
        $max = $line + $offset + 1;

        for (; $i < $max; $i++)
            if (isset($lines[ $i - 1 ]))
                $source[] = [
                    'text' => $lines[ $i - 1 ],
                    'num' => $i,
                ];
    }

    return $source;
}

/**
 * render the error page
 * @param array $args
 */
function renderview(array $args)
{
    $config = config();
    $display_backtrace = $config['backtrace']['display'];
    $display_source = $config['source']['display'];
    $line_offset = $config['source']['line_offset'];

    $file = $args['file'];
    $line = $args['line'];
    $errtype = $args['errtype'];
    $message = $args['message'];
    $fullhtml = $args['fullhtml'];

    if (!isset($args['backtrace'])) {
        $backtrace = [];
        $display_backtrace = false;
    } else {
        $backtrace = $args['backtrace'];
    }

    $source = getsource(
        $file,
        $line,
        $line_offset,
        $display_source
    );

    extract($args);
    include 'nice_error.phtml';

    if ($fullhtml)
        die;
}

/**
 * error handler
 * @param int $errnum
 * @param string $message
 * @param string $file
 * @param string $line
 */
function handleerror($errnum, $message, $file, $line)
{
    $config = config();
    $errkill = $config['error']['kill'];
    $errmsgs = $config['error']['label'];
    $errtype = array_key_exists($errnum, $errmsgs) ?
        $errmsgs[ $errnum ] : $errnum;

    renderview([
        'errtype' => $errtype,
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'backtrace' => debug_backtrace(),
        'fullhtml' => $errkill,
    ]);
}

/**
 * uncaught exception handler
 * @param Exception $exception
 */
function handleexception($exception)
{
    $config = config();
    $backtrace = $exception->getTrace();
    $from_throw = $config['exception']['from_throw'];
    $exc_kill = $config['exception']['kill'];

    if (!$from_throw && count($backtrace)) {
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];
    } else {
        $file = $exception->getFile();
        $line = $exception->getLine();
    }

    // prepend exception thrown location
    array_unshift($backtrace, [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ]);

    renderview([
        'errtype' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $file,
        'line' => $line,
        'backtrace' => $backtrace,
        'fullhtml' => $exc_kill,
    ]);
}

/**
 * shutdown handler
 */
function handleshutdown()
{
    $config = config();
    $error = error_get_last();
    $errmsgs = $config['error']['label'];
    $shutdown = $config['error']['shutdown'];

    if (!is_null($error)) {
        extract($error);
        $errtype = array_key_exists($type, $errmsgs) ?
            $errmsgs[ $type ] : $type;

        if (!in_array($type, $shutdown)) {
            return;
        }

        renderview([
            'errtype' => $errtype,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'fullhtml' => true,
        ]);
    }
}

/**
 * @return array
 */
function config()
{
    static $config;

    if (!$config)
        $config = parse_ini_file('nice_error.ini', true);

    return $config;
}

error_reporting(config()['error']['report']);
ini_set('display_errors', 'off');
set_error_handler(__NAMESPACE__ . '\handleerror');
set_exception_handler(__NAMESPACE__ . '\handleexception');
register_shutdown_function(__NAMESPACE__ . '\handleshutdown');

