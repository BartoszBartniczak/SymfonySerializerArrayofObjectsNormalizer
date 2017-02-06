<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;


use BartoszBartniczak\ArrayObject\ArrayObject;
use BartoszBartniczak\ArrayObject\ArrayOfObjects;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\ArrayOfObjectsSubclass;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\Person;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\PersonArray;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\SerializerDeserializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Tests\Fixtures\AbstractNormalizerDummy;


class ArrayOfObjectsNormalizerTest extends TestCase
{
    /**
     * @var ArrayOfObjectsNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        parent::setUp();
        $this->normalizer = new ArrayOfObjectsNormalizer();
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::__construct
     */
    public function testConstructor()
    {

        $this->assertInstanceOf(ArrayObjectNormalizer::class, $this->normalizer);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::supportsDenormalization
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::checkInstanceOfArrayObject
     */
    public function testSupportsDenormalization()
    {

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'supportsDenormalization'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface->expects($this->exactly(2))
            ->method('supportsDenormalization')
            ->with('{}', Person::class)
            ->willReturn(true);
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $this->normalizer->setSerializer($serializerDeserializerInterface);

        $this->assertTrue($this->normalizer->supportsDenormalization('{}', ArrayOfObjects::class));
        $this->assertTrue($this->normalizer->supportsDenormalization('{}', ArrayOfObjects::class . '<' . Person::class . '>'));
        $this->assertTrue($this->normalizer->supportsDenormalization('{}', ArrayOfObjectsSubclass::class));
        $this->assertTrue($this->normalizer->supportsDenormalization('{}', ArrayOfObjectsSubclass::class . '<' . Person::class . '>'));

        $this->assertFalse($this->normalizer->supportsDenormalization('{}', ArrayObject::class));
        $this->assertFalse($this->normalizer->supportsDenormalization('{}', \ArrayObject::class));
        $this->assertFalse($this->normalizer->supportsDenormalization('{}', 'int'));
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::denormalize
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::createObject
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::findConstructor
     */
    public function testDenormalizeEmptyArray()
    {
        $arrayOfObjects = new ArrayOfObjects(Person::class);

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */
        $this->normalizer->setSerializer($serializerDeserializerInterface);

        $object = $this->normalizer->denormalize(["className" => Person::class], ArrayOfObjects::class, 'json');

        $this->assertEquals($arrayOfObjects, $object);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::denormalize
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::createObject
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::findConstructor
     */
    public function testDenormalize()
    {
        $arrayOfObjects = new ArrayOfObjects(Person::class, [new Person('Alfred')]);

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'denormalize'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface->expects($this->at(0))
            ->method('denormalize')
            ->with(["name" => "Alfred"])
            ->willReturn(new Person('Alfred'));
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */
        $this->normalizer->setSerializer($serializerDeserializerInterface);

        $object = $this->normalizer->denormalize(["className" => Person::class, 'storage' => [
            ["name" => "Alfred"]
        ]], ArrayOfObjects::class, 'json');

        $this->assertEquals($arrayOfObjects, $object);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::supportsNormalization
     */
    public function testSupportsNormalization()
    {

        $this->assertTrue($this->normalizer->supportsNormalization(new ArrayOfObjects(Person::class)));
        $this->assertTrue($this->normalizer->supportsNormalization(new ArrayOfObjectsSubclass(Person::class)));

        $this->assertFalse($this->normalizer->supportsNormalization(new ArrayObject()));
        $this->assertFalse($this->normalizer->supportsNormalization(new \ArrayObject()));
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::normalize
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::setNormalizer
     */
    public function testNormalize()
    {

        $object = new ArrayOfObjects(Person::class);

        $normalizerInterface = $this->getMockBuilder(NormalizerInterface::class)
            ->setMethods([
                'normalize'
            ])
            ->getMockForAbstractClass();
        $normalizerInterface
            ->expects($this->once())
            ->method('normalize')
            ->with([
                ArrayOfObjectsNormalizer::PROPERTY_CLASSNAME => Person::class,
                ArrayOfObjectsNormalizer::PROPERTY_STORAGE => []
            ]);
        /* @var $normalizerInterface NormalizerInterface */

        $this->normalizer->setNormalizer($normalizerInterface);
        $this->normalizer->normalize($object, 'json');
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::findConstructor
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::addConstructorForClass
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::denormalize
     */
    public function testFindConstructor()
    {

        $this->normalizer->addConstructorForClass(PersonArray::class, function (array $data, string $className) {
            return new PersonArray($data[ArrayOfObjectsNormalizer::PROPERTY_STORAGE]);
        });

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'denormalize'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface->expects($this->at(0))
            ->method('denormalize')
            ->with(["name" => "Alfred"])
            ->willReturn(new Person('Alfred'));
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */
        $this->normalizer->setSerializer($serializerDeserializerInterface);

        $object = $this->normalizer->denormalize(["className" => Person::class, 'storage' => [
            ["name" => "Alfred"]
        ]], PersonArray::class, 'json');

        $this->assertInstanceOf(PersonArray::class, $object);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayOfObjectsNormalizer::denormalize
     */
    public function testDenormalizeThrowsUnexpectedValueExceptionIfKeyIsNotBuiltInType()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The type of the key "alfred" must be "int" ("string" given).');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */
        $this->normalizer->setSerializer($serializerDeserializerInterface);

        $this->normalizer->denormalize(["className" => Person::class, 'storage' => ['alfred' => ["name" => "Alfred"]]], ArrayOfObjects::class, 'json', ['key_type' => new class()
        {
            public function getBuiltinType()
            {
                return "int";

            }
        }]);
    }

}
