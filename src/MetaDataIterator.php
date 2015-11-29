<?php

namespace Nassau\RegistryCompiler;

class MetaDataIterator extends \ArrayObject
{
	private $metadata;

	public function __construct()
	{
		$this->metadata = new \SplObjectStorage();
	}

	public function offsetSet($index, $value, $metadata = null)
	{
		parent::offsetSet($index, $value);
		$this->metadata->offsetSet($value, $metadata);
	}

	public function offsetUnset($index)
	{
		if ($this->offsetExists($index)) {
			$this->metadata->offsetUnset($this->offsetGet($index));
		}
		parent::offsetUnset($index);
	}

	public function getMetaData($object)
	{
		return $this->metadata->offsetGet($object);
	}

}