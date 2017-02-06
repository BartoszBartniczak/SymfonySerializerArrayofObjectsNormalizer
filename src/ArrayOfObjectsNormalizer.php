<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;


use BartoszBartniczak\ArrayObject\ArrayObject;
use BartoszBartniczak\ArrayObject\ArrayOfObjects;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class ArrayOfObjectsNormalizer extends ArrayObjectNormalizer
{
    const PROPERTY_CLASSNAME = 'className';
    const PROPERTY_STORAGE = 'storage';

    /**
     * @var ArrayOfObjects;
     */
    protected $constructors;

    public function __construct()
    {
        parent::__construct();
        $this->constructors = new ArrayOfObjects(\Closure::class);
    }

    protected function checkInstanceOfArrayObject($type)
    {
        $class = $this->extractClass($type);

        if ($class === ArrayOfObjects::class) {
            return true;
        }

        if (!class_exists($class)) {
            return false;
        }

        $parents = class_parents($class);

        return isset($parents[ArrayOfObjects::class]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $this->checkRequirements($data, $class);

        $serializer = $this->serializer;

        $builtinType = isset($context['key_type']) ? $context['key_type']->getBuiltinType() : null;

        $subclass = $data[self::PROPERTY_CLASSNAME];

        if (isset($data[self::PROPERTY_STORAGE])) {
            foreach ($data[self::PROPERTY_STORAGE] as $key => $value) {
                if (null !== $builtinType && !call_user_func('is_' . $builtinType, $key)) {
                    throw new UnexpectedValueException(sprintf('The type of the key "%s" must be "%s" ("%s" given).', $key, $builtinType, gettype($key)));
                }

                $data[self::PROPERTY_STORAGE][$key] = $serializer->denormalize($value, $subclass, $format, $context);
            }
        }

        $className = $this->extractClass($class);
        return $this->createObject($data, $className);
    }

    /**
     * @param $data
     * @param $className
     * @return mixed
     */
    protected function createObject(array $data, string $className)
    {
        $function = $this->findConstructor($className);

        return $function($data, $className);
    }

    protected function findConstructor(string $className): \Closure
    {
        if ($this->constructors->offsetExists($className)) {
            return $this->constructors->offsetGet($className);
        }

        return function (array $data, string $className) {
            return new $className($data[self::PROPERTY_CLASSNAME], $data[self::PROPERTY_STORAGE]??[]);
        };
    }

    public function addConstructorForClass(string $class, \Closure $closure)
    {
        $this->constructors->offsetSet($class, $closure);
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ArrayOfObjects;
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /* @var $object ArrayOfObjects */
        return $this->normalizer->normalize([
            self::PROPERTY_CLASSNAME => $object->getClassName(),
            self::PROPERTY_STORAGE => $object->getArrayCopy()
        ]);
    }

}