<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures;

use BartoszBartniczak\ArrayObject\ArrayOfObjects;

class PersonArray extends ArrayOfObjects
{

    public function __construct($input = null, $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct(Person::class, $input, $flags, $iterator_class);
    }

}