<?php
/**
 * Created by PhpStorm.
 * User: puck
 * Date: 28/11/15
 * Time: 01:39
 */

namespace Nassau\RegistryCompiler;


class ItemCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testPriorityQueue()
	{
		$iterator = new ItemCollection([
			'priority_field' => 'priority',
			'order' => RegistryTagOptionsResolver::ORDER_PRIORITY
		]);

		$iterator->add("A", ["priority" => 10]);

		$queue = $iterator->getIterator();
		$this->assertInstanceOf(\SplPriorityQueue::class, $queue);
		$queue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

		$this->assertEquals(["data" => "A", "priority" => 10], $queue->extract());
	}

	public function testIndexedMap()
	{
		$iterator = new ItemCollection([
			'alias_field' => 'name',
			'order' => RegistryTagOptionsResolver::ORDER_INDEXED,
		]);

		$iterator->add("A", ["name" => "blue"]);

		$this->assertEquals(['blue' => "A"], $iterator->getIterator()->getArrayCopy());
	}

	public function testList()
	{
		$iterator = new ItemCollection([
			'order' => RegistryTagOptionsResolver::ORDER_NATURAL,
		]);

		$iterator->add("A", []);
		$iterator->add("B", []);

		$this->assertEquals(["A", "B"], $iterator->getIterator()->getArrayCopy());
	}
}
