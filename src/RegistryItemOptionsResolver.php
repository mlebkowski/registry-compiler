<?php

namespace Nassau\RegistryCompiler;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistryItemOptionsResolver
{

	/**
	 * @var OptionsResolver
	 */
	private $resolver;

	public function __construct(array $options)
	{
		$this->resolver = new OptionsResolver;

		switch ($options['order']) {
			case RegistryTagOptionsResolver::ORDER_PRIORITY:
				$this->resolver->setDefaults([
					$options['priority_field'] => $options['default_priority'],
				]);
				$this->resolver->setAllowedTypes($options['priority_field'], 'int');
				break;

			case RegistryTagOptionsResolver::ORDER_INDEXED:
				$this->resolver->setRequired($options['alias_field']);
				$this->resolver->setAllowedTypes($options['alias_field'], 'string');
				break;
		}

	}

	public function resolve(array $options)
	{
		return $this->resolver->resolve($options);
	}
}
