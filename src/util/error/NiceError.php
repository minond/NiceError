<?php

namespace util\error;

/**
 * error handlers
 */
class NiceError
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * source contents getter
     * @param string file
     * @param int $line
     * @param int $offset - optional, default = 10
     * @return string
     */
    protected function getsource($file, $line, $offset = 10)
    {
        $source = [];
        $lines = explode(PHP_EOL, file_get_contents($file));
        $i = $line - $offset;
        $max = $line + $offset + 1;

        for (; $i < $max; $i++)
            if (isset($lines[ $i - 1 ]))
                $source[] = [
                    'text' => $lines[ $i - 1 ],
                    'num' => $i,
                ];

        return $source;
    }

    /**
     * render the error page
     * @param array $args
     */
    protected function renderview(array $args)
    {
        $file = $args['file'];
        $line = $args['line'];
        $errtype = $args['errtype'];
        $message = $args['message'];

        if (!isset($args['backtrace'])) {
            $backtrace = [];
        } else {
            $backtrace = $args['backtrace'];
        }

        $o = function($text) {
            echo htmlspecialchars($text);
        };

        $h = function($text) {
            return htmlspecialchars($text);
        };

        $s = function($obj, $key) {
            return isset($obj[ $key ]) && $obj[ $key ];
        };

        $source = function($file, $line, $offset = 10) {
            $source = [];
            $lines = explode(PHP_EOL, file_get_contents($file));
            $i = $line - $offset;
            $max = $line + $offset + 1;

            for (; $i < $max; $i++)
                if (isset($lines[ $i - 1 ]))
                    $source[] = [
                        'text' => $lines[ $i - 1 ],
                        'num' => $i,
                    ];

            return $source;
        };

        extract($args);
        include $this->config['NiceError']['base_dir'] . '/resources/nice_error.phtml';
        die;
    }

    /**
     * error handler
     * @param int $errnum
     * @param string $message
     * @param string $file
     * @param string $line
     */
    public function handleerror($errnum, $message, $file, $line)
    {
        $errmsgs = $this->config['NiceError']['label'];
        $errtype = array_key_exists($errnum, $errmsgs) ?
            $errmsgs[ $errnum ] : $errnum;

        // remove this function
        $backtrace = debug_backtrace();
        array_shift($backtrace);

        $this->renderview([
            'errtype' => $errtype,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'backtrace' => $backtrace,
        ]);
    }

    /**
     * uncaught exception handler
     * @param Exception $exception
     */
    public function handleexception($exception)
    {
        $backtrace = $exception->getTrace();
        $class = '';
        $function = '';

        if (count($backtrace)) {
            $file = $backtrace[0]['file'];
            $line = $backtrace[0]['line'];
            $class = $backtrace[0]['class'];
            $function = $backtrace[0]['function'];
        } else {
            $file = $exception->getFile();
            $line = $exception->getLine();
            $backtrace = debug_backtrace();
        }

        // prepend exception thrown location
        // array_shift($backtrace);
        array_unshift($backtrace, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'class' => $class,
            'function' => $function,
        ]);

        $this->renderview([
            'errtype' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $file,
            'line' => $line,
            'backtrace' => $backtrace,
        ]);
    }

    /**
     * shutdown handler
     */
    public function handleshutdown()
    {
        $error = error_get_last();
        $errmsgs = $this->config['NiceError']['label'];
        $shutdown = [
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

        if (!is_null($error)) {
            extract($error);
            $errtype = array_key_exists($type, $errmsgs) ?
                $errmsgs[ $type ] : $type;

            if (!in_array($type, $shutdown)) {
                return;
            }

            $this->renderview([
                'errtype' => $errtype,
                'message' => $message,
                'file' => $file,
                'line' => $line,
            ]);
        }
    }
}

