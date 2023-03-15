<?php

namespace Bigin\Migrate\Support;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HelperMigrate
{
    /**
     * @var string|bool
     */
    public $sortEnable = 'asc';

    /**
     * @var boolean
     */
    public $initCommand = false;

    /**
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * The name column tracking migrated
     *
     * @var string
     */
    public $columnTracking = 'migrated';

    /**
     * The name table migrate data
     *
     * @var string
     */
    public $tableMigration;

    /**
     * The name model migrate data
     *
     * @var mixed
     */
    public $entityMigration;

    /**
     * The limit items
     *
     * @var integer
     */
    public $limit = 400;

    /**
     * Setter primaryKey attribute
     *
     * @param mixed $column
     * @return $this
     */
    public function setPrimaryKeyAttribute(string $column)
    {
        $this->primaryKey = $column;

        return $this;
    }

    /**
     * Setter columnTracking attribute
     *
     * @param mixed $column
     * @return $this
     */
    public function setColumnTrackingAttribute(string $column)
    {
        $this->columnTracking = $column;

        return $this;
    }

    /**
     * Setter tableMigration attribute
     *
     * @param mixed $table
     * @return $this
     */
    public function setTableMigrationAttribute(string $table)
    {
        $this->tableMigration = $table;

        return $this;
    }

    /**
     * Setter entityMigration attribute
     *
     * @param mixed $entity
     * @return $this
     */
    public function setEntityMigrationAttribute(string $entity)
    {
        $this->entityMigration = $entity;

        return $this;
    }

    /**
     * Setter limit attribute
     *
     * @param mixed $limit
     * @return $this
     */
    public function setLimitAttribute(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Clean database before start migration service for transferee
     *
     * @return void
     */
    public function resetTrackingColumn()
    {
        HelperSchema::downAddColumn($this->tableMigration, $this->columnTracking);
        HelperSchema::downAddColumn($this->tableMigration, 'updated_at');
    }

    /**
     * inherit
     */
    public function addColumnTrackingData()
    {
        if ($this->tableMigration) {
            HelperSchema::upAddColumn($this->tableMigration, $this->columnTracking, function (Blueprint $table) {
                $table->boolean($this->columnTracking)->nullable();
            }, function () {
                DB::statement("CREATE INDEX ON {$this->tableMigration} ({$this->columnTracking});");
            });

            HelperSchema::upAddColumn($this->tableMigration, 'updated_at', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });

            HelperSchema::upAddColumn($this->tableMigration, $this->primaryKey, function (Blueprint $table) {
                DB::statement("ALTER TABLE {$this->tableMigration} ADD COLUMN {$this->primaryKey} SERIAL PRIMARY KEY");
            });
        }
    }

    /**
     * @param null $filter
     * @return Collection
     */
    public function loadDataMigration($filter = null): Collection
    {
        if ($this->entityMigration) {
            $queryData = new $this->entityMigration;
        } else {
            $queryData = DB::table($this->tableMigration);
        }

        $queryData = $queryData
            ->take($this->limit)
            ->where(function ($query) {
                $query
                    ->whereNull($this->columnTracking)
                    ->orWhere($this->columnTracking, false);
            })
            ->where($filter ?: []);

        if ($this->sortEnable) {
            $queryData->orderBy($this->primaryKey, $this->sortEnable);
        }

        $items = $queryData->get();

        DB::table($this->tableMigration)
            ->whereIn($this->primaryKey, $items->pluck($this->primaryKey)->toArray())
            ->update([
                $this->columnTracking => true,
                'updated_at' => Carbon::now()
            ]);

        return $items;
    }
}
