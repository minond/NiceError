<?php

namespace util\error;

/**
 * error output base
 */
interface ErrorOutput
{
    /**
     * process error information and send to client
     * @param array $error
     * @return void
     */
    public function render(array $error);
}

