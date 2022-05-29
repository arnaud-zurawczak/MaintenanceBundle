<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers\Query;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Default Class for handle database with a doctrine connection.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DefaultQuery extends PdoQuery
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public const NAME_TABLE = 'ady_maintenance';

    /**
     * @param EntityManagerInterface $em Entity Manager
     */
    public function __construct(EntityManagerInterface $em, array $options = [])
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
    public function initDb(): Connection
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
