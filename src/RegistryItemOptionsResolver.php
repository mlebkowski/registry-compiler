<?php

namespace Nassau\RegistryCompiler;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistryItemOptionsResolver extends OptionsResolver
{

	public function __construct(array $options)
	{
		switch ($options['order']) {
			case RegistryTagOptionsResolver::ORDER_PRIORITY:
				$this->setDefaults([
					$options['priority_field'] => $options['default_priority'],
				]);
				$this->setAllowedTypes($options['priority_field'], 'int');
				break;

			case RegistryTagOptionsResolver::ORDER_INDEXED:
				$this->setRequired($options['alias_field']);
				$this->setAllowedTypes($options['alias_field'], 'string');
				break;
		}

	}
}
