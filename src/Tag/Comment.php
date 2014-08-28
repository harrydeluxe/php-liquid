<?php
/**
 * Creates a comment; everything inside will be ignored
 *
 * @example
 * {% comment %} This will be ignored {% endcomment %}
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidTagComment extends LiquidBlock
{
    /**
     * Renders the block
     *
     * @param LiquidContext $context
     * @return string
     */
    public function render(&$context)
    {
        return '';
    }
}
