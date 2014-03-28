<?php

namespace util\error;

class Helper
{
    /**
     * can we send html output?
     * @return boolean
     */
    public static function acceptsHtml()
    {
        return isset($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false;
    }

    /**
     * is NiceError enabled?
     * @return boolean
     */
    public static function enabled()
    {
        // why do I need to check $_ENV and getenv????
        return (isset($_ENV['NICE_ERRORS']) && $_ENV['NICE_ERRORS']) ||
            getenv('NICE_ERRORS');
    }
}

