<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Migrations extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Enable/Disable Migrations
     * --------------------------------------------------------------------------
     *
     * Migrations are enabled by default.
     *
     * You should enable migrations whenever you intend to do a schema migration
     * and disable it back when you're done.
     */
    public bool $enabled = true;

    /**
     * --------------------------------------------------------------------------
     * Migrations Table
     * --------------------------------------------------------------------------
     *
     * This is the name of the table that will store the current migrations state.
     * When migrations runs it will store in a database table which migration
     * files have already been run.
     */
    public string $table = 'migrations';

    public $type = 'sequential';

}
