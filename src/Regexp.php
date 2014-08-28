<?php
/**
 * A support class for regular expressions and
 * non liquid specific support classes and functions.
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidRegexp
{

    /**
     * The regexp pattern
     *
     * @var string
     */
    public $pattern;

    /**
     * The matches from the last method called
     *
     * @var array;
     */
    public $matches;


    /**
     * Constructor
     *
     * @param string $pattern
     * @return Regexp
     */
    public function __construct($pattern)
    {
        $this->pattern = (substr($pattern, '0', 1) != '/') ? '/' . $this->quote($pattern) . '/' : $pattern;
    }


    /**
     * Quotes regular expression characters
     *
     * @param string $string
     * @return string
     */
    function quote($string)
    {
        return preg_quote($string, '/');
    }


    /**
     * Returns an array of matches for the string in the same way as Ruby's scan method
     *
     * @param string $string
     * @return array
     */
    function scan($string)
    {
        $result = preg_match_all($this->pattern, $string, $matches);

        if (count($matches) == 1)
        {
            return $matches[0];
        }

        array_shift($matches);

        $result = array();

        foreach($matches as $matchKey => $subMatches)
        {
            foreach($subMatches as $subMatchKey => $subMatch)
            {
                $result[$subMatchKey][$matchKey] = $subMatch;
            }
        }

        return $result;
    }


    /**
     * Matches the given string. Only matches once.
     *
     * @param string $string
     * @return int 1 if there was a match, 0 if there wasn't
     */
    public function match($string)
    {
        return preg_match($this->pattern, $string, $this->matches);
    }


    /**
     * Matches the given string. Matches all.
     *
     * @param string $string
     * @return int The number of matches
     */
    function match_all($string)
    {
        return preg_match_all($this->pattern, $string, $this->matches);
    }


    /**
     * Splits the given string
     *
     * @param string $string
     * @param int $limit Limits the amount of results returned
     * @return array
     */
    function split($string, $limit = null)
    {
        return preg_split($this->pattern, $string, $limit);
    }
}
