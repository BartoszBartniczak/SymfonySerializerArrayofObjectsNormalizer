<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;

use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\PersonArray;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DeSerializationTestCase extends TestCase
{

    /**
     * @var Serializer
     */
    protected $serializer;

    protected function setUp()
    {

        $arrayOfObjectsNormalizer = new ArrayOfObjectsNormalizer();
        $arrayOfObjectsNormalizer->addConstructorForClass(PersonArray::class, function (array $data, string $className){
            return new PersonArray($data[ArrayOfObjectsNormalizer::PROPERTY_STORAGE]);
        });

        $normalizers =[
            $arrayOfObjectsNormalizer,
            new ArrayObjectNormalizer(),
            new ObjectNormalizer(),
        ];

        $encoders = [
            new JsonEncoder(),
        ];

        $this->serializer = new Serializer($normalizers, $encoders);

    }

}
