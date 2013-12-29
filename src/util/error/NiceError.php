<?php

namespace util\error;

/**
 * error handlers
 */
class NiceError
{
    /**
     * @var Output[]
     */
    protected $outputs;

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
     * @param Output[] $outputs
     */
    public function __construct(array $outputs = [])
    {
        $this->outputs = $outputs;
    }

    /**
     * @param Output $output
     */
    public function addOutput(Output $output)
    {
        $this->outputs[] = $output;
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
        $this->render(new Error([
            'message' => $message,
            'backtrace' => debug_backtrace(),
            'errtype' => array_key_exists($errnum, $this->errmsgs) ?
                $this->errmsgs[ $errnum ] : $errnum,
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
            $file = $this->getVal($backtrace, [ 0, 'file' ]);
            $line = $this->getVal($backtrace, [ 0, 'line' ]);
            $class = $this->getVal($backtrace, [ 0, 'class' ]);
            $function = $this->getVal($backtrace, [ 0, 'function' ]);
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

        $this->render(new Error([
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

            // I don't like it, but it'll do for now.
            if ($message === 'Module \'xdebug\' already loaded') {
                return;
            }

            $this->render(new Error([
                'errtype' => $errtype,
                'message' => $message,
                'backtrace' => [ $error ]
            ]));
        }
    }

    /**
     * loop through all output objects and render the error
     * @param Error $error
     */
    protected function render(Error $error)
    {
        foreach ($this->outputs as & $output) {
            $output->render($error);
            unset($output);
        }

        die;
    }

    /**
     * get an array value if keys are set
     * @param array $var
     * @param array $path
     * @return mixed
     */
    protected function getVal($var, array $path)
    {
        $val = $var;

        foreach ($path as $key) {
            if (isset($val[ $key ])) {
                $val = $val[ $key ];
            } else {
                $val = null;
                break;
            }
        }

        return $val;
    }
}

