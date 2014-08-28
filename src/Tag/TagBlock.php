<?php

namespace Liquid\Tag;

use Liquid\Context;
use Liquid\LiquidException;
use Liquid\BlankFileSystem;
use Liquid\Regexp;

/**
 * Marks a section of a template as being reusable.
 *
 * @example
 * {% block foo %} bar {% endblock %}
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek
 * @license http://harrydeluxe.mit-license.org
 */

class TagBlock extends AbstractBlock
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $_block;

    /**
     * Constructor
     *
     * @param string $markup
     * @param Array $tokens
     * @param BlankFileSystem $fileSystem
     * @return TagCapture
     */
    public function __construct($markup, &$tokens, &$fileSystem)
    {
        $syntaxRegexp = new Regexp('/(\w+)/');

        if ($syntaxRegexp->match($markup))
        {
            $this->_block = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens, $fileSystem);
        }
        else
        {
            throw new LiquidException("Syntax Error in 'block' - Valid syntax: block [name]");
        }
    }


    /**
     * Renders the block
     *
     * @param Context $context
     */
    public function render(&$context)
    {
        return parent::render($context);
    }
}
