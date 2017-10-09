# Liquid template engine for PHP [![Build Status](https://travis-ci.org/kalimatas/php-liquid.svg?branch=master)](https://travis-ci.org/kalimatas/php-liquid) [![Coverage Status](https://coveralls.io/repos/github/kalimatas/php-liquid/badge.svg?branch=master)](https://coveralls.io/github/kalimatas/php-liquid?branch=master) [![Total Downloads](https://poser.pugx.org/liquid/liquid/downloads.svg)](https://packagist.org/packages/liquid/liquid)

Liquid is a PHP port of the [Liquid template engine for Ruby](https://github.com/Shopify/liquid), which was written by Tobias Lutke. Although there are many other templating engines for PHP, including Smarty (from which Liquid was partially inspired), Liquid had some advantages that made porting worthwhile:

 * Readable and human friendly syntax, that is usable in any type of document, not just html, without need for escaping.
 * Quick and easy to use and maintain.
 * 100% secure, no possibility of embedding PHP code.
 * Clean OO design, rather than the mix of OO and procedural found in other templating engines.
 * Seperate compiling and rendering stages for improved performance.
 * Easy to extend with your own "tags and filters":https://github.com/harrydeluxe/php-liquid/wiki/Liquid-for-programmers.
 * 100% Markup compatibility with a Ruby templating engine, making templates usable for either.
 * Unit tested: Liquid is fully unit-tested. The library is stable and ready to be used in large projects.

## Why Liquid?

Why another templating library?

Liquid was written to meet three templating library requirements: good performance, easy to extend, and simply to use.

## Installing

You can install this lib via [composer](https://getcomposer.org/):

    composer require liquid/liquid

## Example template

	{% if products %}
		<ul id="products">
		{% for product in products %}
		  <li>
			<h2>{{ product.name }}</h2>
			Only {{ product.price | price }}

			{{ product.description | prettyprint | paragraph }}

			{{ 'it rocks!' | paragraph }}

		  </li>
		{% endfor %}
		</ul>
	{% endif %}

## How to use Liquid

The main class is `Liquid::Template` class. There are two separate stages of working with Liquid templates: parsing and rendering. Here is a simple example:

    use Liquid\Template;

    $template = new Template();
    $template->parse("Hello, {{ name }}!");
    echo $template->render(array('name' => 'Alex'));

	// Will echo
	// Hello, Alex!

To find more examples have a look at the `examples` directory or at the original Ruby implementation repository's [wiki page](https://github.com/Shopify/liquid/wiki).

## Advanced usage

You would probably want to add a caching layer (at very least a request-wide one), enable context-aware automatic escaping, and do load includes from disk with full file names.

    use Liquid\Liquid;
    use Liquid\Template;
    use Liquid\Cache\Local;

    Liquid::set('INCLUDE_SUFFIX', '');
    Liquid::set('INCLUDE_PREFIX', '');
    Liquid::set('INCLUDE_ALLOW_EXT', true);
    Liquid::set('ESCAPE_BY_DEFAULT', true);

    $template = new Template(__DIR__.'/protected/templates/');

    $template->parse("Hello, {% include 'honorific.html' %}{{ plain-html | raw }} {{ comment-with-xss }}");
    $template->setCache(new Local());

	echo $template->render([
	    'name' => 'Alex',
	    'plain-html' => '<b>Your comment was:</b>',
	    'comment-with-xss' => '<script>alert();</script>',
	]);

Will output:

	Hello, Mx. Alex
	<b>Your comment was:</b> &lt;script&gt;alert();&lt;/script&gt;

Note that automatic escaping is not a standard Liquid feature: use with care.

Similarly, the following snippet will parse and render `templates/home.liquid` while storing parsing results in a class-local cache:

    \Liquid\Liquid::set('INCLUDE_PREFIX', '');

    $template = new \Liquid\Template(__DIR__ . '/protected/templates');
    $template->setCache(new \Liquid\Cache\Local());
    echo $template->parseFile('home')->render();

If you render the same template over and over for at least a dozen of times, the class-local cache will give you a slight speed up in range of some milliseconds per render depending on a complexity of your template.

You should probably extend `Liquid\Template` to initialize everything you do with `Liquid::set` in one place.

### Custom filters

Adding filters has never been easier.

	$template = new Template();
	$template->registerFilter('absolute_url', function ($arg) {
	    return "https://www.example.com$arg";
	});
	$template->parse("{{ my_url | absolute_url }}");
	echo $template->render(array(
	    'my_url' => '/test'
	));
	// expect: https://www.example.com/test

## Requirements

 * PHP 5.6+

Package versions below 1.4 could be used with PHP 5.3/5.4/5.5.

## Issues

Have a bug? Please create an issue here on GitHub!

[https://github.com/kalimatas/php-liquid/issues](https://github.com/kalimatas/php-liquid/issues)

## Fork notes

This fork is based on [php-liquid](https://github.com/harrydeluxe/php-liquid) by Harald Hanek.

It contains several improvements:

 * namespaces
 * installing via composer
 * new standard filters
 * `raw` tag added

Any help is appreciated!
