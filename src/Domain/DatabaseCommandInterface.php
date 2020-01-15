<?php

namespace Bitendian\TBP\Deploy\Domain;

interface DatabaseCommandInterface
{
    public function executeQuery($sql, $configuration);

    public function loadFromFile($fileName, $configuration);

    public function saveToFile($fileName, $configuration);

    public function checkCommandExecuteQueryAvailability();

    public function checkCommandLineLoadAvailability();

    public function checkCommandLineSaveAvailability();
}