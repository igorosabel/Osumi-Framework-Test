<?php declare(strict_types=1);
class OPostInstall {
	private ?OColors $colors = null;
	private ?OConfig $config = null;
	private array    $messages = [
		'es' => [
			'TITLE'                    => "\nPOST INSTALL 7.0.0\n\n",
			'ADD_NAMESPACE_TO_MODEL'   => "  Archivo de modelo actualizado: \"%s\"\n",
			'ADD_NAMESPACE_TO_MODULE'  => "  Archivo de módulo actualizado: \"%s\"\n",
			'ADD_NAMESPACE_TO_SERVICE' => "  Archivo de servicio actualizado: \"%s\"\n",
			'ADD_NAMESPACE_TO_TASK'    => "  Archivo de tarea actualizado: \"%s\"\n",
			'CORE_FOLDER_DELETE'       => "  Carpeta \"%s\" antigua eliminada.\n",
			'POST_UPDATE'              => "  El archivo \"%s\" no se puede actualizar automaticamente. Por favor, ejecuta los siguientes comandos:\n\n",
			'END_TITLE'                => "\nPOST INSTALL 7.0.0 finalizado.\n\n"
		],
		'en' => [
			'TITLE'                    => "\n\nPOST INSTALL 7.0.0\n\n",
			'ADD_NAMESPACE_TO_MODEL'   => "  Model file updated: \"%s\"\n",
			'ADD_NAMESPACE_TO_MODULE'  => "  Module file updated: \"%s\"\n",
			'ADD_NAMESPACE_TO_SERVICE' => "  Service file updated: \"%s\"\n",
			'ADD_NAMESPACE_TO_TASK'    => "  Task file updated: \"%s\"\n",
			'CORE_FOLDER_DELETE'       => "  Old \"%s\" folder deleted.\n",
			'POST_UPDATE'              => "  File \"%s\" could not be updated automatically. Please, run the following commands:\n\n",
			'END_TITLE'                => "\nPOST INSTALL 7.0.0 finished.\n\n"
		]
	];
	private array $models_list = [];

	/**
	 * Store global configuration locally
	 */
	public function __construct() {
		global $core;
		$this->config = $core->config;
		$this->colors = new OColors();
	}

	/**
	 * Add namespace support to model file
	 *
	 * @param string $model Name of the model file
	 *
	 * @return string Returns information messages
	 */
	private function addNamespaceToModel(string $model): string {
		$ret = sprintf($this->messages[$this->config->getLang()]['ADD_NAMESPACE_TO_MODEL'],
			$this->colors->getColoredString($model, 'light_green')
		);

		$model_path = $this->config->getDir('app_model').$model.'.php';
		$model_content = file_get_contents($model_path);
		$model_content = str_ireplace("\r\n", "\n", $model_content);
		$model_content = str_ireplace(
			"<?php declare(strict_types=1);\n",
			"<?php declare(strict_types=1);\n\nnamespace OsumiFramework\\App\\Model;\n\nuse OsumiFramework\\OFW\\DB\\OModel;\n\n",
			$model_content
		);

		file_put_contents($model_path, $model_content);

		return $ret;
	}

	/**
	 * Add namespace support to module file
	 *
	 * @param string $module Name of the module file
	 *
	 * @return string Returns information messages
	 */
	private function addNamespaceToModule(string $module): string {
		$ret = sprintf($this->messages[$this->config->getLang()]['ADD_NAMESPACE_TO_MODULE'],
			$this->colors->getColoredString($module, 'light_green')
		);

		$module_path = $this->config->getDir('app_module').$module.'/'.$module.'.php';
		$module_content = file_get_contents($module_path);
		$module_content = str_ireplace("\r\n", "\n", $module_content);
		$module_content = str_ireplace(
			"<?php declare(strict_types=1);\n",
			"<?php declare(strict_types=1);\n\nnamespace OsumiFramework\\App\\Module;\n\nuse OsumiFramework\\OFW\\Core\\OModule;\nuse OsumiFramework\\OFW\\Web\\ORequest;\nuse OsumiFramework\\OFW\\Routing\\ORoute;\n",
			$module_content
		);
		$module_content .= $this->addDB($module_content);
		$module_content .= "\n";
		file_put_contents($module_path, $module_content);

		return $ret;
	}

	/**
	 * Add namespace support to service file
	 *
	 * @param string $service Name of the service file
	 *
	 * @return string Returns information messages
	 */
	private function addNamespaceToService(string $service): string {
		$ret = sprintf($this->messages[$this->config->getLang()]['ADD_NAMESPACE_TO_SERVICE'],
			$this->colors->getColoredString($service, 'light_green')
		);

		$service_path = $this->config->getDir('app_service').$service.'.php';
		$service_content = file_get_contents($service_path);
		$service_content = str_ireplace("\r\n", "\n", $service_content);
		$service_content = str_ireplace(
			"<?php declare(strict_types=1);\n",
			"<?php declare(strict_types=1);\n\nnamespace OsumiFramework\\App\\Service;\n\nuse OsumiFramework\\OFW\\Core\\OService;\n",
			$service_content
		);
		$service_content .= $this->addDB($service_content);
		$service_content .= "\n";
		file_put_contents($service_path, $service_content);

		return $ret;
	}

	/**
	 * Add namespace support to task file
	 *
	 * @param string $task Name of the task file
	 *
	 * @return string Returns information messages
	 */
	private function addNamespaceToTask(string $task): string {
		$ret = sprintf($this->messages[$this->config->getLang()]['ADD_NAMESPACE_TO_TASK'],
			$this->colors->getColoredString($task, 'light_green')
		);

		$task_path = $this->config->getDir('app_task').$task.'.php';
		$task_content = file_get_contents($task_path);
		$task_content = str_ireplace("\r\n", "\n", $task_content);
		$task_content = str_ireplace(
			"<?php declare(strict_types=1);\n",
			"<?php declare(strict_types=1);\n\nnamespace OsumiFramework\\App\\Task;\n\nuse OsumiFramework\\OFW\\Core\\OTask;\n",
			$task_content
		);
		$task_content .= $this->addDB($task_content);
		$task_content .= "\n";
		file_put_contents($task_path, $task_content);

		return $ret;
	}

	/**
	 * Add database support, both ODB and model classes
	 *
	 * @param string $content Content of the code file to be checked
	 *
	 * @return string Content with new namespaces
	 */
	private function addDB(string $content): string {
		// Check if ODB is used and add it if necessary
		if (stripos($content, "new ODB")!==false) {
			$content .= "use OsumiFramework\\OFW\\DB\\ODB;\n";
		}
		// Check if model classes are used and add them if necessary
		foreach ($this->models_list as $model) {
			if (stripos($content, "new ".ucfirst($model))!==false) {
				$content .= "use OsumiFramework\\App\Model\\".ucfirst($model).";\n";
			}
		}

		return $content;
	}

	/**
	 * Runs the v6.1.0 update post-installation tasks
	 *
	 * @return string
	 */
	public function run(): string {
		$ret = '';
		$ret .= $this->messages[$this->config->getLang()]['TITLE'];

		// Add namespaces

		// Models
		$models_path = $this->config->getDir('app_model');
		if (file_exists($models_path)) {
			if ($model = opendir($models_path)) {
				while (false !== ($entry = readdir($model))) {
					if ($entry != '.' && $entry != '..') {
						$entry = str_ireplace(".php", "", $entry);
						$ret .= $this->addNamespaceToModel($entry);
						array_push($this->models_list, $entry);
					}
				}
				closedir($model);
			}
		}

		// Modules
		$modules_path = $this->config->getDir('app_module');
		if (file_exists($modules_path)) {
			if ($module = opendir($modules_path)) {
				while (false !== ($entry = readdir($module))) {
					if ($entry != '.' && $entry != '..') {
						$ret .= $this->addNamespaceToModule($entry);
					}
				}
				closedir($module);
			}
		}

		// Services
		$services_path = $this->config->getDir('app_service');
		if (file_exists($services_path)) {
			if ($service = opendir($services_path)) {
				while (false !== ($entry = readdir($service))) {
					if ($entry != '.' && $entry != '..') {
						$entry = str_ireplace(".php", "", $entry);
						$ret .= $this->addNamespaceToService($entry);
					}
				}
				closedir($service);
			}
		}

		// Tasks
		$tasks_path = $this->config->getDir('app_task');
		if (file_exists($tasks_path)) {
			if ($task = opendir($tasks_path)) {
				while (false !== ($entry = readdir($task))) {
					if ($entry != '.' && $entry != '..') {
						$entry = str_ireplace(".php", "", $entry);
						$ret .= $this->addNamespaceToTask($entry);
					}
				}
				closedir($task);
			}
		}

		// Tasks
		$core_path = $this->config->getDir('ofw_core');
		if (file_exists($core_path)) {
			if ($core_file = opendir($core_path)) {
				while (false !== ($entry = readdir($core_file))) {
					if ($entry != '.' && $entry != '..') {
						echo "CORE FILE: ".$entry."\n";
					}
				}
				closedir($core_file);
			}
		}

		rmdir($this->config->getDir('ofw_core'));
		$ret .= sprintf($this->messages[$this->config->getLang()]['CORE_FOLDER_DELETE'],
			$this->colors->getColoredString("ofw/core", 'light_green')
		);

		$ret .= sprintf($this->messages[$this->config->getLang()]['POST_UPDATE'],
			$this->colors->getColoredString("ofw/template/update/update.php", 'light_green')
		);

		$ret .= "    ".$this->colors->getColoredString("rm ofw/template/update/update.php", 'light_green')."\n";
		$ret .= "    ".$this->colors->getColoredString("mv ofw/template/update/update_7.php ofw/template/update/update.php", 'light_green')."\n";

		$ret .= $this->messages[$this->config->getLang()]['END_TITLE'];

		return $ret;
	}
}