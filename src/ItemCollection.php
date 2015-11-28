<?php

namespace Nassau\RegistryCompiler;

class ItemCollection implements \IteratorAggregate
{
	/**
	 * @var \Iterator|\SplPriorityQueue
	 */
	private $iterator;

	private $setter;

	public function __construct($tagOptions)
	{
		switch ($tagOptions['order']) {
			case RegistryTagOptionsResolver::ORDER_PRIORITY:
				$this->iterator = new \SplPriorityQueue();
				$this->setter = function ($item, $options) use ($tagOptions) {
					$priority = $options[$tagOptions['priority_field']];
					$this->iterator->insert($item, $priority);
				};
				return;

			case RegistryTagOptionsResolver::ORDER_NATURAL:
				$this->iterator = new \ArrayObject();
				$this->setter = function ($item) {
					$this->iterator->append($item);
				};
				return;

			case RegistryTagOptionsResolver::ORDER_INDEXED:
				$this->iterator = new \ArrayObject();
				$this->setter = function ($item, $options) use ($tagOptions) {
					$index = $options[$tagOptions['alias_field']];
					$this->iterator->offsetSet($index, $item);
				};
				return;
		}

		throw new \RuntimeException('Order type mismatch: ' . $tagOptions['order']);
	}

	public function getIterator()
	{
		return $this->iterator;
	}

	public function add($item, array $options)
	{
		call_user_func($this->setter, $item, $options);
	}

}