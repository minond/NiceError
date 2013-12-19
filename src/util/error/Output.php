<?php

namespace util\error;

/**
 * error output base
 */
interface Output
{
    /**
     * process error information and send to client
     * @param Error $error
     * @return void
     */
    public function render(Error $error);
}

