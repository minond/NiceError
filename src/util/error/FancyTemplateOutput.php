<?php

namespace util\error;

use util\Error;

/**
 * nice html output
 */
class FancyTemplateOutput implements ErrorOutput
{
    /**
     * {@inheritDoc}
     */
    public function render(Error $error)
    {
        // error/util/src/./resources/fancy
        include __dir__ . '/../../../resources/fancy/template.phtml';
        die;
    }
}

