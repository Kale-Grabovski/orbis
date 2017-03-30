<?php

namespace App\Models;

use App\Components\DB;
use PDO;
use PDOStatement;

class BaseModel
{
    /**
     * @return BaseModel instance of the current class
     */
    public static function model() : BaseModel
    {
        return new static;
    }

    /**
     * Helper returns Database instance
     *
     * @return PDO
     */
    protected function getDb()
    {
        return DB::getInstance();
    }

    /**
     * Helper performs query with placeholders
     *
     * @param string $query
     * @param array $placeholders
     * @return PDOStatement
     */
    private function execute(string $query, array $placeholders) : PDOStatement
    {
        $sth = $this->getDb()->prepare($query);
        $sth->execute($placeholders);

        return $sth;
    }

    /**
     * Performs PDO SELECT query and return all rows in a result
     *
     * @param string $query
     * @param array $placeholders
     * @return array
     */
    public function select(string $query, array $placeholders = []) : array
    {
        return $this->execute($query, $placeholders)->fetchAll();
    }

    /**
     * Performs INSERT statement
     *
     * @param string $query
     * @param array $placeholders
     */
    public function insert(string $query, array $placeholders = [])
    {
        $this->getDb()->beginTransaction();
        $this->execute($query, $placeholders);
        $this->getDb()->commit();
    }
}
