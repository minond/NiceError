<?php

namespace util\error\output;

use util\error\Error;
use util\error\Output;

/**
 * nice html output
 */
class FancyTemplateOutput implements Output
{
    /**
     * {@inheritDoc}
     */
    public function render(Error $error)
    {
        include __dir__ . '/fancy/template.phtml';
        die;
    }
}

