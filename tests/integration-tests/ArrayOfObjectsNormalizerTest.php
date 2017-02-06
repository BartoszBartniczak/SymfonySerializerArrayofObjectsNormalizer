<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;


use BartoszBartniczak\ArrayObject\ArrayOfObjects;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\ArrayOfObjectsSubclass;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\Person;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\PersonArray;

class ArrayOfObjectsNormalizerTest extends DeSerializationTestCase
{

    public function testDeSerializeEmptyArray()
    {
        $object = new ArrayOfObjects(Person::class);
        $json = $this->serializer->serialize($object, 'json');

        $this->assertSame('{"className":"' . addslashes(Person::class) . '","storage":[]}', $json);

        $deserializedObject = $this->serializer->deserialize($json, ArrayOfObjects::class, 'json');
        $this->assertInstanceOf(ArrayOfObjects::class, $deserializedObject);
        $this->assertEquals($object, $deserializedObject);
    }

    public function testDeSerializeArrayOfObjects()
    {

        $object = new ArrayOfObjects(Person::class, [
            'einstein' => new Person('Albert Einstein'),
            'tesla' => new Person('Nikola Tesla')
        ]);
        $json = $this->serializer->serialize($object, 'json');

        $this->assertSame('{"className":"' . addslashes(Person::class) . '","storage":{"einstein":{"name":"Albert Einstein"},"tesla":{"name":"Nikola Tesla"}}}', $json);

        $deserializedObject = $this->serializer->deserialize($json, ArrayOfObjects::class, 'json');
        $this->assertInstanceOf(ArrayOfObjects::class, $deserializedObject);
        $this->assertEquals($object, $deserializedObject);
    }

    public function testDeSerializeSubclass()
    {
        $object = new ArrayOfObjectsSubclass(Person::class, [
            'einstein' => new Person('Albert Einstein'),
            'tesla' => new Person('Nikola Tesla')
        ]);
        $json = $this->serializer->serialize($object, 'json');

        $this->assertSame('{"className":"' . addslashes(Person::class) . '","storage":{"einstein":{"name":"Albert Einstein"},"tesla":{"name":"Nikola Tesla"}}}', $json);

        $deserializedObject = $this->serializer->deserialize($json, ArrayOfObjectsSubclass::class, 'json');
        $this->assertInstanceOf(ArrayOfObjects::class, $deserializedObject);
        $this->assertEquals($object, $deserializedObject);
    }

    public function testDeSerializeSubclassWithCustomConstructor()
    {
        $object = new PersonArray([
            'einstein' => new Person('Albert Einstein'),
            'tesla' => new Person('Nikola Tesla')
        ]);
        $json = $this->serializer->serialize($object, 'json');

        $this->assertSame('{"className":"' . addslashes(Person::class) . '","storage":{"einstein":{"name":"Albert Einstein"},"tesla":{"name":"Nikola Tesla"}}}', $json);

        $deserializedObject = $this->serializer->deserialize($json, PersonArray::class, 'json');
        $this->assertInstanceOf(PersonArray::class, $deserializedObject);
        $this->assertEquals($object, $deserializedObject);
    }

}
