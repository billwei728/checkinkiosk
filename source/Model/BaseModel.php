<?php

namespace App\Model;

use App\App;
use Exception;

class BaseModel
{
    protected object $app;


    public function __construct()
    {
    }

    // public function save(array $params = []) 
    // {
    // }

    // public function getColumn(): array 
    // {
    // }

    // private function getTable(): string 
    // {
    // }

    public function getHydrahon(): object
    {
        $pdoDBHandle = '';
        $connection = $this->app->getDBHandle();

        try {
            $pdoDBHandle = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
            {
                $queryCmd = substr($queryString, 0, 6);
                $startedTransaction = false;
                if ($queryCmd != 'select') {
                    if (! $connection->inTransaction()) {
                        $startedTransaction = true;
                        $connection->beginTransaction();
                    }
                }
                
                $statement = $connection->prepare($queryString);
                if (! $statement) {
                    if ($startedTransaction) {
                        $connection->rollBack();
                    }
                }
                if (! $statement->execute($queryParameters)) {
                    if ($startedTransaction) {
                        $connection->rollBack();
                    }
                }

                # when the query is fetchable return all results and let hydrahon do the rest #
                if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
                    return $statement->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    if ($startedTransaction) {
                        $connection->commit();
                    }
                    return true;
                }
            });
        } catch (PDOException $error) {
            throw new Exception(gettext('Connection Failed, Please contact administrator!'));
        }

        return $pdoDBHandle;
    }
}

?>