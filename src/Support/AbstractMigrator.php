<?php

namespace Bigin\Migrate\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

abstract class AbstractMigrator extends Command
{
    use HelperMigrate;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->loadReferences();

        if ($this->option('reset')) {
            $this->resetTrackingColumn();
            return;
        }

        $lines = $this->loadDataMigration($this->getCustomCondition());

        if ($lines->isEmpty()) {
            return;
        }

        $this->initMigrator($lines);
        $this->handle();
    }

    /**
     * loadReferences
     *
     * @return void
     */
    protected function loadReferences()
    {
        if (!$this->initCommand) {
            $this->addColumnTrackingData();
            $this->initCommand = true;
        }
    }

    /**
     * @return mixed
     */
    protected function getCustomCondition()
    {
        return null;
    }

    /**
     * @param Collection $lines
     * @return mixed
     */
    abstract protected function initMigrator(Collection $lines);
}
