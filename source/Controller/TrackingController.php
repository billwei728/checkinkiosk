<?php

namespace App\Controller;

use App\App;
use Exception;

class TrackingController
{
	private ?object $model = null;


    public function __construct(App $app)
    {
    	$this->setModel($app);
    }

    public function doAction(App $app, string $action, array $params = []): array 
    {
    	if ($action == 'register') {
    		return $this->doRegisterTracking($app, $params);
    	}
        if ($action == 'select') {
            return $this->doSelectTracking($app, $params);
        }
    }

    protected function doRegisterTracking(App $app, array $params = []): array 
    {
    	$response = array('success' => true, 'errMsg' => '');

    	try {
            $boothController = $app->getController('Booth');
            $booths = $boothController->doAction($app, 'select', ['eventid' => $params['eventid']]);

            foreach ($booths['data'] as $num => $booth) {
                $result = $this->model->save([
                    'userid'  => $_SESSION['userid'], 
                    'eventid' => $params['eventid'], 
                    'boothid' => $booth['id']
                ]);
            }

            if (! $result['success']) {
            	$response = array('success' => $result['success'], 'errMsg' => $result['errMsg']);
            }
        } catch (\PDOException $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }

        return $response;
    }

    protected function doSelectTracking(App $app, array $params = []): array 
    {
        $response = array('success' => true, 'errMsg' => '');

        try {
            $result = $this->model->select(); 

            if (! $result['success']) {
                $response = array('success' => $result['success'], 'errMsg' => $result['errMsg'], 'data' => $result['data']);
            }

            $response['data'] = $result['data'];

        } catch (\PDOException $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }

        return $response;
    }

    private function setModel(App $app): void
    {
    	$className = 'App\Model\TrackingModel';
        $reflection = new \ReflectionClass($className);

    	$this->model = $reflection->newInstanceArgs([$app]);
    }
}

?>