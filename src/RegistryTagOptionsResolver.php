<?php

namespace Nassau\RegistryCompiler;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistryTagOptionsResolver extends OptionsResolver
{
	const ORDER_NATURAL = 'natural';
	const ORDER_PRIORITY = 'priority';
	const ORDER_INDEXED = 'indexed';

	public function __construct()
	{
		$this->setDefaults([
			// this method will be used on collection
			'method' => 'set',
			// use one call with the whole collection, or one call per item?
			'use_collection' => false,
			// add items in this order. natural — as they go; priority — sort by priority; indexed - use hashmap
			'order' => self::ORDER_NATURAL,
			// take this field from tag to determine priority
			'priority_field' => 'priority',
			'default_priority' => 1024,
			// use this field as item name
			'alias_field' => 'alias',
			// require item to be instance of
			'class' => null,
		]);

		$this->setRequired('tag');
		$this->setAllowedValues('order', [self::ORDER_NATURAL, self::ORDER_PRIORITY, self::ORDER_INDEXED]);
		$this->setAllowedTypes('use_collection', 'bool');

	}

}
