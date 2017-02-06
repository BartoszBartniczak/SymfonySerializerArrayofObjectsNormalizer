<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;


use BartoszBartniczak\ArrayObject\ArrayObject;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\Person;

class ArrayObjectDeSerializationTest extends DeSerializationTestCase
{

    public function testEmptyArrayDeSerialization()
    {
        $data = $this->serializer->serialize(new ArrayObject(), 'json');
        $this->assertSame('[]', $data);
        $object = $this->serializer->deserialize($data, ArrayObject::class, 'json');
        $this->assertInstanceOf(ArrayObject::class, $object);
        $this->assertEquals(new ArrayObject(), $object);
    }

    public function testArrayOfObjectsDeSerialization()
    {

        $object = new ArrayObject([
            'einstein' => new Person("Einstein"),
            'tesla' => new Person("Tesla")
        ]);
        $data = $this->serializer->serialize($object, 'json');
        $this->assertSame('{"einstein":{"name":"Einstein"},"tesla":{"name":"Tesla"}}', $data);

        $deserializedObject = $this->serializer->deserialize($data, ArrayObject::class . '<' . Person::class . '>', 'json');
        $this->assertEquals($object, $deserializedObject);
    }

}
