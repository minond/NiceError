<?php

namespace util\error;

use util\Error;

/**
 * error handlers
 */
class NiceError
{
    /**
     * @var ErrorOutput
     */
    protected $output;

    /**
     * @var array
     */
    protected $errmsgs = [
        E_ERROR => 'Fatal run-time error',
        E_WARNING => 'Run-time warning',
        E_PARSE => 'Compile-time parse error',
        E_NOTICE => 'Run-time notice',
        E_CORE_ERROR => 'Initial startup fatal error',
        E_CORE_WARNING => 'Warning',
        E_COMPILE_ERROR => 'Fatal compile-time error',
        E_COMPILE_WARNING => 'Compile-time warning',
        E_USER_ERROR => 'User-generated error',
        E_USER_WARNING => 'User-generated warning',
        E_USER_NOTICE => 'User-generated notice',
        E_STRICT => 'Strict mode warning',
        E_RECOVERABLE_ERROR => 'Catchable fatal error',
        E_DEPRECATED => 'Run-time notice',
        E_USER_DEPRECATED => 'User-generated warning',
    ];

    /**
     * @var array
     */
    protected $shutdown = [
        E_ERROR,
        E_WARNING,
        E_PARSE,
        E_NOTICE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING,
        E_USER_ERROR,
        E_USER_WARNING,
        E_USER_NOTICE,
        E_STRICT,
        E_RECOVERABLE_ERROR,
        E_DEPRECATED,
        E_USER_DEPRECATED,
    ];

    /**
     * @param array $config
     */
    public function __construct(ErrorOutput $output)
    {
        $this->output = $output;
    }

    /**
     * error handler
     * @param int $errnum
     * @param string $message
     * @param string $file
     * @param string $line
     */
    public function handleError($errnum, $message, $file, $line)
    {
        $errtype = array_key_exists($errnum, $this->errmsgs) ?
            $this->errmsgs[ $errnum ] : $errnum;

        // remove this function
        $backtrace = debug_backtrace();
        array_shift($backtrace);

        $this->output->render(new Error([
            'errtype' => $errtype,
            'message' => $message,
            'backtrace' => $backtrace,
        ]));
    }

    /**
     * uncaught exception handler
     * @param Exception $exception
     */
    public function handleException($exception)
    {
        $backtrace = $exception->getTrace();
        $class = '';
        $function = '';

        if (count($backtrace) && isset($backtrace[0]['line'])) {
            $file = $backtrace[0]['file'];
            $line = $backtrace[0]['line'];
            $class = $backtrace[0]['class'];
            $function = $backtrace[0]['function'];
        } else {
            $file = $exception->getFile();
            $line = $exception->getLine();
            $backtrace = debug_backtrace();
            array_shift($backtrace);
        }

        // prepend exception thrown location
        array_unshift($backtrace, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'class' => $class,
            'function' => $function,
        ]);

        $this->output->render(new Error([
            'errtype' => get_class($exception),
            'message' => $exception->getMessage(),
            'backtrace' => $backtrace,
        ]));
    }

    /**
     * shutdown handler
     */
    public function handleShutdown()
    {
        $error = error_get_last();

        if (!is_null($error)) {
            extract($error);
            $errtype = array_key_exists($type, $this->errmsgs) ?
                $this->errmsgs[ $type ] : $type;

            if (!in_array($type, $this->shutdown)) {
                return;
            }

            $this->output->render(new Error([
                'errtype' => $errtype,
                'message' => $message,
                'backtrace' => [ $error ]
            ]));
        }
    }
}

