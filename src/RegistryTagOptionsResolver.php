<?php

namespace Nassau\RegistryCompiler;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistryTagOptionsResolver
{
	const ORDER_NATURAL = 'natural';
	const ORDER_PRIORITY = 'priority';
	const ORDER_INDEXED = 'indexed';

	/**
	 * @var OptionsResolver
	 */
	private $resolver;

	public function __construct()
	{
		$this->resolver = (new OptionsResolver)->setDefaults([
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
		])
			->setRequired('tag')
			->setAllowedValues('order', [self::ORDER_NATURAL, self::ORDER_PRIORITY, self::ORDER_INDEXED])
			->setAllowedTypes('use_collection', ['bool', 'string'])
			->setNormalizer('use_collection', function (Options $options, $value) {
				if (!$options['method']) {
					$value = $value ?: true;
				}

				return is_string($value) ? $value : ($value ? 'collection' : false);
			});

	}

	public function resolve(array $options)
	{
		return $this->resolver->resolve($options);
	}

}
