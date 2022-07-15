<?php

namespace App\Controller;

use App\App;
use Exception;

class UserController
{
	private ?object $model = null;


    public function __construct(App $app)
    {
    	$this->setModel($app);
    }

    public function doAction(App $app, string $action, array $params = []): array 
    {
    	if ($action == 'register') {
    		return $this->doRegisterUser($app, $params);
    	}
    	if ($action == 'readtnc') {
    		return $this->doPassTNC($app,$params);
    	}
    	if ($action == 'checktnc') {
    		return $this->doCheckTNC($app, $params);
    	}
    }

    protected function doRegisterUser(App $app, array $params = []): array 
    {
    	$response = array('success' => true, 'errMsg' => '');
    	$params['name'] = trim($params['name']);
    	$params['email'] = trim($params['email']);

    	try {
            $result = $this->model->save($params);

            if (! $result['success']) {
            	$response = array('success' => $result['success'], 'errMsg' => $result['errMsg']);
            }

            unset($params['name'], $params['email']);

            $boothController = $app->getController('Tracking');
            $result = $boothController->doAction($app, 'register', $params);

            if (! $result['success']) {
                $response = array('success' => $result['success'], 'errMsg' => $result['errMsg']);
            }
        } catch (\PDOException $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }

        return $response;
    }

    protected function doPassTNC(App $app, array $params = []): array 
    {
    	$response = array('success' => true, 'errMsg' => '');

    	try {
            $result = $this->model->update($params);

            if (! $result['success']) {
            	$response = array('success' => $result['success'], 'errMsg' => $result['errMsg']);
            }
        } catch (\PDOException $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }

        return $response;
    }

    protected function doCheckTNC(App $app, array $params = []): array 
    {
    	$bSuccess = true;
    	$response = array('success' => true, 'errMsg' => '');

    	try {
            $result = $this->model->select(['id', 'isreadtnc']);

            if (! $result['success']) {
            	$response = array('success' => $result['success'], 'errMsg' => $result['errMsg']);
            }

            $response['data'] = $result['data'];
        } catch (\PDOException $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }

        return $response;
    }

    private function setModel(App $app): void
    {
    	$className = 'App\Model\UserModel';
        $reflection = new \ReflectionClass($className);

    	$this->model = $reflection->newInstanceArgs([$app]);
    }
}

?>