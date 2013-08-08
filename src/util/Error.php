<?php

namespace util;

/**
 * represents error data
 */
class Error
{
    /**
     * type of error (Exception, user error, etc.)
     * @var string
     */
    public $errtype;

    /**
     * error's message
     * @var string
     */
    public $message;

    /**
     * backtrace from error point. should always include information from where
     * error was triggered from.
     * @var array
     */
    public $backtrace;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $prop => $value)
            $this->{ $prop } = $value;
    }
}

