<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Database\SQLite;

use Closure;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver as DoctrineDriver;
use Hyperf\Database\Connection;
use Hyperf\Database\Query\Grammars\Grammar as HyperfQueryGrammar;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\Database\Schema\Builder as SchemaBuilder;
use Hyperf\Database\Schema\Grammars\Grammar as HyperfSchemaGrammar;
use Hyperf\Database\SQLite\Query\Grammars\SQLiteGrammar as QueryGrammar;
use Hyperf\Database\SQLite\Query\Processors\SQLiteProcessor;
use Hyperf\Database\SQLite\Schema\Grammars\SQLiteGrammar as SchemaGrammar;
use Hyperf\Database\SQLite\Schema\SQLiteBuilder;
use PDO;

class SQLiteConnection extends Connection
{
    /**
     * Create a new database connection instance.
     *
     * @param Closure|PDO $pdo
     * @param string $database
     * @param string $tablePrefix
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        $enableForeignKeyConstraints = $this->getForeignKeyConstraintsConfigurationValue();

        if ($enableForeignKeyConstraints === null) {
            return;
        }

        $enableForeignKeyConstraints
            ? $this->getSchemaBuilder()->enableForeignKeyConstraints()
            : $this->getSchemaBuilder()->disableForeignKeyConstraints();
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Hyperf\Database\SQLite\Schema\SQLiteBuilder
     */
    public function getSchemaBuilder(): SchemaBuilder
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SQLiteBuilder($this);
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Hyperf\Database\SQLite\Query\Grammars\SQLiteGrammar
     */
    protected function getDefaultQueryGrammar(): HyperfQueryGrammar
    {
        /* @phpstan-ignore-next-line */
        return $this->withTablePrefix(new QueryGrammar());
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Hyperf\Database\SQLite\Schema\Grammars\SQLiteGrammar
     */
    protected function getDefaultSchemaGrammar(): HyperfSchemaGrammar
    {
        /* @phpstan-ignore-next-line */
        return $this->withTablePrefix(new SchemaGrammar());
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Hyperf\Database\SQLite\Query\Processors\SQLiteProcessor
     */
    protected function getDefaultPostProcessor(): Processor
    {
        return new SQLiteProcessor();
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDO\SQLite\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver();
    }

    /**
     * Get the database connection foreign key constraints configuration option.
     *
     * @return null|bool
     */
    protected function getForeignKeyConstraintsConfigurationValue()
    {
        return $this->getConfig('foreign_key_constraints');
    }
}
