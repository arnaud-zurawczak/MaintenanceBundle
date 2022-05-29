<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers\Query;

use Doctrine\ORM\EntityManager;
use PDO;

/**
 * Default Class for handle database with a doctrine connection.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DefaultQuery extends PdoQuery
{
    /**
     * @var EntityManager
     */
    protected $em;

    public const NAME_TABLE = 'ady_maintenance';

    /**
     * @param EntityManager $em Entity Manager
     */
    public function __construct(EntityManager $em, array $options = [])
    {
        $this->em = $em;
        if (empty($options['table'])) {
            $options['table'] = self::NAME_TABLE;
        }
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function initDb(): PDO
    {
        if (null === $this->db) {
            $db = $this->em->getConnection();
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
        $type = 'mysql' != $this->em->getConnection()->getDatabasePlatform()->getName() ? 'timestamp' : 'datetime';

        $this->db->exec(
            sprintf('CREATE TABLE IF NOT EXISTS %s (ttl %s DEFAULT NULL)', $this->options['table'], $type)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQuery(PDO $db): bool
    {
        return $this->exec($db, sprintf('DELETE FROM %s', $this->options['table']));
    }

    /**
     * {@inheritdoc}
     */
    public function selectQuery(PDO $db): array
    {
        return $this->fetch($db, sprintf('SELECT ttl FROM %s', $this->options['table']));
    }

    /**
     * {@inheritdoc}
     */
    public function insertQuery(?int $ttl, PDO $db): bool
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
