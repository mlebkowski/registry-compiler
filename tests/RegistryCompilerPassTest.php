<?php

namespace Nassau\RegistryCompiler;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RegistryCompilerPassTest extends \PHPUnit_Framework_TestCase
{
	public function testUsesMethod()
	{
		$container = new ContainerBuilder();
		$container->addCompilerPass(new RegistryCompilerPass());
		(new YamlFileLoader($container, new FileLocator([__DIR__])))->load('services.yml');

		$container->compile();

		$this->assertFalse($container->has('main_registry.collection'));

		/** @var \ArrayObject $main */
		$main = $container->get('main_registry');

		$this->assertEquals(range(0, 2), array_keys($main->getArrayCopy()));

		list ($dates, $values, $names) = $main;

		$this->assertGreaterThan(reset($dates), end($dates));

		$this->assertEquals(3, sizeof($dates));

		$this->assertEquals(substr(M_PI, 0, 10), (string)reset($values));
		$this->assertEquals(substr(M_LN2, 0, 9), (string)end($values));

		$this->assertEquals("#00FF00", (string)$names['green']);
		$this->assertEquals("#0000FF", (string)$names['blue']);
		$this->assertEquals("#FF0000", (string)$names['red']);

	}
}
