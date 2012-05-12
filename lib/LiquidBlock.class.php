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
        $startRegexp = new LiquidRegexp('/^' . LIQUID_TAG_START . '/');
        $tagRegexp = new LiquidRegexp('/^' . LIQUID_TAG_START . '\s*(\w+)\s*(.*)?' . LIQUID_TAG_END . '$/');
        $variableStartRegexp = new LiquidRegexp('/^' . LIQUID_VARIABLE_START . '/');

        $this->_nodelist = array();

        if (!is_array($tokens))
        {
            return;
        }

        $tags = LiquidTemplate::getTags();

        while(count($tokens))
        {
            $token = array_shift($tokens);

            if ($startRegexp->match($token))
            {
                if ($tagRegexp->match($token))
                {
                    // if we found the proper block delimitor just end parsing here and let the outer block proceed
                    if ($tagRegexp->matches[1] == $this->blockDelimiter())
                    {
                        return $this->endTag();
                    }

                    if (array_key_exists($tagRegexp->matches[1], $tags))
                        $tag_name = $tags[$tagRegexp->matches[1]];
                    else
                    {
                        $tag_name = 'LiquidTag' . ucwords($tagRegexp->matches[1]);// search for a defined class of the right name, instead of searching in an array
                        $tag_name = (Liquid::classExists($tag_name) === true) ? $tag_name : null;
                    }

                    if (class_exists($tag_name))
                    {
                        $this->_nodelist[] = new $tag_name($tagRegexp->matches[2], $tokens, $this->_fileSystem);
                        if($tagRegexp->matches[1] == 'extends')
                            return true;
                    }
                    else
                    {
                        $this->unknownTag($tagRegexp->matches[1], $tagRegexp->matches[2], $tokens);
                    }
                }
                else
                {
                    throw new LiquidException("Tag $token was not properly terminated");// harry
                }

            }
            elseif ($variableStartRegexp->match($token))
            {
                $this->_nodelist[] = $this->_createVariable($token);

            }
            elseif ($token != '')
            {
                $this->_nodelist[] = $token;
            }
        }

        $this->assertMissingDelimitation();
    }


    /**
     * An action to execute when the end tag is reached
     *
     */
    protected function endTag()
    {
    }


    /**
     * Handler for unknown tags
     *
     * @param string $tag
     * @param array $params
     * @param array $tokens
     */
    protected function unknownTag($tag, $params, &$tokens)
    {
        switch ($tag)
        {
            case 'else':
                throw new LiquidException($this->_blockName() . " does not expect else tag");

            case 'end':
                throw new LiquidException("'end' is not a valid delimiter for " . $this->_blockName() . " tags. Use " . $this->blockDelimiter());

            default:
                throw new LiquidException("Unkown tag $tag");
        }

    }


    /**
     * Returns the string that delimits the end of the block
     *
     * @return string
     */
    function blockDelimiter()
    {
        return "end" . $this->_blockName();
    }


    /**
     * Returns the name of the block
     *
     * @return string
     */
    private function _blockName()
    {
        return str_replace('liquidtag', '', strtolower(get_class($this)));
    }


    /**
     * Create a variable for the given token
     *
     * @param string $token
     * @return LiquidVariable
     */
    private function _createVariable($token)
    {
        $variableRegexp = new LiquidRegexp('/^' . LIQUID_VARIABLE_START . '(.*)' . LIQUID_VARIABLE_END . '$/');
        if ($variableRegexp->match($token))
            return new LiquidVariable($variableRegexp->matches[1]);

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
        return $this->renderAll($this->_nodelist, $context);
    }


    /**
     * This method is called at the end of parsing, and will through an error unless
     * this method is subclassed, like it is for LiquidDocument
     *
     * @return bool
     */
    function assertMissingDelimitation()
    {
        throw new LiquidException($this->_blockName() . " tag was never closed");
    }


    /**
     * Renders all the given nodelist's nodes
     *
     * @param array $list
     * @param LiquidContext $context
     * @return string
     */
    protected function renderAll(array $list, &$context)
    {
        $result = '';

        foreach($list as $token)
        {
            $result .= (is_object($token) && method_exists($token, 'render')) ? $token->render($context) : $token;
        }

        return $result;
    }
}
