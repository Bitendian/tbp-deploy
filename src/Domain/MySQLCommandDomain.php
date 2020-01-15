<?php

namespace Bitendian\TBP\Deploy\Domain;

class MySQLCommandDomain implements DatabaseCommandInterface
{
    public function executeQuery($sql, $configuration)
    {
        $user = $configuration->username;
        $pass = $configuration->password;
        $host = $configuration->server;
        $name = $configuration->database;

        $result = shell_exec("echo '$sql' | mysql -u$user -p$pass -h$host $name");
        return empty($result);
    }

    public function loadFromFile($fileName, $configuration)
    {
        $user = $configuration->username;
        $pass = $configuration->password;
        $host = $configuration->server;
        $name = $configuration->database;

        $result = shell_exec("mysql -u$user -p$pass -h$host $name < $fileName");
        return empty($result);
    }

    public function saveToFile($fileName, $configuration)
    {
        $user = $configuration->username;
        $pass = $configuration->password;
        $host = $configuration->server;
        $name = $configuration->database;

        $result = shell_exec("mysqldump --default-charset=utf8 -u$user -p$pass -h$host $name > $fileName");
        return empty($result);
    }

    public function checkCommandExecuteQueryAvailability()
    {
        return $this->checkCommandLineLoadAvailability();
    }

    public function checkCommandLineLoadAvailability()
    {
        $result = shell_exec("which mysql");
        return !empty($result);
    }

    public function checkCommandLineSaveAvailability()
    {
        $result = shell_exec("which mysqldump");
        return !empty($result);
    }
}
