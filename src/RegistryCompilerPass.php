<?php

namespace Nassau\RegistryCompiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegistryCompilerPass implements CompilerPassInterface
{
	const REGISTRY_TAG_NAME = 'nassau.registry';

	/**
	 * @var string
	 */
	private $tagName;

	/**
	 * RegistryCompilerPass constructor.
	 *
	 * @param string $tagName — you may override the main tag name the library searches for
	 */
	public function __construct($tagName = self::REGISTRY_TAG_NAME)
	{
		$this->tagName = $tagName;
	}

	/**
	 * You can modify the container here before it is dumped to PHP code.
	 *
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container)
	{
		$optionsResolver = new RegistryTagOptionsResolver();

		foreach ($container->findTaggedServiceIds($this->tagName) as $id => $tags) {
			foreach ($tags as $tag) {
				try {
					$tagOptions = $optionsResolver->resolve($tag);
				} catch (\Exception $e) {
					throw new \RuntimeException("Unable to configure $id repository", 0, $e);
				}

				$this->buildRegistry($container, $id, $tagOptions);
			}
		}
	}

	private function buildRegistry(ContainerBuilder $container, $registryId, array $tagOptions)
	{
		$optionsResolver = new RegistryItemOptionsResolver($tagOptions);

		$collection = new ItemCollection($tagOptions);

		foreach ($container->findTaggedServiceIds($tagOptions['tag']) as $id => $tags) {
			foreach ($tags as $tag) {

				try {
					$options = $optionsResolver->resolve($tag);
				} catch (\Exception $e) {
					throw new \RuntimeException("Unable to configure $id item for $registryId repository", 0, $e);
				}

				if ($tagOptions['class']) {
					$itemDefinition = $container->getDefinition($id);
					if (false === is_a($itemDefinition->getClass(), $tagOptions['class'], true)) {
						throw new \RuntimeException(
							"Items for $registryId registry are required to implement"
							. " '{$tagOptions['class']}' interface, '{$itemDefinition->getClass()}' given"
						);
					}
				}

				$collection->add([$id, $optionsResolver->getMetadata($options)], $options);
			}

		}

		$targetId = $registryId;
		$methodName = $tagOptions['method'];
		$useMetadata = null !== $tagOptions['metadata'];

		if ($tagOptions['use_collection']) {
			$targetId = "{$registryId}.collection";

			$collectionClass = $useMetadata ? MetaDataIterator::class : \ArrayObject::class;
			$container->setDefinition($targetId, new Definition($collectionClass))->setPublic(false);
			$container->getDefinition($registryId)->addMethodCall($methodName, [new Reference($targetId)]);

			$methodName = 'offsetSet';
		}

		$definition = $container->getDefinition($targetId);
		foreach ($collection->getIterator() as $key => $item) {
			list ($id, $metadata) = $item;
			$arguments = [$key, new Reference($id)];

			if ($useMetadata) {
				array_push($arguments, $metadata);
			}

			$definition->addMethodCall($methodName, $arguments);
		}
	}

}