<?php

namespace App;

use App\Database;
use Exception;

class App
{
    static protected $instance = null;

    private array $configs = array();

    private ?object $dbHandle = null;


	private function __construct(string $configFile = '', string $operatingMode = '') 
    {
        $this->setConfig($configFile);
    }

    public function run(array $extraParams = []): void
    {
        if (! $this->initDatabase()) {
            throw new \Exception('Unable to initialise the system database. The application was unable to proceed.');
        }

        if (MODE == '0') {
            $this->requestRouterAdmin($extraParams);
        }

        if (MODE == '1') {
            $this->requestRouter($extraParams);
        }
    }

    private function initDatabase(): bool
    {
        $dbconfig = $this->getConfig()['database'];

        try {
            $this->dbHandle = new Database($dbconfig['type'], $dbconfig['host'], $dbconfig['username'], $dbconfig['password'], $dbconfig['database']);
            if (null == $this->dbHandle) {
                $errorMsg = gettext('No connection to the database') . ', ' . $dbconfig['type'] . '://' . $dbconfig['username'] . ':xxxxxxx@' . $dbconfig['host'] . '/' . $dbconfig['database'];
                throw new \Exception($errorMsg);
            }
        } catch (\PDOException $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }

        return true;
    }

    private function requestRouter(array $extraParams = []): void
    {
        $params = (isset($_POST) && ! empty($_POST)) ? $_POST : ((isset($_GET) && ! empty($_GET)) ? $_GET : []);
        $filepath = 'View/main.html';
        $visitpage = '';

        if (isset($params['page'])) {
            $visitpage = $params['page'];
            unset($params['page']);
        }

        if (0 === count($params)) {
            if (isset($_SESSION['userid'])) {
                $controller = $this->getController('User');
                $result = $controller->doAction($this, 'checktnc');

                if ($result['data']['isreadtnc']) {
                    if (! isset($_COOKIE['inevent'])) {
                        setcookie('inevent', 'true', time() + (86400 * 30), '/');
                    }
                }

                if (strlen($visitpage) === 0) {
                    session_destroy();
                }   
            }

            if (strlen($visitpage > 0)) {
                echo json_encode(array('success' => true, 'page' => 'View/' . $visitpage . '.html'));
            } else {
                echo $this->getView($filepath);
            }
        } else {
            $handler = $params['handler'];
            $action = $params['action'];
            unset($params['handler'], $params['action']);

            try {
                $controller = $this->getController($handler);
                $result = $controller->doAction($this, $action, $params);

                echo json_encode($result);
            } catch (\PDOException $error) {
                throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
            }
        }        
    }

    private function requestRouterAdmin(array $extraParams = []): void
    {
        $params = (isset($_POST) && ! empty($_POST)) ? $_POST : ((isset($_GET) && ! empty($_GET)) ? $_GET : []);
        $filepath = 'View/generateevent.html';
        $visitpage = '';

        if (0 === count($params)) {
            echo $this->getView($filepath);
        } else {
            $handler = $params['handler'];
            $action = $params['action'];
            unset($params['handler'], $params['action']);

            try {
                $controller = $this->getController($handler);
                $result = $controller->doAction($this, $action, $params);

                echo json_encode($result);
            } catch (\PDOException $error) {
                throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
            }
        }        
    }

    public function getController($handler): object
    {
        $className = 'App\Controller\\' . $handler . 'Controller';

        $reflection = new \ReflectionClass($className);
        return $reflection->newInstanceArgs([$this]);
    }

    private function getView(string $filepath): string
    {
        try {
            if (! file_exists($filepath)) {
                throw new \Exception(gettext("The view file ({$filepath}) does not exist"));
            }

            $view = file_get_contents($filepath);
            if ($view === false) {
                throw new \Exception(gettext('Unable to read file, ') . $filepath);
            }

            return $view;
        } catch (\Exception $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }
    }

    private function setConfig(string $configFile): void
    {
        try {
            if (! file_exists($configFile)) {
                throw new \Exception(gettext("The config file ({$configFile}) does not exist"));
            }

            $configs_json = file_get_contents($configFile);
            if ($configs_json === false) {
                throw new \Exception(gettext('Unable to read file, ') . $configFile);
            }

            $configs = json_decode($configs_json, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException(gettext('Unable to parse response body into JSON: ') . json_last_error());
            }

            $this->configs = $configs;
        } catch (\Exception $error) {
            throw new \Exception('[' . get_class($this) . ' - ' . __FUNCTION__ . '] ' . $error->getMessage());
        }
    }

    public function getConfig(): array
    {
        return $this->configs;
    }

    public function getDBHandle(): object
    {
        return $this->dbHandle;
    }


    public static function getInstance(string $configFile = '', string $operatingMode = ''): static 
    {
        if (null === self::$instance) {
            self::$instance = new self($configFile, $operatingMode);
        }
        return self::$instance;
    }
}