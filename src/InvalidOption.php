<?php

namespace XT\ExternalId;

use Exception;

class InvalidOption extends Exception
{
    public static function missingField()
    {
        return new static('Could not determine in which field the external id should be saved');
    }
}
