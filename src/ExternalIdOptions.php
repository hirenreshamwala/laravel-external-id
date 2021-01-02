<?php

namespace XT\ExternalId;

class ExternalIdOptions
{
    /** @var array|callable */
    public $externalIdField;

    public $generateExternalIdOnCreate = true;


    public static function create(): self
    {
        return new static();
    }

    public function saveExternalIdTo(string $fieldName): self
    {
        $this->externalIdField = $fieldName;

        return $this;
    }

    public function doNotGenerateExternalIdOnCreate(): self
    {
        $this->generateExternalIdOnCreate = false;

        return $this;
    }
}
