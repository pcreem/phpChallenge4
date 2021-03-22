<?php 

namespace App\Core\Database;


interface RainfallSchema
{
    public function __construct($pdo);

    public function createDistrictsTable();

    public function createRainfallsTable();

    public function importData();
}