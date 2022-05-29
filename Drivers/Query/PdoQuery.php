<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

/**
 * Abstract class to handle PDO connection.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
abstract class PdoQuery
{
    /**
     * @var \PDO|Connection
     */
    protected $db;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor PdoDriver.
     *
     * @param array $options Options driver
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Execute create query.
     */
    abstract public function createTableQuery(): void;

    /**
     * Result of delete query.
     *
     * @param \PDO|Connection $db PDO instance
     */
    abstract public function deleteQuery($db): bool;

    /**
     * Result of select query.
     *
     * @param \PDO|Connection $db PDO instance
     */
    abstract public function selectQuery($db): array;

    /**
     * Result of insert query.
     *
     * @param ?int            $ttl ttl value
     * @param \PDO|Connection $db  PDO instance
     */
    abstract public function insertQuery(?int $ttl, $db): bool;

    /**
     * Initialize pdo connection.
     *
     * @return \PDO|Connection
     */
    abstract public function initDb();

    /**
     * Execute sql.
     *
     * @param \PDO|Connection $db    PDO instance
     * @param string          $query Query
     * @param array           $args  Arguments
     *
     * @throws \RuntimeException
     */
    protected function exec($db, string $query, array $args = []): bool
    {
        $stmt = $this->prepareStatement($db, $query);

        $this->bindValues($stmt, $args);

        $success = $stmt->execute();

        if (!$success) {
            throw new \RuntimeException(sprintf('Error executing query "%s"', $query));
        }

        return $success;
    }

    /**
     * PrepareStatement.
     *
     * @param \PDO|Connection $db    PDO instance
     * @param string          $query Query
     *
     * @return \PDOStatement|Statement
     *
     * @throws \RuntimeException
     */
    protected function prepareStatement($db, string $query)
    {
        try {
            $stmt = $db->prepare($query);
        } catch (\Exception $e) {
            $stmt = false;
        }

        if (false === $stmt) {
            throw new \RuntimeException('The database cannot successfully prepare the statement');
        }

        return $stmt;
    }

    /**
     * Fetch All.
     *
     * @param \PDO|Connection $db    PDO instance
     * @param string          $query Query
     * @param array           $args  Arguments
     *
     * @return array
     */
    protected function fetch($db, string $query, array $args = [])
    {
        $stmt = $this->prepareStatement($db, $query);

        $this->bindValues($stmt, $args);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param \PDOStatement|Statement $stmt
     *
     * @return void
     */
    private function bindValues($stmt, array $args)
    {
        foreach ($args as $arg => $val) {
            $stmt->bindValue($arg, $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
    }
}
