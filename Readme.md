# Tag your services to associate them with one common registry

Add the `nassau.registry` tag to a service and it will receive a collection of other services tagged with the name you chose. Similar to the event dispatcher, cache warmers, twig extensions, etc.


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
