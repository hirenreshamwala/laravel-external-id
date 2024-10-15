<?php

namespace XT\ExternalId;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasExternalId
{
    protected $externalIdOptions;

    abstract public function getExternalIdOptions(): ExternalIdOptions;

    protected static function bootHasExternalId()
    {
        static::creating(function (Model $model) {
            $model->generateExternalIdOnCreate();
        });
    }

    /**
     * Find a model by its external id.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public function scopeFindByExternalId(Builder $query, $id, $columns = ['*']){
        try {
            $externalIdField = $this->getExternalIdOptions();
            $query->where($externalIdField->externalIdField, '=', $id);
            return $query->first($columns);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find a model by its external id or fail.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public function scopeFindByExternalIdOrFail(Builder $query, $id, $columns = ['*']){
        try {
            $externalIdField = $this->getExternalIdOptions();
            $query->where($externalIdField->externalIdField, '=', $id);
            return $query->firstOrFail($columns);
        } catch (\Exception $e) {
            return null;
        }
    }


    protected function generateExternalIdOnCreate()
    {
        $this->externalIdOptions = $this->getExternalIdOptions();

        if (! $this->externalIdOptions->generateExternalIdOnCreate) {
            return;
        }

        $this->addExternalId();
    }

    public function generateExternalId()
    {
        $this->externalIdOptions = $this->getExternalIdOptions();

        $this->addExternalId();
    }

    protected function getRandomNumber($length = 4) {
        return rand(
            ((int) str_pad(1, $length, 0, STR_PAD_RIGHT)),
            ((int) str_pad(9, $length, 9, STR_PAD_RIGHT))
        );
    }

    private function getNewId(){

        if ($this->externalIdOptions->isIncremental) {
            return $this->getNextIncrementalId();
        }

        if ($this->externalIdOptions->customIdScopeCallback) {
            return $this->externalIdOptions->customIdScopeCallback->call($this);
        }

        $id = Str::uuid()->toString();

        if (!$this->externalIdOptions->isNumberOnly && !$this->externalIdOptions->isTimeBase && isset($this->externalIdOptions->externalIdLength)){
            $id = strtolower(Str::random($this->externalIdOptions->externalIdLength));
        } else if ($this->externalIdOptions->isTimeBase){
            $id = time().$this->getRandomNumber(4);
        } else if ($this->externalIdOptions->isNumberOnly){
            $id = $this->getRandomNumber($this->externalIdOptions->externalIdLength ?? 6);
        }

        if (isset($this->externalIdOptions->externalIdPrefix)){
            $id = $this->externalIdOptions->externalIdPrefix . $id;
        }
        return $id;
    }

    private function getNextIncrementalId()
    {
        $externalIdField = $this->externalIdOptions->externalIdField;
        $lastRecord = static::orderBy($externalIdField, 'desc')->first();
        
        // Check if there is no previous record and use startingIncrementalValue
        if (!$lastRecord) {
            return $this->externalIdOptions->externalIdPrefix . $this->externalIdOptions->startingIncrementalValue;
        }
        
        // Extract the numeric part of the external ID and increment it
        $lastIncrementalId = (int)str_replace($this->externalIdOptions->externalIdPrefix, '', $lastRecord->$externalIdField);

        return $this->externalIdOptions->externalIdPrefix . ($lastIncrementalId + 1);
    }

    protected function addExternalId()
    {
        $this->ensureValidExternalIdOptions();

        $id = $this->hasUnique($this->getNewId());

        $externalIdField = $this->externalIdOptions->externalIdField;

        $this->$externalIdField = $id;
    }

    protected function ensureValidExternalIdOptions()
    {
        if (! strlen($this->externalIdOptions->externalIdField)) {
            throw InvalidOption::missingField();
        }
    }

    protected function hasUnique(string $id): string
    {
        while ($this->otherRecordExistsWithExternalId($id) || $id === '') {
            $id = $this->getNewId();
        }

        return $id;
    }

    protected function otherRecordExistsWithExternalId(string $id): bool
    {
        $query = static::where($this->externalIdOptions->externalIdField, $id)
            ->withoutGlobalScopes();

        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        return $query->exists();
    }
}
