<?php

namespace Fabrico\Extension\ViewBacktrace;

use Fabrico\Event\Reporter;
use Fabrico\View\View;
use Fabrico\Core\Ext;

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

        for (; $i < $max; $i++) {
            if (isset($lines[ $i - 1 ])) {
                $source[] = [
                    'text' => $lines[ $i - 1 ],
                    'num' => $i,
                ];
            }
        }
    }

    return $source;
}

if (Ext::enabled('view_backtrace') && Ext::enabled('twig')) {
    Reporter::before('fabrico.request.http.request:preparehandler', function($info) {
        $view     = Ext::config('view_backtrace:view');
        $errors   = Ext::config('view_backtrace:error:reporting');
        $err_msg  = Ext::config('view_backtrace:error:label');
        $err_kill = Ext::config('view_backtrace:error:kill');
        $exc_kill = Ext::config('view_backtrace:exception:kill');
        $at_throw = Ext::config('view_backtrace:exception:from_throw');
        $bak_show = Ext::config('view_backtrace:backtrace:display');
        $src_show = Ext::config('view_backtrace:source:display');
        $src_line = Ext::config('view_backtrace:source:line_offset');
        $shutdown = Ext::config('view_backtrace:shutdown:reports');

        error_reporting($errors);
        ini_set('display_errors', 'off');

        set_error_handler(function($errnum, $message, $file, $line) use (
            $view,
            $err_msg,
            $err_kill,
            $bak_show,
            $src_show,
            $src_line
        ) {
            $errtype = array_key_exists($errnum, $err_msg) ?
                $err_msg[ $errnum ] : $errnum;

            echo View::generate($view, [
                'errtype' => $errtype,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'backtrace' => debug_backtrace(),
                'display_backtrace' => $bak_show,
                'display_source' => $src_show,
                'source' => getsource($file, $line, $src_line, $src_show),
                'fullhtml' => $err_kill,
            ]);

            if ($err_kill) {
                die;
            }
        }, $errors);

        register_shutdown_function(function() use (
            $view,
            $err_msg,
            $src_show,
            $src_line,
            $shutdown
        ) {
            $error = error_get_last();

            if (!is_null($error)) {
                extract($error);
                $errtype = array_key_exists($type, $err_msg) ?
                    $err_msg[ $type ] : $type;

                if (!in_array($type, $shutdown)) {
                    return;
                }

                echo View::generate($view, [
                    'errtype' => $errtype,
                    'message' => $message,
                    'file' => $file,
                    'line' => $line,
                    'display_backtrace' => false,
                    'display_source' => $src_show,
                    'source' => getsource($file, $line, $src_line, $src_show),
                    'fullhtml' => true,
                ]);
            }
        });

        set_exception_handler(function($exception) use (
            $view,
            $exc_kill,
            $bak_show,
            $src_show,
            $src_line,
            $at_throw
        ) {
            $backtrace = $exception->getTrace();

            if ($exception instanceof \Twig_Error) {
                $line = $exception->getTemplateLine();
                $file = $exception->getTemplateFile();
                $file = View::generateFileFilderFilePath($file);
            } else if ($at_throw) {
                $file = $exception->getFile();
                $line = $exception->getLine();
            } else {
                $file = $backtrace[0]['file'];
                $line = $backtrace[0]['line'];
            }

            // prepend exception thrown location
            array_unshift($backtrace, [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            echo View::generate($view, [
                'errtype' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $file,
                'line' => $line,
                'backtrace' => $backtrace,
                'display_backtrace' => $bak_show,
                'display_source' => $src_show,
                'source' => getsource($file, $line, $src_line, $src_show),
                'fullhtml' => $exc_kill,
            ]);

            if ($exc_kill) {
                die;
            }
        });
    });
}
