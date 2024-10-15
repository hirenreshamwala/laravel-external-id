<?php

namespace XT\ExternalId;

class ExternalIdOptions
{
    /** @var array|callable */
    public $externalIdField;

    public $externalIdPrefix;

    public $externalIdLength;

    public $generateExternalIdOnCreate = true;

    public $isNumberOnly = false;

    public $isTimeBase = false;

    public $isIncremental = false;

    public $startingIncrementalValue = 1;

    public $customIdScopeCallback;


    public static function create(): self
    {
        return new static();
    }

    public function saveExternalIdTo(string $fieldName, string $prefix = null, int $length = null): self
    {
        $this->externalIdField = $fieldName;

        $this->externalIdPrefix = $prefix;

        $this->externalIdLength = $length;

        return $this;
    }

    public function setLength(int $length = null): self
    {
        $this->externalIdLength = $length;

        return $this;
    }

    public function setPrefix(string $prefix = null): self
    {
        $this->externalIdPrefix = $prefix;

        return $this;
    }

    public function setIsNumberOnly(bool $isNumberOnly = false): self
    {
        $this->isNumberOnly = $isNumberOnly;

        return $this;
    }

    public function setIsTimeBase(bool $isTimeBasedId = false): self
    {
        $this->isTimeBase = $isTimeBasedId;

        return $this;
    }

    public function incremental(int $startingValue = 1): self
    {
        $this->isIncremental = true;
        $this->startingIncrementalValue = $startingValue;

        return $this;
    }

    public function doNotGenerateExternalIdOnCreate(): self
    {
        $this->generateExternalIdOnCreate = false;

        return $this;
    }

    public function customIdScope(callable $callbackMethod): self
    {
        $this->customIdScopeCallback = $callbackMethod;

        return $this;
    }
}
