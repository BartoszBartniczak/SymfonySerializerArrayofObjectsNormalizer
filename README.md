BartoszBartniczak/SymfonySerializerArrayofObjectsNormalizer [![Build Status](https://travis-ci.org/BartoszBartniczak/SymfonySerializerArrayofObjectsNormalizer.svg?branch=master)](https://travis-ci.org/BartoszBartniczak/SymfonySerializerArrayofObjectsNormalizer) [![Coverage Status](https://coveralls.io/repos/github/BartoszBartniczak/SymfonySerializerArrayofObjectsNormalizer/badge.svg?branch=master)](https://coveralls.io/github/BartoszBartniczak/SymfonySerializerArrayofObjectsNormalizer?branch=master)
===================================================
ArrayOfObjects Normalizer for Symfony/Serializer component. This normalizer allows to (de-)serialize ArrayObjects and ArrayOfObjects from BartoszBartniczak\ArrayOfObjects library.
--------------------------------------------------

### External links:
1. [BartoszBartniczak\ArrayOfObjects](https://github.com/BartoszBartniczak/ArrayOfObjects)
2. [BartoszBartniczak\SymfonySerializerArrayObjectNormalizer](https://github.com/BartoszBartniczak/SymfonySerializerArrayObjectNormalizer)

### Configuration

```php
<?php

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer;
use BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer;

$normalizers =[
     new ArrayOfObjectsNormalizer(), //add this normalizer for ArrayOfObjects (de-)serialization
    new ArrayObjectNormalizer(), //add this normalizer for ArrayObject (de-)serialization
    new ObjectNormalizer(),
];

$encoders = [
    new JsonEncoder(),
];

$serializer = new Serializer($normalizers, $encoders);
```

### ArrayOfObjects (de-)serialization

```php
use BartoszBartniczak\ArrayObject\ArrayOfObjects;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\Person;

$arrayOfObjects = new ArrayOfObjects(Person::class, [
            'einstein' => new Person('Albert Einstein'),
            'tesla' => new Person('Nikola Tesla')
        ]);

$json = $serializer->serialize($arrayOfObjects, 'json');
```

In the $json variable you should contains now this JSON document:

```json
{
  "className": "BartoszBartniczak\\SymfonySerializer\\Normalizer\\Fixtures\\Person",
  "storage": {
    "einstein": {
      "name": "Albert Einstein"
    },
    "tesla": {
      "name": "Nikola Tesla"
    }
  }
}
```

Now you can deserialize this object:

```php
$serializer->deserialize($json, ArrayOfObjects::class, 'json');
```

> You do not need to define the type of elements. The `className` parameter is used for de-serialization.

#### Subclasses (extending the ArrayofObjects class)

This Normalizer supports inheritance of objects. You can extend the `ArrayofObjects` (e.g. for adding some methods) and this Normalizer still will be able to (de-)serialize objects.

```php
use BartoszBartniczak\ArrayObject\ArrayOfObjects;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\Person;

class ArrayOfObjectsSubclass extends ArrayOfObjects{
    
}

$arrayOfObjects = new ArrayOfObjectsSubclass(Person::class, [
            'einstein' => new Person('Albert Einstein'),
            'tesla' => new Person('Nikola Tesla')
        ]);

$json = $serializer->serialize($arrayOfObjects, 'json');

$serializer->deserialize($json, ArrayOfObjectsSubclass::class, 'json');
```

#### Subclasses with custom constructor

Very often, we create arrays that contain a specific type of object e.g. `PersonArray`, which can contain only `Person` objects.

```php
new ArrayOfObjects(Person::class)
```

There is no reason to define the type, every time you create the `ArrayOfObjects` which should contain `Person` objects.

You can create a class which extends `ArrayOfObjects`:

```php
class PersonArray extends ArrayOfObjects{

    public function __construct($input = null, $flags = 0, $iterator_class = "ArrayIterator")
        {
            parent::__construct(Person::class, $input, $flags, $iterator_class);
        }

}
```

The only problem is the deserialization process, which uses constructor to build the object. So, you need to define a `\Closure` function, which will be able to create your custom array.

For above example, it could look like this:

```php
$arrayOfObjectsNormalizer = new ArrayOfObjectsNormalizer();
$arrayOfObjectsNormalizer->addConstructorForClass(PersonArray::class, function (array $data, string $className){
    return new PersonArray($data[ArrayOfObjectsNormalizer::PROPERTY_STORAGE]);
});
```

Then you need to register this normalizer.

```php
$normalizers =[
    $arrayOfObjectsNormalizer,
    new ArrayObjectNormalizer(),
    new ObjectNormalizer(),
];

$encoders = [
    new JsonEncoder(),
];

$serializer = new Serializer($normalizers, $encoders);
```

Now, you can (de-)serialize `PersonArray`.

```php
$object = new PersonArray([
    'einstein' => new Person('Albert Einstein'),
    'tesla' => new Person('Nikola Tesla')
]);
$json = $serializer->serialize($object, 'json');

$serializer->deserialize($json, PersonArray::class, 'json');
```




 

