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
        $display_backtrace = $this->config['backtrace']['display'];
        $display_source = $this->config['source']['display'];
        $line_offset = $this->config['source']['line_offset'];

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

        $source = !$display_source ? [] : $this->getsource(
            $file,
            $line,
            $line_offset
        );

        extract($args);
        include $this->config['base_dir'] . '/resources/nice_error.phtml';

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
    public function handleerror($errnum, $message, $file, $line)
    {
        $errkill = $this->config['error']['kill'];
        $errmsgs = $this->config['error']['label'];
        $errtype = array_key_exists($errnum, $errmsgs) ?
            $errmsgs[ $errnum ] : $errnum;

        $this->renderview([
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
    public function handleexception($exception)
    {
        $backtrace = $exception->getTrace();
        $from_throw = $this->config['exception']['from_throw'];
        $exc_kill = $this->config['exception']['kill'];

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

        $this->renderview([
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
    public function handleshutdown()
    {
        $error = error_get_last();
        $errmsgs = $this->config['error']['label'];
        $shutdown = $this->config['error']['shutdown'];

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
                'fullhtml' => true,
            ]);
        }
    }
}

