<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropAllTables extends Command
{
    protected $signature = 'db:drop-all-tables';
    protected $description = 'Drop all tables from the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Dropping all tables...');
        
        // Disable foreign key checks to avoid constraint errors
        Schema::disableForeignKeyConstraints();
        
        // Get all table names
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $tables = array_map('current', $tables);

        foreach ($tables as $table) {
            Schema::drop($table);
            $this->info("Dropped table: $table");
        }
        
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $this->info('All tables dropped successfully!');
    }
}
