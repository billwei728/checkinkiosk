<?php

namespace App\Model;

use App\App;
use Exception;

class TrackingModel extends BaseModel //implements ModelInterface
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

    public function update(array $params = []) 
    {
        if (empty($params)) {
            throw new Exception(gettext('There is no data to update.'));
        }

        $dbtable = $this->getHydrahon()->table($this->getTable());

        $bExists = $dbtable->select()
                           ->where('id', $_SESSION['userid'])
                           ->exists();

        if (! $bExists) {
            throw new Exception(gettext('The user ID was invalid. Please contact the administrator!'));
        }

        $update = $dbtable->update();
        $update->set($params);
        $update->where('id', $_SESSION['userid']);

        if ($update->execute()) {
            return array('success' => true);
        } else {
            return array('success' => false, 'errMsg' => gettext('Failed to update record.'));
        }
    }

    public function select(array $filter = [], array $fields = []) 
    {
        $dbtable = $this->getHydrahon()->table($this->getTable());

        $select = $dbtable->select($fields)
                          ->where('userid', $_SESSION['userid'])
                          ->execute();

        if ($select) {
            return array('success' => true, 'data' => $select);
        } else {
            return array('success' => false, 'errMsg' => gettext('Failed to select record.'));
        }
    }

    public function isValid($columns): bool 
    {
        if (empty($columns['userid'])) {
            throw new Exception(gettext('User ID cannot be empty!'));
        }

        if (empty($columns['eventid'])) {
            throw new Exception(gettext('Event ID cannot be empty!'));
        }

        if (empty($columns['boothid'])) {
            throw new Exception(gettext('Booth ID cannot be empty!'));
        }

        return true;
    }

    public function getColumn(): array 
    {
        return array(
            'id'         => 0,
            'userid'     => 0,
            'eventid'    => 0,
            'boothid'    => 0,
            'status'     => 1,
            'createdon'  => '',
            'modifiedon' => ''
        );
    }

    private function getTable(): string 
    {
        return 'tracking';
    }
}

?>