# Tag your services to associate them with one common registry

Add the `nassau.registry` tag to a service and it will receive a collection of other services tagged with the name you chose. Similar to the event dispatcher, cache warmers, twig extensions, etc. 

If you ever created a `CompilerPass` and used `$containerBuilder->findTaggedServiceIds()` inside you could probably replace it with `nassau/registry-compiler`.

## Installation

```
composer require nassau/registry-compiler
```

Add the compiler pass to one of your bundles. Since it may be used in some of your dependencies,
the recommended way is to use the `register` method of the Pass to ensure only one instance is
registered.

```php
# somewhere inside AcmeBundle.php

	public function build(ContainerBuilder $container)
	{
		(new RegistryCompilerPass)->register($container);
	}
```

## Usage

```yaml
services:
    foobar.manager:
        class: FooBaringManager
        tags:
            -
            # This is the starting point for the lib:

              name: nassau.registry

            # Collect all services tagged with this value:
            #
            # This is a required attribute

               tag: foobaring.provider

            # Every tagged service will be passed to this method. It’s signature needs to be:
            #
            # `public function addFooBarProvider($index, $foobar)`
            #
            # This is an optional attribute, defaults to "set", as per symfonys conventions
            #
            # Setting this to null/empty value will automatically enable the `use_collection`
            # option below.

              method: addFooBarProvider

            # Instead of having each tagged service passed to your setter, you may choose to
            # get an iterator with all the objects in one call. This will be an `ArrayObject`
            # and your signature needs to be
            #
            # `public function addFooBarProviders(\ArrayObject $items)`

              use_collection: false

            # You may decide to use a constructor injection instead of a setter when using the
            # above option. In this case set the method to null, so the compiler won’t add any
            # `calls` to your service definition. Instead, setup your dependency yourself:
            #
            # foo:
            #   arguments: [ @foo.providers ]
            #   tags:
            #     - { name: nassau.registry, use_collection: providers, method: ~ }
            #

              use_collection: collection

            # Choose an order in which the services will be provided
            #   natural (default): just the way the container returns them
            #   priority: define a priority field to control the order (high to low)
            #   indexed: don’t order them, you’ll be using a key => value anyway

              order: indexed

            # If you choose the 'priority' order, you may override the name of the
            # attribute used to determine the items priority. You may also set the default
            # value if the field isn’t set on an item.
            #
            # Default: priority

              priority_field: weight
              default_priority: 1024

            # If you choose the 'indexed' order, you may override the name of the
            # attribute used to determine the items key. This attribute is required
            # to be present on the related service tag.
            #
            # Default: alias

              alias_field: foobar_name

            # And finally, you’d probably like to restrict services to be of
            # certain class or implementing an interface:

              class: FooBaringInterface

    foobar.provider.cache:
        class: CachedFooBaring
        public: false
        tags:
            -
            # First match the registrys tag name:

              name: foobaring.provider

            # Then provide any details needed. In this case, the name is required since
            # the order is set to "indexed". The "alias" attribute was set to "foobar_name"

              foobar_name: cache

            # This will result in calling method `addFooBarProvider` on `foobar.manager`
            # service with parameters: "cache" and `foobar.provider.cache` instance.
```

## Example

Given an interface:

```php
interface FooBarInterface {
    public function makeFooBar($input);
}
```

You may need to split the implementation across multiple classes. Maybe it’s something like a monolog processor — each one does it’s thing and moves on. To simplify the usage, you create a chain implementation so the classes using FooBarInterface knows nothing about the details:

```
class ChainFooBar implements FooBarInterface {

    /** @var FooBarInterface[] **/
    private $collection = [];

    public function addFooBar($name, FooBarInterface $fooBar) {
        $this->collection[$name] = $fooBar;
    }

    public function makeFooBar($input) {
        foreach ($this->collection as $fooBar) {
            $fooBar->makeFooBar($input);
        }
    }
}
```

So far so good. So now you just need to wire every implementations together using the container. As long as you have a fixed number of implementations you may just use calls:

```yml
services:
  foo_bar.chain:
    class: ChainFooBar
    calls: 
      - ['addFooBar', [ 'alpha', '@foo_bar.alpha' ] ]
      - ['addFooBar', [ 'bravo', '@foo_bar.bravo' ] ]
```

But this gets messy and there is no easy way to add more implementations, especially if you’re only making a library / architecture and it’s up to the developer to make the implementations.

This is where `nassau.registry` comes into play. Instead of manually connecting the implementations to the chain, you register a tag, so any service can hook itself up:

```yaml
services:
  foo_bar.chain:
    class: ChainFooBar
    tags:
      - name: nassau.registry
        tag: foo_bar
        method: addFooBar
        order: indexed
        class: FooBarInterface

  foo_bar.alpha:
    class: FooBarAlpha
    tags:
      - name: foo_bar
        alias: alpha
```
