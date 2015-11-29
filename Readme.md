# Tag your services to associate them with one common registry

## Installation

```
composer require nassau/registry-compiler
```

Add the compiler pass to one of your bundles:

```php
# somewhere inside AcmeBundle.php

	public function build(ContainerBuilder $container)
	{
		$container->addCompilerPass(new RegistryCompilerPass());
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

            # Every tagged service will be passed to this method. Its signature needs to be:
            #
            # `public function addFooBarProvider($index, $foobar)`
            #
            # This is an optional attribute, defaults to "set", as per symfonys conventions

              method: addFooBarProvider

            # Instead of having each tagged service passed to your setter, you may choose to
            # get an iterator with all the objects in one call. This will be an `ArrayObject`
            # and your signature needs to be
            #
            # `public function addFooBarProviders(\ArrayObject $items)`

              use_collection: false

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

            # Define additional metadata on item’s tag. See below for examples

              metadata: null

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

## Collecting additional metadata

Sometimes you need some additional configuration on your services. Use `metadata` attribute to
define a space-separated list of additional attributes allowed on matched items tags. They cannot
conflict with any other attributes (such as `name`, `priority` or `alias`, etc), and they are not
validated in any way (use your own `OptionResolver` if needed).

If a single attribute is defined, its value or null will be passed as a third argument to the
setter method. If multiple, expect an array with any of the used attributes.

### Single value

The `set` method on the `formatter` service will be called once with a scalar value:

```php
set(0, $locale_item, "en")
```

```yaml
services:
  formatter:
    class: …
    tags:
      - name: nassau.registry
        tag: formatter.provider
        metadata: locale
        method: set

  locale_item:
    class: …
    public: false
    tags:
      - name: formatter.provider
        locale: en
```

### Multiple values

In this case, there will be two calls added to the `movie_quotes` service:

```php
addQuote("The Hitchhiker’s Guide to the Galaxy", $deep_thought, [
    'author' => "Deep Thought",
    'quote' => "the answer to life the universe and everything",
    'answer' => 42
]);
addQuote("unknown", $dont_panic, ['quote' => "Don’t Panic"]);
```

```yaml
services:
  movie_quotes:
    class: …
    tags:
      - name: nassau.registry
        tag: movie_quotes
        order: indexed
        method: addQuote

        # allow any of the three metadata values to be present:

        metadata: "author, quote, answer"

  deep_thought:
    class: …
    public: false
    tags:
      - name: movie_quotes
        alias: "The Hitchhiker’s Guide to the Galaxy"

        # below are metadata values:

        author: "Deep Thought"
        quote: "the answer to life the universe and everything"
        answer: 42

  dont_panic:
    class: …
    public: false
    tags:
      - name: movie_quotes
        alias: "unknown"

        # value & author missing, only ["quote" => …] will be passed

        quote: "Don’t Panic"
```