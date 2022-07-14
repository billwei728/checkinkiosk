<?php

namespace App;

use PDO; 
use Exception;

class Database extends \PDO
{
    public function __construct(
        string $type, 
        string $host, 
        string $username, 
        string $password, 
        string $database, 
        string $defaultCharset = 'utf8')
    {
        try {
            parent::__construct("$type:host=$host;dbname=$database", $username, $password);
            $this->pdoDBHandle = $this;

            $defaultCharset = preg_replace("/[^-a-z0-9_]/i", '', $defaultCharset);
            if (0 < strlen($defaultCharset)) {
                $this->pdoDBHandle->exec('SET NAMES ' . $defaultCharset);
            }
        } catch (\PDOException $expErr) {
            throw new \Exception("Failed connecting to $type://$username:$password@$host/$database - " . $this->pdoDBHandle2->ErrorMsg());
        }

        return $this;
    }

    public function close(): void
    {
        $this->pdoDBHandle = null;
    }
}

?>