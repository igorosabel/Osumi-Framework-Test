<?php
class OPostInstall {
	private $config = null;
	private $messages = [
		'es' => [
					'TITLE'                => "\n\nPOST INSTALL 5.2.0\n\n",
					'SERVICES_UPDATING'    => "Actualizando services...\n",
					'SERVICE_UPDATED'      => "  Service \"%s\" actualizado.\n",
					'SERVICE_NOT_CLEAN'    => "    El servicio sigue teniendo referencias a getController, deberá ser revisado manualmente.\n",
					'CONTROLLERS_UPDATING' => "Actualizando controllers...\n",
					'CONTROLLER_UPDATED'   => "  Controller \"%s\" actualizado.\n",
					'END_TITLE'            => "\n\nPOST INSTALL 5.2.0 finalizado.\n\n"
				],
		'en' => [
					'TITLE'                => "\n\nPOST INSTALL 5.2.0\n\n",
					'SERVICES_UPDATING'    => "Updating services...\n",
					'SERVICE_UPDATED'      => "  Service \"%s\" has been updated.\n",
					'SERVICE_NOT_CLEAN'    => "    The service still has references to getController, must be checked manually.\n",
					'CONTROLLERS_UPDATING' => "Updating controllers...\n",
					'CONTROLLER_UPDATED'   => "  Controller \"%s\" has been updated.\n",
					'END_TITLE'            => "\n\nPOST INSTALL 5.2.0 finished.\n\n"
				]
	];

	public function __construct() {
		global $core;
		$this->config = $core->config;
	}

	private function updateService($service) {
		$path = $this->config->getDir('app_service').$service.'.php';
		$content = file_get_contents($path);

		$content = str_ireplace('__construct($'.'controller)',            '__construct()',         $content);
		$content = str_ireplace('setController($'.'controller)',          'loadService()',         $content);
		$content = str_ireplace('$'.'this->getController()->getDB()',     'new ODB()',             $content);
		$content = str_ireplace('$'.'this->getController()->getConfig()', '$'.'this->getConfig()', $content);

		file_put_contents($path, $content);

		echo sprintf($this->messages[$this->config->getLang()]['SERVICE_UPDATED'], $service);

		if (stripos($content, 'getController')!==false) {
			echo $this->messages[$this->config->getLang()]['SERVICE_NOT_CLEAN'];
		}
	}

	private function updateController($controller, $services) {
		$path = $this->config->getDir('app_controller').$controller.'.php';
		$content = file_get_contents($path);

		foreach ($services as $service) {
			$content = str_ireplace('new '.$service.'Service($'.'this)', 'new '.$service.'Service()', $content);
		}

		file_put_contents($path, $content);

		echo sprintf($this->messages[$this->config->getLang()]['CONTROLLER_UPDATED'], $controller);
	}

	public function run() {
		echo $this->messages[$this->config->getLang()]['TITLE'];
		$services = [];

		echo $this->messages[$this->config->getLang()]['SERVICES_UPDATING'];
		if (file_exists($this->config->getDir('app_service'))) {
			if ($model = opendir($this->config->getDir('app_service'))) {
				while (false !== ($entry = readdir($model))) {
					if ($entry != '.' && $entry != '..') {
						$service = str_ireplace('.php', '', $entry);
						array_push($services, $service);
						$this->updateService($service);
					}
				}
				closedir($model);
			}
		}

		echo $this->messages[$this->config->getLang()]['CONTROLLERS_UPDATING'];
		if (file_exists($this->config->getDir('app_controller'))) {
			if ($model = opendir($this->config->getDir('app_controller'))) {
				while (false !== ($entry = readdir($model))) {
					if ($entry != '.' && $entry != '..') {
						$controller = str_ireplace('.php', '', $entry);
						$this->updateController($controller, $services);
					}
				}
				closedir($model);
			}
		}

		echo $this->messages[$this->config->getLang()]['END_TITLE'];
	}
}