<?php

namespace Liquid;

/**
 * The Template class.
 * 
 * Example:
 *
 *     $tpl = new \Liquid\Template();
 *     $tpl->parse(template_source);
 *     $tpl->render(array('foo'=>1, 'bar'=>2);
 */

class Template
{
    /**
     * @var LiquidDocument The _root of the node tree
     */
    private $_root;

    /**
     * @var LiquidBlankFileSystem The file system to use for includes
     */
    private $_fileSystem;

    /**
     * @var array Globally included filters
     */
    private $_filters;

    /**
     * @var array Custom tags
     */
    private static $_tags = array();


    private static $_cache;


    /**
     * Constructor
     *
     * @return Template
     */
    public function __construct($path = null, $cache = null)
    {
        $this->_fileSystem = (isset($path)) ? new LiquidLocalFileSystem($path) : new LiquidBlankFileSystem();
        $this->_filters = array();
        $this->setCache($cache);
    }


    /**
     * 
     *
     */
    public function setFileSystem($fileSystem)
    {
        $this->_fileSystem = $fileSystem;
    }


    /**
     * 
     *
     */
    public function setCache($cache)
    {
        if (is_array($cache))
        {
            if (isset($cache['cache']) && class_exists('LiquidCache' . ucwords($cache['cache'])))
            {
                $classname = 'LiquidCache' . ucwords($cache['cache']);
                self::$_cache = new $classname($cache);
            }
            else
                throw new LiquidException('Invalid Cache options!');
        }
        else
        {
            self::$_cache = $cache;
        }
    }


    /**
     * 
     *
     * @return object
     */
    public static function getCache()
    {
        return self::$_cache;
    }


    /**
     * 
     *
     * @return LiquidDocument
     */
    public function getRoot()
    {
        return $this->_root;
    }


    /**
     * Register custom Tags
     *
     * @param string $name
     * @param string $class
     */
    public function registerTag($name, $class)
    {
        self::$_tags[$name] = $class;
    }


    /**
     * 
     *
     * @return array
     */
    public static function getTags()
    {
        return self::$_tags;
    }


    /**
     * Register the filter
     *
     * @param unknown_type $filter
     */
    public function registerFilter($filter)
    {
        $this->_filters[] = $filter;
    }


    /**
     * Tokenizes the given source string
     *
     * @param string $source
     * @return array
     */
    public static function tokenize($source)
    {
        return (!$source) ? array() : preg_split(LIQUID_TOKENIZATION_REGEXP, $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }


    /**
     * Parses the given source string
     *
     * @param string $source
     */
    public function parse($source)
    {
        $cache = self::$_cache;

        if (isset($cache))
        {
            if (($this->_root = $cache->read(md5($source))) != false && $this->_root->checkIncludes() != true)
            {
            }
            else
            {
                $this->_root = new LiquidDocument(Template::tokenize($source), $this->_fileSystem);
                $cache->write(md5($source), $this->_root);
            }
        }
        else
        {
            $this->_root = new LiquidDocument(Template::tokenize($source), $this->_fileSystem);
        }
        return $this;
    }


    /**
     * Renders the current template
     *
     * @param array $assigns An array of values for the template
     * @param array $filters Additional filters for the template
     * @param array $registers Additional registers for the template
     * @return string
     */
    public function render(array $assigns = array(), $filters = null, $registers = null)
    {
        $context = new Context($assigns, $registers);

        if (!is_null($filters))
        {
            if (is_array($filters))
            {
                array_merge($this->_filters, $filters);
            }
            else
            {
                $this->_filters[] = $filters;
            }
        }

        foreach($this->_filters as $filter)
        {
            $context->addFilters($filter);
        }

        return $this->_root->render($context);
    }
}
