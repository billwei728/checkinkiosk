<?php

namespace App\Model;

use App\App;
use Exception;

class BoothModel extends BaseModel //implements ModelInterface
{

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function save(array $params = []) 
    {
        if (empty($params)) {
            throw new Exception(gettext('There is no data to insert.'));
        }

        $dbtable = $this->getHydrahon()->table($this->getTable());

        $bExists = $dbtable->select()
                           ->where('name', $params['name'])
                           ->exists();

        if ($bExists) {
            throw new Exception(gettext('This name is already being used. Please try with another name!'));
        }

        $fields = $this->getColumn();
        foreach ($fields as $column => $value) {
            if (in_array($column, ['createdon', 'modifiedon'])) {
                unset($fields[$column]);
                continue;
            }
            $fields[$column] = (isset($params[$column])) ? $params[$column] : $value;
        }

        if (! $this->isValid($fields)) {
            throw new Exception(gettext('One or more fields have an error. Please check and try again!'));
        }

        $insert = $dbtable->insert();
        $insert->values($fields);

        if ($insert->execute()) {
            return array('success' => true);
        } else {
            return array('success' => false, 'errMsg' => gettext('Failed to insert record.'));
        }
    }

    public function isValid($columns): bool 
    {
        if (empty($columns['name'])) {
            throw new Exception(gettext('Name cannot be empty!'));
        }

        return true;
    }

    public function getColumn(): array 
    {
        return array(
            'id'         => 0,
            'eventid'    => 0,
            'name'       => '',
            'status'     => 1,
            'createdon'  => '',
            'modifiedon' => ''
        );
    }

    private function getTable(): string 
    {
        return 'booth';
    }
}

?>