<?php
/**
 * Base class for blocks.
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidBlock extends LiquidTag
{
    /**
     * @var array
     */
    protected $_nodelist;


    /**
     * 
     *
     * @return array
     */
    public function getNodelist()
    {
        return $this->_nodelist;
    }


    /**
     * Parses the given tokens
     *
     * @param array $tokens
     */
    public function parse(&$tokens)
    {
        $start_regexp = new LiquidRegexp('/^' . LIQUID_TAG_START . '/');
        $tag_regexp = new LiquidRegexp('/^' . LIQUID_TAG_START . '\s*(\w+)\s*(.*)?' . LIQUID_TAG_END . '$/');
        $variable_start_regexp = new LiquidRegexp('/^' . LIQUID_VARIABLE_START . '/');

        $this->_nodelist = array();

        if (!is_array($tokens))
        {
            return;
        }

        $tags = LiquidTemplate::getTags();

        while(count($tokens))
        {
            $token = array_shift($tokens);

            if ($start_regexp->match($token))
            {
                if ($tag_regexp->match($token))
                {
                    // if we found the proper block delimitor just end parsing here and let the outer block proceed 
                    if ($tag_regexp->matches[1] == $this->block_delimiter())
                    {
                        return $this->end_tag();
                    }

                    if (array_key_exists($tag_regexp->matches[1], $tags))
                        $tag_name = $tags[$tag_regexp->matches[1]];
                    else
                    {
                        $tag_name = 'LiquidTag' . ucwords($tag_regexp->matches[1]);// search for a defined class of the right name, instead of searching in an array	
                        $tag_name = (Liquid::classExists($tag_name) === true) ? $tag_name : null;
                    }

                    if (class_exists($tag_name))
                    {
                        $this->_nodelist[] = new $tag_name($tag_regexp->matches[2], $tokens, $this->file_system);
                    }
                    else
                    {
                        $this->unknown_tag($tag_regexp->matches[1], $tag_regexp->matches[2], $tokens);
                    }
                }
                else
                {
                    throw new LiquidException("Tag $token was not properly terminated");// harry
                }

            }
            elseif ($variable_start_regexp->match($token))
            {
                $this->_nodelist[] = $this->create_variable($token);

            }
            elseif ($token != '')
            {
                $this->_nodelist[] = $token;
            }
        }

        $this->assert_missing_delimitation();
    }


    /**
     * An action to execute when the end tag is reached
     *
     */
    function end_tag()
    {
    }


    /**
     * Handler for unknown tags
     *
     * @param string $tag
     * @param array $params
     * @param array $tokens
     */
    function unknown_tag($tag, $params, &$tokens)
    {
        switch ($tag)
        {
            case 'else':
                throw new LiquidException($this->block_name() . " does not expect else tag");

            case 'end':
                throw new LiquidException("'end' is not a valid delimiter for " . $this->block_name() . " tags. Use " . $this->block_delimiter());

            default:
                throw new LiquidException("Unkown tag $tag");
        }

    }


    /**
     * Returns the string that delimits the end of the block
     *
     * @return string
     */
    function block_delimiter()
    {
        return "end" . $this->block_name();
    }


    /**
     * Returns the name of the block
     *
     * @return string
     */
    function block_name()
    {
        return str_replace('liquidtag', '', strtolower(get_class($this)));
    }


    /**
     * Create a variable for the given token
     *
     * @param string $token
     * @return LiquidVariable
     */
    function create_variable($token)
    {
        $variable_regexp = new LiquidRegexp('/^' . LIQUID_VARIABLE_START . '(.*)' . LIQUID_VARIABLE_END . '$/');
        if ($variable_regexp->match($token))
            return new LiquidVariable($variable_regexp->matches[1]);

        throw new LiquidException("Variable $token was not properly terminated");
    }


    /**
     * Render the block.
     *
     * @param LiquiContext $context
     * @return string
     */
    public function render(&$context)
    {
        return $this->render_all($this->_nodelist, $context);
    }


    /**
     * This method is called at the end of parsing, and will through an error unless
     * this method is subclassed, like it is for LiquidDocument
     *
     * @return bool
     */
    function assert_missing_delimitation()
    {
        throw new LiquidException($this->block_name() . " tag was never closed");
    }


    /**
     * Renders all the given nodelist's nodes
     *
     * @param array $list
     * @param LiquidContext $context
     * @return string
     */
    protected function render_all(array $list, &$context)
    {
        $result = '';

        foreach($list as $token)
        {
            $result .= (is_object($token) && method_exists($token, 'render')) ? $token->render($context) : $token;
        }

        return $result;
    }
}
