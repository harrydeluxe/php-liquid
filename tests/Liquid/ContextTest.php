<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

class HundredCentes
{
	public function toLiquid()
	{
		return 100;
	}
}

class ToLiquidNotObject
{
	public function toLiquid()
	{
		return STDIN;
	}
}

class CentsDrop extends Drop
{
	public function amount()
	{
		return new HundredCentes();
	}
}

class NoToLiquid
{
	public $answer = 42;

	private $name = null;

	public function name()
	{
		return 'example';
	}

	public function count()
	{
		return 1;
	}

	public function __toString()
	{
		return "forty two";
	}
}

class ToLiquidWrapper
{
	public $value = null;

	public function toLiquid()
	{
		return $this->value;
	}
}

class NestedObject
{
	public $property;
	public $value = -1;

	public function toLiquid()
	{
		// we intentionally made the value different so
		// that we could see where it is coming from
		return array(
			'property' => $this->property,
			'value' => 42,
		);
	}
}

class CountableObject implements \Countable
{
	public function count()
	{
		return 2;
	}
}

class ToArrayObject
{
	public $property;
	public $value = -1;

	public function toArray()
	{
		// we intentionally made the value different so
		// that we could see where it is coming from
		return array(
			'property' => $this->property,
			'value' => 42,
		);
	}
}

class GetSetObject
{
	public function field_exists($name)
	{
		return $name == 'answer';
	}

	public function get($prop)
	{
		if ($prop == 'answer') {
			return 42;
		}
	}
}

class GetSetMagic
{
	public function __get($prop)
	{
		if ($prop == 'prime') {
			return 2;
		}
	}
}


class HiFilter
{
	public function hi($value)
	{
		return $value . ' hi!';
	}
}

class GlobalFilter
{
	public function notice($value)
	{
		return "Global $value";
	}
}

class LocalFilter
{
	public function notice($value)
	{
		return "Local $value";
	}
}

class ContextTest extends TestCase
{
	/** @var Context */
	public $context;

	protected function setUp(): void
	{
		parent::setUp();

		$this->context = new Context();
	}

	public function testScoping()
	{
		$this->context->push();
		$this->assertNull($this->context->pop());
	}

	/**
	 */
	public function testNoScopeToPop()
	{
		$this->expectException(\Liquid\LiquidException::class);

		$this->context->pop();
	}

	/**
	 */
	public function testGetArray()
	{
		$this->expectException(\Liquid\LiquidException::class);

		$this->context->get(array());
	}

	public function testGetNotVariable()
	{
		$data = array(
			null => null,
			'null' => null,
			'true' => true,
			'false' => false,
			"'quoted_string'" => 'quoted_string',
			'"double_quoted_string"' => "double_quoted_string",
		);

		foreach ($data as $key => $expected) {
			$this->assertEquals($expected, $this->context->get($key));
		}

		$this->assertEquals(42.00, $this->context->get(42.00));
	}

	public function testVariablesNotExisting()
	{
		$this->assertNull($this->context->get('test'));
	}

	public function testVariableIsObjectWithNoToLiquid()
	{
		$this->context->set('test', new NoToLiquid());
		$this->assertEquals(42, $this->context->get('test.answer'));
		$this->assertEquals(1, $this->context->get('test.count'));
		$this->assertNull($this->context->get('test.invalid'));
		$this->assertEquals("forty two", $this->context->get('test'));
		$this->assertEquals("example", $this->context->get('test.name'));
	}

	public function testToLiquidNull()
	{
		$object = new ToLiquidWrapper();
		$this->context->set('object', $object);
		$this->assertNull($this->context->get('object.key'));
	}

	public function testToLiquidStringKeyMustBeNull()
	{
		$object = new ToLiquidWrapper();
		$object->value = 'foo';
		$this->context->set('object', $object);
		$this->assertNull($this->context->get('object.foo'));
		$this->assertNull($this->context->get('object.foo.bar'));
	}

	public function testNestedObject()
	{
		$object = new NestedObject();
		$object->property = new NestedObject();
		$this->context->set('object', $object);
		$this->assertEquals(42, $this->context->get('object.value'));
		$this->assertEquals(42, $this->context->get('object.property.value'));
		$this->assertNull($this->context->get('object.property.value.invalid'));
	}

	public function testToArrayObject()
	{
		$object = new ToArrayObject();
		$object->property = new ToArrayObject();
		$this->context->set('object', $object);
		$this->assertEquals(42, $this->context->get('object.value'));
		$this->assertEquals(42, $this->context->get('object.property.value'));
		$this->assertNull($this->context->get('object.property.value.invalid'));
	}

	public function testGetSetObject()
	{
		$this->context->set('object', new GetSetObject());
		$this->assertEquals(42, $this->context->get('object.answer'));
		$this->assertNull($this->context->get('object.invalid'));
	}

	public function testGetSetMagic()
	{
		$this->context->set('object', new GetSetMagic());
		$this->assertEquals(2, $this->context->get('object.prime'));
		$this->assertNull($this->context->get('object.invalid'));
	}

	public function testFinalVariableCanBeObject()
	{
		$this->context->set('test', (object) array('value' => (object) array()));
		$this->assertInstanceOf(\stdClass::class, $this->context->get('test.value'));
	}

	public function testVariables()
	{
		$this->context->set('test', 'test');
		$this->assertTrue($this->context->hasKey('test'));
		$this->assertFalse($this->context->hasKey('test.foo'));
		$this->assertEquals('test', $this->context->get('test'));

		// We add this text to make sure we can return values that evaluate to false properly
		$this->context->set('test_0', 0);
		$this->assertEquals('0', $this->context->get('test_0'));
	}

	public function testLengthQuery()
	{
		$this->context->set('numbers', array(1, 2, 3, 4));
		$this->assertEquals(4, $this->context->get('numbers.size'));
	}

	public function testStringLength()
	{
		$this->context->set('name', 'Foo Bar');
		$this->assertEquals(7, $this->context->get('name.size'));

		$this->context->set('name', 'テスト');
		$this->assertEquals(3, $this->context->get('name.size'));
	}

	public function testCountableLength()
	{
		$this->context->set('countable', new CountableObject());
		$this->assertEquals(2, $this->context->get('countable.size'));
	}

	public function testOverrideSize()
	{
		$this->context->set('hash', array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'size' => '5000'));
		$this->assertEquals(5000, $this->context->get('hash.size'));
	}

	public function testArrayFirst()
	{
		$this->context->set('array', array(11, 'jack', 43, 74, 5, 'tom'));
		$this->assertEquals(11, $this->context->get('array.first'));
	}

	public function testOverrideFirst()
	{
		$this->context->set('array', array(11, 'jack', 43, 'first' => 74, 5, 'tom'));
		$this->assertEquals(74, $this->context->get('array.first'));
	}

	public function testArrayLast()
	{
		$this->context->set('array', array(11, 'jack', 43, 74, 5, 'tom'));
		$this->assertEquals('tom', $this->context->get('array.last'));
	}

	public function testOverrideLast()
	{
		$this->context->set('array', array(11, 'jack', 43, 'last' => 74, 5, 'tom'));
		$this->assertEquals(74, $this->context->get('array.last'));
	}

	public function testDeepValueNotObject()
	{
		$this->context->set('example', array('foo' => new ToLiquidNotObject()));
		$this->assertNull($this->context->get('example.foo.bar'));
	}

	public function testHierchalData()
	{
		$this->context->set('hash', array('name' => 'tobi'));
		$this->assertEquals('tobi', $this->context->get('hash.name'));
	}

	public function testHierchalDataNoKey()
	{
		$this->context->set('hash', array('name' => 'tobi'));
		$this->assertNull($this->context->get('hash.no_key'));
	}

	public function testAddFilter()
	{
		$context = new Context();
		$context->addFilters(new HiFilter());
		$this->assertEquals('hi? hi!', $context->invoke('hi', 'hi?'));

		$context = new Context();
		$this->assertEquals('hi?', $context->invoke('hi', 'hi?'));

		$context->addFilters(new HiFilter());
		$this->assertEquals('hi? hi!', $context->invoke('hi', 'hi?'));
	}

	public function testOverrideGlobalFilter()
	{
		$template = new Template();
		$template->registerFilter(new GlobalFilter());

		$template->parse("{{'test' | notice }}");
		$this->assertEquals('Global test', $template->render());
		$this->assertEquals('Local test', $template->render(array(), new LocalFilter()));
	}

	public function testCallbackFilter()
	{
		$template = new Template();
		$template->registerFilter('foo', function ($arg) {
			return "Foo $arg";
		});

		$template->parse("{{'test' | foo }}");
		$this->assertEquals('Foo test', $template->render());
	}

	public function testAddItemInOuterScope()
	{
		$this->context->set('test', 'test');
		$this->context->push();
		$this->assertEquals('test', $this->context->get('test'));
		$this->context->pop();
		$this->assertEquals('test', $this->context->get('test'));
	}

	public function testAddItemInInnerScope()
	{
		$this->context->push();
		$this->context->set('test', 'test');
		$this->assertEquals('test', $this->context->get('test'));
		$this->context->pop();
		$this->assertNull($this->context->get('test'));
	}

	public function testMerge()
	{
		$this->context->merge(array('test' => 'test'));
		$this->assertEquals('test', $this->context->get('test'));

		$this->context->merge(array('test' => 'newvalue', 'foo' => 'bar'));
		$this->assertEquals('newvalue', $this->context->get('test'));
		$this->assertEquals('bar', $this->context->get('foo'));
	}

	public function testCents()
	{
		$this->context->merge(array('cents' => new HundredCentes()));
		$this->assertEquals(100, $this->context->get('cents'));
	}

	public function testNestedCents()
	{
		$this->context->merge(array('cents' => array('amount' => new HundredCentes())));
		$this->assertEquals(100, $this->context->get('cents.amount'));

		$this->context->merge(array('cents' => array('cents' => array('amount' => new HundredCentes()))));
		$this->assertEquals(100, $this->context->get('cents.cents.amount'));
	}

	public function testCentsThroughDrop()
	{
		$this->context->merge(array('cents' => new CentsDrop()));
		$this->assertEquals(100, $this->context->get('cents.amount'));
	}

	public function testCentsThroughDropNestedly()
	{
		$this->context->merge(array('cents' => array('cents' => new CentsDrop())));
		$this->assertEquals(100, $this->context->get('cents.cents.amount'));

		$this->context->merge(array('cents' => array('cents' => array('cents' => new CentsDrop()))));
		$this->assertEquals(100, $this->context->get('cents.cents.cents.amount'));
	}

	public function testGetNoOverride()
	{
		$_GET['test'] = '<script>alert()</script>';
		// Previously $_GET would override directly set values
		// It happend during class construction - we need to create a brand new instance right here
		$context = new Context();
		$context->set('test', 'test');
		$this->assertEquals('test', $context->get('test'));
	}

	public function testServerOnlyExposeWhitelistByDefault()
	{
		$_SERVER['AWS_SECRET_ACCESS_KEY'] = 'super_secret';

		$context = new Context();
		$this->assertNull($context->get('AWS_SECRET_ACCESS_KEY'));

		$context->set('AWS_SECRET_ACCESS_KEY', 'test');
		$this->assertEquals('test', $context->get('AWS_SECRET_ACCESS_KEY'));

		$_SERVER['FOO'] = 'foo';
		$_SERVER['BAR'] = 'bar';

		Liquid::set('SERVER_SUPERGLOBAL_WHITELIST', ['FOO']);

		$context = new Context();
		$this->assertEquals('foo', $context->get('FOO'));
		$this->assertNull($context->get('BAR'));

		$context->set('BAR', 'bar');
		$this->assertEquals('bar', $context->get('BAR'));
	}

	public function testServerExposedWhenRequested()
	{
		Liquid::set('EXPOSE_SERVER', true);

		$_SERVER['AWS_SECRET_ACCESS_KEY'] = 'super_secret';

		$context = new Context();
		$this->assertEquals('super_secret', $context->get('AWS_SECRET_ACCESS_KEY'));

		$context->set('AWS_SECRET_ACCESS_KEY', 'test');
		$this->assertEquals('super_secret', $context->get('AWS_SECRET_ACCESS_KEY'), '$_SERVER should take precedence in this case');
	}
}
