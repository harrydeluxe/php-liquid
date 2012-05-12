<?php
/**
 * This class represents the entire template document
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidDocument extends LiquidBlock
{

    /**
     * Constructor
     *
     * @param array $tokens
     * @param LiquidFileSystem $fileSystem
     * @return LiquidDocument
     */
    function __construct($tokens, &$fileSystem)
    {
        $this->_fileSystem = $fileSystem;
        $this->parse($tokens);
    }


    /**
     * check for cached includes
     *
     * @return string
     */
    public function checkIncludes()
    {
        $return = false;
        foreach($this->_nodelist as $token)
        {
            if (is_object($token))
            {
                if (get_class($token) == 'LiquidTagInclude' || get_class($token) == 'LiquidTagExtends')
                {
                    if ($token->checkIncludes() == true)
                        $return = true;
                }
            }
        }
        return $return;
    }


    /**
     * There isn't a real delimiter
     *
     * @return string
     */
    function blockDelimiter()
    {
        return '';
    }


    /**
     * Document blocks don't need to be terminated since they are not actually opened
     *
     */
    function assertMissingDelimitation()
    {
    }
}
