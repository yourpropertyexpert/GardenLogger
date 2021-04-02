<?php

/**
 * Wrapper class for mustache templates
 *
 * Common include file for the WerAreWe Frontend
 * Template class
 * copyright WerAreWe Ltd 2019, used by permission of board
 */

namespace yourpropertyexpert;

use Mustache_Loader_FilesystemLoader;
use Mustache_Engine;

/**
 * Class name deliberately chosen to allow migration away from Mustache if a better tech appears
 * @copyright WerAreWe 2019, 2020
 */
class Template
{
    /** @var $mustache A Mustache engine object created in the constructor */
    private $mustache;

    /**
     * Constructor for Template object
     */
    public function __construct()
    {
        $mloader = new Mustache_Loader_FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/templates');
        $this->mustache = new Mustache_Engine(['loader' => $mloader]);
    }

    /**
     * Convert a template name and data to a string
     * @param string $template a template name
     * @param array $data the data needed by the template
     * @return string the processed template
     */
    public function render($template, $data)
    {
        return $this->mustache->render($template, $data);
    }
}
