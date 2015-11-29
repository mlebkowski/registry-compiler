<?php

namespace Nassau\RegistryCompiler;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistryItemOptionsResolver extends OptionsResolver
{
	private $metadata = [];

	public function __construct(array $options)
	{
		// tag name:
		$this->setDefined('name');

		switch ($options['order']) {
			case RegistryTagOptionsResolver::ORDER_PRIORITY:
				$this->setDefaults([
					$options['priority_field'] => $options['default_priority'],
				]);
				$this->setAllowedTypes($options['priority_field'], 'integer');
				break;

			case RegistryTagOptionsResolver::ORDER_INDEXED:
				$this->setRequired($options['alias_field']);
				$this->setAllowedTypes($options['alias_field'], 'string');
				break;
		}

		if (null !== $options['metadata']) {
			foreach (preg_split('/,?\s+/', $options['metadata']) as $name) {
				if ($this->isDefined($name)) {
					throw new \InvalidArgumentException(sprintf(
						'You cannot use "%s" as metadata since it’s already defined', $name
					));
				}

				$this->metadata[] = $name;
				$this->setDefined($name);
			}
		}
	}

	public function getMetadata($values)
	{
		$metadata = array_intersect_key($values, array_flip($this->metadata));
		if (1 === sizeof($this->metadata)) {
			list ($metadata) = array_pad(array_values($metadata), 1, null);
		}

		return $metadata;
	}
}