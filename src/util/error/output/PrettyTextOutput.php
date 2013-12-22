<?php

namespace util\error\output;

use util\error\Error;
use util\error\Output;

/**
 * pretty output for a terminal
 */
class PrettyTextOutput implements Output
{
    /**
     * {@inheritDoc}
     */
    public function render(Error $error)
    {
        $stderr = fopen('php://stderr', 'w');
        $dir = getcwd();

        // error:
        // util\error\Error Object
        // (
        //     [errtype] => Exception
        //     [message] => Invalid static method ll called on class
        //                  Propositum\Model\Task
        //     [backtrace] => Array
        //         (
        //             [0] => Array
        //                 (
        //                     [file] => /home/server/Propositum/vendor/
        //                               minond/model/src/Efficio/Dataset/
        //                               Access/GetterSetter.php
        //                     [line] => 142
        //                     [class] => Efficio\Dataset\Model
        //                     [function] => __callStatic
        //                 )
        //
        //             [1] => Array
        //                 (
        //                     [file] => /home/server/Propositum/src/
        //                               Propositum/Controller/Tasks.php
        //                     [line] => 13
        //                     [function] => __callStatic
        //                     [class] => Efficio\Dataset\Model
        //                     [type] => ::
        //                     [args] => Array
        //                         (
        $err = $error->errtype;
        $msg = $error->message;

        // server:
        // Array
        // (
        //     [DOCUMENT_ROOT] => /home/server/Propositum
        //     [REMOTE_ADDR] => 127.0.0.1
        //     [REMOTE_PORT] => 60757
        //     [SERVER_SOFTWARE] => PHP 5.5.3-1ubuntu2.1 Development Server
        //     [SERVER_PROTOCOL] => HTTP/1.1
        //     [SERVER_NAME] => 0.0.0.0
        //     [SERVER_PORT] => 8080
        //     [REQUEST_URI] => /tasks
        //     [REQUEST_METHOD] => GET
        //     [SCRIPT_NAME] => /tasks
        //     [SCRIPT_FILENAME] => /home/server/Propositum//home/server/
        //                          Propositum/vendor/minond/fabrico/src/
        //                          Fabrico/Command/StdCommands/../scripts/
        //                          router.php
        //     [PHP_SELF] => /tasks
        //     [HTTP_HOST] => localhost:8080
        //     [HTTP_CONNECTION] => keep-alive
        //     [HTTP_CACHE_CONTROL] => max-age=0
        //     [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/
        //                      xml;q=0.9,image/webp,*/*;q=0.8
        //     [HTTP_USER_AGENT] => Mozilla/5.0 (X11; Linux x86_64)
        //                          AppleWebKit/537.36 (KHTML, like Gecko)
        //                          Chrome/31.0.1650.63 Safari/537.36
        //     [HTTP_ACCEPT_ENCODING] => gzip,deflate,sdch
        //     [HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.8
        //     [HTTP_COOKIE] => PHPSESSID=jp2jjl13flr1u1r1ecj4askgj6;
        //     [REQUEST_TIME_FLOAT] => 1387688171.964
        //     [REQUEST_TIME] => 1387688171
        // )
        $addr = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        $date = date('D M d H:i:s Y');

        // output from php -S:
        // [Sat Dec 21 21:54:56 2013] 127.0.0.1:60752 Invalid request (...)
        fwrite($stderr, sprintf("[%s] %s:%s %s (%s)\n",
            $date, $addr, $port, $err, $msg));

        // backtrace
        foreach ($error->backtrace as $i => $trace) {
            $file = $this->getVal($trace, 'file');
            $file = str_replace($dir, '', $file);
            $line = $this->getVal($trace, 'line');

            $clazz = $this->getVal($trace, 'class');
            $type = $this->getVal($trace, 'type');
            $function = $this->getVal($trace, 'function');

            if ($clazz && $type) {
                $function = $clazz . $type . $function;
            }

            fwrite($stderr, sprintf("  #%s %s [%s:%s]\n",
                $i + 1, $function, $file, $line));
        }
    }

    /**
     * return an array's value if its key exists
     * shortcut for: isset($arr['?']) ? $arr['?'] : ''
     * @param array $arr
     * @param string $key
     * @return string
     */
    protected function getVal(array $arr, $key)
    {
        return isset($arr[ $key ]) ? $arr[ $key ] : '';
    }
}

