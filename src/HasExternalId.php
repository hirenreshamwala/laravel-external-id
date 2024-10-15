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
        $externalIdField = $this->getExternalIdOptions();
        $query->where($externalIdField->externalIdField, '=', $id)->first($columns);
    }

    /**
     * Find a model by its external id or fail.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public function scopeFindByExternalIdOrFail(Builder $query, $id, $columns = ['*']){
        $externalIdField = $this->getExternalIdOptions();
        $query->where($externalIdField->externalIdField, '=', $id)->firstOrFail($columns);
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

    protected function addExternalId()
    {
        $this->ensureValidExternalIdOptions();

        $id = Str::uuid()->toString();

        $externalIdField = $this->externalIdOptions->externalIdField;

        $this->$externalIdField = $id;
    }

    protected function ensureValidExternalIdOptions()
    {
        if (! strlen($this->externalIdOptions->externalIdField)) {
            throw InvalidOption::missingField();
        }
    }
}
