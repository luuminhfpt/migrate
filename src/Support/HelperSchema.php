<?php

namespace Bigin\Migrate\Support;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HelperSchema
{
    /**
     * @param string $table
     * @param string $column
     * @param Closure $callback
     */
    static public function upAddColumn(string $table, string $column, Closure $callback, Closure $afterCallback = null)
    {
        if (Schema::hasTable($table)) {
            if (!Schema::hasColumn($table, $column)) {
                Schema::table($table, $callback);

                if (is_callable($afterCallback)) {
                    $afterCallback();
                }
            }
        }
    }

    /**
     * @param string $table
     * @param string $column
     * @param Closure $callback
     */
    static public function downAddColumn(string $table, string $column)
    {
        if (Schema::hasTable($table)) {
            if (Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
}
