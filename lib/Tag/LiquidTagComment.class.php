<?php
/**
 * Creates a comment; everything inside will be ignored
 *
 * @example
 * {% comment %} This will be ignored {% endcomment %}
 * 
 * @package Liquid
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