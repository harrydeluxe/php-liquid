<?php
/**
 * An if statement
 * 
 * @example
 * {% if true %} YES {% else %} NO {% endif %}
 * 
 * will return:
 * YES
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidTagIf extends LiquidDecisionBlock
{

    /**
     * Array holding the nodes to render for each logical block
     *
     * @var array
     */
    private $_nodelistHolders = array();

    /**
     * Array holding the block type, block markup (conditions) and block nodelist
     *
     * @var array
     */
    private $_blocks = array();


    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidFileSystem $fileSystem
     * @return IfLiquidTag
     */
    public function __construct($markup, &$tokens, &$fileSystem)
    {
        $this->_nodelist = &$this->_nodelistHolders[count($this->_blocks)];

        array_push($this->_blocks, array(
            'if', $markup, &$this->_nodelist
        ));

        parent::__construct($markup, $tokens, $fileSystem);

    }


    /**
     * Handler for unknown tags, handle else tags
     *
     * @param string $tag
     * @param array $params
     * @param array $tokens
     */
    public function unknownTag($tag, $params, &$tokens)
    {
        if ($tag == 'else' || $tag == 'elsif')
        {
            /* Update reference to nodelistHolder for this block */
            $this->_nodelist = &$this->_nodelistHolders[count($this->_blocks) + 1];
            $this->_nodelistHolders[count($this->_blocks) + 1] = array();

            array_push($this->_blocks, array(
                $tag, $params, &$this->_nodelist
            ));

        }
        else
        {
            parent::unknownTag($tag, $params, $tokens);
        }
    }


    /**
     * Render the tag
     *
     * @param LiquidContext $context
     */
    public function render(&$context)
    {
        $context->push();

        $logicalRegex = new LiquidRegexp('/\s+(and|or)\s+/');
        $conditionalRegex = new LiquidRegexp('/(' . LIQUID_QUOTED_FRAGMENT . ')\s*([=!<>a-z_]+)?\s*(' . LIQUID_QUOTED_FRAGMENT . ')?/');

        $result = '';

        foreach($this->_blocks as $i => $block)
        {
            if ($block[0] == 'else')
            {
                $result = $this->renderAll($block[2], $context);

                break;
            }

            if ($block[0] == 'if' || $block[0] == 'elsif')
            {
                /* Extract logical operators */
                $logicalRegex->match($block[1]);

                $logicalOperators = $logicalRegex->matches;
                array_shift($logicalOperators);

                /* Extract individual conditions */
                $temp = $logicalRegex->split($block[1]);

                $conditions = array();

                foreach($temp as $condition)
                {
                    if ($conditionalRegex->match($condition))
                    {
                        $left = (isset($conditionalRegex->matches[1])) ? $conditionalRegex->matches[1] : null;
                        $operator = (isset($conditionalRegex->matches[2])) ? $conditionalRegex->matches[2] : null;
                        $right = (isset($conditionalRegex->matches[3])) ? $conditionalRegex->matches[3] : null;

                        array_push($conditions, array(
                                'left' => $left,
                                'operator' => $operator,
                                'right' => $right
                        ));
                    }
                    else
                    {
                        throw new LiquidException("Syntax Error in tag 'if' - Valid syntax: if [condition]");
                    }
                }

                if (count($logicalOperators))
                {
                    /* If statement contains and/or */
                    $display = true;

                    foreach($logicalOperators as $k => $logicalOperator)
                    {
                        if ($logicalOperator == 'and')
                        {
                            $display = $this->_interpretCondition($conditions[$k]['left'], $conditions[$k]['right'], $conditions[$k]['operator'], $context) && $this->_interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context);
                        }
                        else
                        {
                            $display = $this->_interpretCondition($conditions[$k]['left'], $conditions[$k]['right'], $conditions[$k]['operator'], $context) || $this->_interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context);
                        }
                    }

                }
                else
                {
                    /* If statement is a single condition */
                    $display = $this->_interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
                }

                if ($display)
                {
                    $result = $this->renderAll($block[2], $context);

                    break;
                }
            }
        }

        $context->pop();

        return $result;
    }
}
