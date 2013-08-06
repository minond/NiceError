<?php

namespace util\error;

/**
 * nice html output
 */
class FancyTemplateOutput implements ErrorOutput
{
    /**
     * path to template file
     * @var string
     */
    protected $template;

    /**
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritDoc}
     */
    public function render(array $error)
    {
        extract($error);
        include $this->template;
        die;
    }
}

