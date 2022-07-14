<?php

namespace App\controller;

use App\App;
use Exception;

class BoothController
{
	private ?object $model = null;


    public function __construct(App $app)
    {
    	$this->setModel($app);
    }

    public function doAction(App $app, string $action, array $params = []): array 
    {
    	if ($action == 'register') {
    		return $this->doRegisterBooth($app, $params);
    	}
    }

    protected function doRegisterBooth(App $app, array $params = []): array 
    {
    	$bSuccess = true;
    	$response = array('success' => true, 'errMsg' => '');
    	$eventid = trim($params['eventid']);

        $booths = [];
        foreach ($params as $key => $value) {
            if (str_starts_with($key, 'name_')) {
                $booths[]['name'] = $value;
            }
        }

    	try {
            foreach ($booths as $num => $booth) {
                $result = $this->model->save(['eventid' => $eventid, 'name' => $booth['name']]);
            }            

            if (! $result['success']) {
            	$response = array('success' => $result['success'], 'errMsg' => $result['errMsg']);
            }
        } catch (\PDOException $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }

        return $response;
    }

    private function setModel(App $app): void
    {
    	$className = 'App\Model\BoothModel';
        $reflection = new \ReflectionClass($className);

    	$this->model = $reflection->newInstanceArgs([$app]);
    }
}

?>