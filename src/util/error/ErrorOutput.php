<?php

namespace util\error;

use util\Error;

/**
 * error output base
 */
interface ErrorOutput
{
    /**
     * process error information and send to client
     * @param Error $error
     * @return void
     */
    public function render(Error $error);
}

