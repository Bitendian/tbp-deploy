<?php

namespace Bitendian\TBP\Deploy\Services;

use Bitendian\TBP\Deploy\Domain\MySQLCommandDomain;
use Bitendian\TBP\Utils\SystemMessages;
use Bitendian\TBP\Deploy\Domain\DatabaseCommandInterface;
use Bitendian\TBP\Domain\Connection\Interfaces\DatabaseConnectionInterface;
use Bitendian\TBP\Domain\Connection\Database\MysqlDatabaseConnection;
use Bitendian\TBP\Domain\Connection\Database\MssqlDatabaseConnection;
use Bitendian\TBP\TBPException;

class DatabaseService
{
    const MYSQL = 'mysql';
    const MSSQL = 'mssql';

    /**
     * @param string $driverName
     * @param \stdClass $configuration
     * @return bool
     */
    public function reset($driverName, $configuration)
    {
        if (!$this->checkDatabaseConfigurationSyntax($configuration)) {
            SystemMessages::addError(_('configuration problem'));
            return false;
        }

        $commandDomain = $this->getDatabaseCommandDomain($driverName);

        $databaseName = $configuration->database;
        $query = "DROP DATABASE IF EXISTS `$databaseName`; CREATE DATABASE IF NOT EXISTS `$databaseName` CHARACTER SET utf8 COLLATE utf8_bin";

        if (!$commandDomain->checkCommandLineLoadAvailability()) {
            SystemMessages::addError(_('SQL command line load executable not found'));
            return false;
        }

        return $commandDomain->executeQuery($query, $configuration);
    }

    /**
     * @param string $driverName
     * @param \stdClass $configuration
     * @param string $sqlFile
     * @return bool
     */
    public function loadFromFile($driverName, $configuration, $sqlFile)
    {
        if (!$this->checkDatabaseConfigurationSyntax($configuration)) {
            SystemMessages::addError(_('configuration problem'));
            return false;
        }

        if (!file_exists($sqlFile)) {
            SystemMessages::addError(_('SQL file not found: ') . $sqlFile);
            return false;
        }

        $commandDomain = $this->getDatabaseCommandDomain($driverName);
        if (!$commandDomain->checkCommandLineLoadAvailability()) {
            SystemMessages::addError(_('SQL command line load executable not found'));
            return false;
        }

        return $commandDomain->loadFromFile($sqlFile, $configuration);
    }

    /**
     * @param string $driverName
     * @param \stdClass $configuration
     * @param string $sqlFile
     * @return bool
     */
    public function saveToFile($driverName, $configuration, $sqlFile)
    {
        if (!$this->checkDatabaseConfigurationSyntax($configuration)) {
            SystemMessages::addError(_('configuration problem'));
            return false;
        }

        $commandDomain = $this->getDatabaseCommandDomain($driverName);
        if (!$commandDomain->checkCommandLineSaveAvailability()) {
            SystemMessages::addError(_('SQL command line save executable not found'));
            return false;
        }
        return $commandDomain->saveToFile($sqlFile, $configuration);
    }

    /**
     * @param string $driverName
     * @param \stdClass $configuration
     * @return bool
     */
    public function testConnection($driverName, $configuration)
    {
        if (!$this->checkDatabaseConfigurationSyntax($configuration)) {
            SystemMessages::addError(_('configuration problem'));
            return false;
        }

        $connection = $this->getConnection($driverName, $configuration);
        try {
            @$connection->open();
            @$connection->close();
        } catch (TBPException $e) {
            SystemMessages::addError(_('unable to connect to configured database in file'));
            return false;
        }

        return true;
    }

    /**
     * @param $driverName
     * @param $configuration
     * @return DatabaseConnectionInterface
     */
    private function getConnection($driverName, $configuration)
    {
        if ($driverName == self::MYSQL) return new MysqlDatabaseConnection($configuration);
        else return new MssqlDatabaseConnection($configuration);
    }

    /**
     * @param string $driverName
     * @return DatabaseCommandInterface|null
     */
    private function getDatabaseCommandDomain($driverName)
    {
        if ($driverName == self::MYSQL) return new MySQLCommandDomain();
        else return null;
    }

    private function checkDatabaseConfigurationSyntax($configuration)
    {
        if (!$configuration || !is_object($configuration)) {
            SystemMessages::addError(_('expected configuration object'));
            return false;
        }

        if (!isset($configuration->username)) {
            SystemMessages::addError(_('expected configuration username'));
            return false;
        }

        if (!isset($configuration->password)) {
            SystemMessages::addError(_('expected configuration password'));
            return false;
        }

        if (!isset($configuration->database) || empty($configuration->database)) {
            SystemMessages::addError(_('expected configuration database name'));
            return false;
        }

        if (!isset($configuration->server) || empty($configuration->server)) {
            SystemMessages::addError(_('expected configuration host'));
            return false;
        }

        return true;
    }
}
