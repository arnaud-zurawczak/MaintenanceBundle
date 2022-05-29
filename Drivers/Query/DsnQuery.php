<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers\Query;

/**
 * Class for handle database with a dsn connection.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DsnQuery extends PdoQuery
{
    /**
     * {@inheritdoc}
     */
    public function initDb(): \PDO
    {
        if (null === $this->db) {
            if (!class_exists('PDO') || !in_array('mysql', \PDO::getAvailableDrivers(), true)) {
                throw new \RuntimeException('You need to enable PDO_Mysql extension for the profiler to run properly.');
            }

            $db = new \PDO($this->options['dsn'], $this->options['user'], $this->options['password']);
            $this->db = $db;
            if (!isset($this->options['table_created']) || !$this->options['table_created']) {
                $this->createTableQuery();
            }
        }

        return $this->db;
    }

    /**
     * {@inheritdoc}
     */
    public function createTableQuery(): void
    {
        $type = 'mysql' != $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) ? 'timestamp' : 'datetime';

        $this->db->exec(sprintf('CREATE TABLE IF NOT EXISTS %s (ttl %s DEFAULT NULL)', $this->options['table'], $type));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQuery($db): bool
    {
        return $this->exec($db, sprintf('DELETE FROM %s', $this->options['table']));
    }

    /**
     * {@inheritdoc}
     */
    public function selectQuery($db): array
    {
        return $this->fetch($db, sprintf('SELECT ttl FROM %s', $this->options['table']));
    }

    /**
     * {@inheritdoc}
     */
    public function insertQuery(?int $ttl, $db): bool
    {
        return $this->exec(
            $db,
            sprintf(
                'INSERT INTO %1$s (ttl) VALUES (?)',
                $this->options['table']
            ),
            [1 => $ttl]
        );
    }
}
