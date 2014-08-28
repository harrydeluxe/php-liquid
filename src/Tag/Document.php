<?php

namespace Liquid\Tag;

use Liquid\BlankFileSystem;

/**
 * This class represents the entire template document.
 */
class Document extends AbstractBlock
{
    /**
     * Constructor.
     *
     * @param array $tokens
     * @param BlankFileSystem $fileSystem
     */
    public function __construct($tokens, &$fileSystem)
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
    public function blockDelimiter()
    {
        return '';
    }


    /**
     * Document blocks don't need to be terminated since they are not actually opened
     */
    public function assertMissingDelimitation()
    {
    }
}
