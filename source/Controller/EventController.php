<?php

namespace App\Controller;

use App\App;
use Exception;

class EventController
{
	private ?object $model = null;


    public function __construct(App $app)
    {
    	$this->setModel($app);
    }

    public function doAction(App $app, string $action, array $params = []): array 
    {
        if ($action == 'select') {
            return $this->doSelectEvent($app, $params);
        }
    	if ($action == 'register') {
    		return $this->doRegisterEvent($app, $params);
    	}
    }

    protected function doRegisterEvent(App $app, array $params = []): array 
    {
    	$bSuccess = true;
    	$response = array('success' => true, 'errMsg' => '');
    	$eventname = trim($params['eventname']);

    	try {
            $result = $this->model->save(['name' => $eventname]);

            if (! $result['success']) {
            	$response = array('success' => $result['success'], 'errMsg' => $result['errMsg']);
            }

            unset($params['eventname']);
            $event = $this->model->select(['name' => $eventname]);
            $params['eventid'] = $event['data']['id'];

            $boothController = $app->getController('Booth');
            $result = $boothController->doAction($app, 'register', $params);

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
    	$className = 'App\Model\EventModel';
        $reflection = new \ReflectionClass($className);

    	$this->model = $reflection->newInstanceArgs([$app]);
    }
}

?>