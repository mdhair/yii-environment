<?php

/**
 * @name Environment
 * @author Marco van 't Wout | Tremani
 * @version 2.2
 * 
 * =Environment-class=
 * 
 * Original sources: http://www.yiiframework.com/doc/cookbook/73/
 * 
 * Simple class used to set configuration and debugging depending on environment.
 * Using this you can predefine configurations for use in different environments,
 * like _development, testing, staging and production_.
 * 
 * The main config is extended to include the Yii path and debug flags.
 * There are mode_x.php files to override and extend the main config for specific implementation.
 * You can optionally use a local config to override these preset configurations, for
 * example when using multiple development instalations with different paths, db's.
 * 
 * This class was designed to have minimal impact on the default Yii generated files.
 * Minimal changes to the index/bootstrap and existing config files are needed.
 * 
 * The Environment is determined by $_SERVER[YII_ENVIRONMENT], created
 * by Apache's SetEnv directive. This can be modified in getMode()
 *
 * It is also possible to set up console config, but it cannot directly determine environment
 * through the server variable. It determines the environment by reading configDir/mode.php, which
 * is generated by running the webapp or can be created manually.
 *
 * If you want to customize this class or its config and modes, extend it (see ExampleEnvironment.php)
 * 
 * ==Setting environment==
 * 
 * Setting environment can be done in the httpd.conf or in a .htaccess file
 * See: http://httpd.apache.org/docs/1.3/mod/mod_env.html#setenv
 * 
 * Httpd.conf example:
 * 
 * {{{
 * <Directory "C:\www">
 *     # Set Yii environment
 *     SetEnv YII_ENVIRONMENT DEVELOPMENT
 * </Directory>
 * }}}
 * 
 * ==Installation==
 * 
 *  # Put the yii-environment directory in `protected/extensions/`
 *  # Modify your index.php (and other bootstrap files)
 *  # Modify your main.php config file and add mode specific configs
 *  # Set your local environment
 * 
 * ===Index.php usage example:===
 * 
 * See `yii-environment/example-index/` or use the following code block:
 * 
 * {{{
 * <?php
 * // set environment
 * require_once(dirname(__FILE__) . '/protected/extensions/yii-environment/Environment.php');
 * $env = new Environment();
 * //$env = new Environment('PRODUCTION'); //override mode
 * 
 * // set debug and trace level
 * defined('YII_DEBUG') or define('YII_DEBUG', $env->yiiDebug);
 * defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', $env->yiiTraceLevel);
 * 
 * // run Yii app
 * //$env->showDebug(); // show produced environment configuration
 * require_once($env->yiiPath);
 * $env->runYiiStatics(); // like Yii::setPathOfAlias()
 * Yii::createWebApplication($env->configWeb)->run();
 * }}}
 * 
 * ===Structure of config directory===
 * 
 * Your `protected/config/` directory will look like this:
 * 
 *  * config/main.php                     (Global configuration)
 *  * config/mode_development.php         (Mode-specific configurations)
 *  * config/mode_test.php
 *  * config/mode_staging.php
 *  * config/mode_production.php
 *  * config/local.php                    (Local override for mode-specific config. Don't put in your SVN!)
 *  * config/mode.php                     (Used for determining console environment. Don't put in your SVN!)
 * 
 * ===Modify your config/main.php===
 * 
 * See `yii-environment/example-config/` or use the following code block:
 * Optional: in configConsole you can copy settings from configWeb by
 * using value key `inherit` (see examples folder).
 * 
 * {{{
 * <?php
 * return array(
 *     // Set yiiPath (relative to Environment.php)
 *     'yiiPath' => dirname(__FILE__) . '/../../../yii/framework/yii.php',
 *     'yiicPath' => dirname(__FILE__) . '/../../../yii/framework/yiic.php',
 *     'yiitPath' => dirname(__FILE__) . '/../../../yii/framework/yiit.php',
 * 
 *     // Set YII_DEBUG and YII_TRACE_LEVEL flags
 *     'yiiDebug' => true,
 *     'yiiTraceLevel' => 0,
 * 
 *     // Static function Yii::setPathOfAlias()
 *     'yiiSetPathOfAlias' => array(
 *         // uncomment the following to define a path alias
 *         //'local' => 'path/to/local-folder'
 *     ),
 * 
 *     // This is the main Web application configuration. Any writable
 *     // CWebApplication properties can be configured here.
 *     'configWeb' => array(
 *         (...)
 *     ),
 * 
 *     // This is the Console application configuration. Any writable
 *     // CConsoleApplication properties can be configured here.
 *     // Leave array empty if not used.
 *     // Use value 'inherit' to copy from generated configWeb.
 *     'configConsole' => array(
 *         (...)
 *     ),
 * );
 * }}}
 * 
 * ===Create mode-specific config files===
 * 
 * Create `config/mode_<mode>.php` files for the different modes
 * These will override or merge attributes that exist in the main config.
 * Optional: also create a `config/local.php` file for local overrides
 * 
 * {{{
 * <?php
 * return array(
 *     // Set yiiPath (relative to Environment.php)
 *     //'yiiPath' => dirname(__FILE__) . '/../../../yii/framework/yii.php',
 *     //'yiicPath' => dirname(__FILE__) . '/../../../yii/framework/yiic.php',
 *     //'yiitPath' => dirname(__FILE__) . '/../../../yii/framework/yiit.php',
 * 
 *     // Set YII_DEBUG and YII_TRACE_LEVEL flags
 *     'yiiDebug' => true,
 *     'yiiTraceLevel' => 0,
 * 
 *     // Static function Yii::setPathOfAlias()
 *     'yiiSetPathOfAlias' => array(
 *         // uncomment the following to define a path alias
 *         //'local' => 'path/to/local-folder'
 *     ),
 * 
 *     // This is the main Web application configuration. Any writable
 *     // CWebApplication properties can be configured here.
 *     'configWeb' => array(
 *         (...)
 *     ),
 * 
 *     // This is the Console application configuration. Any writable
 *     // CConsoleApplication properties can be configured here.
 *     // Leave array empty if not used
 *     // Use value 'inherit' to copy from generated configWeb
 *     'configConsole' => array(
 *         (...)
 *     ),
 * );
 * }}}
 *
 */
class Environment
{
	// Environment settings (extend Environment class if you want to change these)
	const SERVER_VAR = 'YII_ENVIRONMENT';			//Apache SetEnv var
	const CONFIG_DIR = '../../config/';				//relative to Environment.php
	const MODE_FILE = '../../config/mode.php';		//for writing environment mode to file

	// Valid modes (extend Environment class if you want to change or add to these)
	const MODE_DEVELOPMENT = 100;
	const MODE_TEST = 200;
	const MODE_STAGING = 300;
	const MODE_PRODUCTION = 400;

	// Inherit key that can be used in configConsole
	const INHERIT_KEY = 'inherit';

	// Selected mode
	private $_mode;

	// Environment Yii properties
	public $yiiPath;			// path to yii.php
	public $yiicPath;			// path to yiic.php
	public $yiitPath;			// path to yiit.php
	public $yiiDebug;			// int
	public $yiiTraceLevel;		// int
	
	// Environment Yii statics to run
	// @see http://www.yiiframework.com/doc/api/1.1/YiiBase#setPathOfAlias-detail
	public $yiiSetPathOfAlias = array();	// array with "$alias=>$path" elements
	
	// Application config
	public $configWeb;				// web config array
	public $configConsole;			// console config array

	/**
	 * Initilizes the Environment class with the given mode
	 * @param constant $mode used to override automatically setting mode
	 * @param bool $getModeByFile determine mode by file in config directory
	 */
	function __construct($mode = null, $getModeByFile = false)
	{
		$this->_mode = $this->getMode($mode, $getModeByFile);
		$this->setEnvironment();
	}

	/**
	 * Get current environment mode depending on environment variable.
	 * Override this function if you want to change this method.
	 * @param string $mode
	 * @param bool $getModeByFile determine mode by file in config directory
	 * @return string
	 */
	private function getMode($mode = null, $getModeByFile = false)
	{
		// If not overridden
		if ($mode === null)
		{
			if ($getModeByFile) {
				// Return mode based on certain file content
				if (!file_exists($this->getModeFile()))
					throw new Exception('"Cannot find mode file, please run webapp and/or create mode config file.');

				$mode = file_get_contents($this->getModeFile());
			} else {
			// Return mode based on Apache server var
				if (!isset($_SERVER[constant(get_class($this).'::SERVER_VAR')]))
				throw new Exception('"SetEnv '.constant(get_class($this).'::SERVER_VAR').' <mode>" not defined in Apache config.');

				$mode = $_SERVER[constant(get_class($this).'::SERVER_VAR')];
			}
			$mode = strtoupper($mode);
		}
		
		// Check if mode is valid
		if (!defined(get_class($this).'::MODE_'.$mode))
			throw new Exception('Invalid Environment mode supplied or selected');

		return $mode;
	}

	/**
	 * Sets the environment and configuration for the selected mode
	 */
	private function setEnvironment()
	{
		// Load main config
		$fileMainConfig = $this->getConfigDir().'main.php';
		if (!file_exists($fileMainConfig))
			throw new Exception('Cannot find main config file "'.$fileMainConfig.'".');
		$configMain = require($fileMainConfig);

		// Load specific config
		$fileSpecificConfig = $this->getConfigDir().'mode_'.strtolower($this->_mode).'.php';
		if (!file_exists($fileSpecificConfig))
			throw new Exception('Cannot find mode specific config file "'.$fileSpecificConfig.'".');
		$configSpecific = require($fileSpecificConfig);

		// Merge specific config into main config
		$config = self::mergeArray($configMain, $configSpecific);

		// If one exists, load local config
		$fileLocalConfig = $this->getConfigDir().'local.php';
		if (file_exists($fileLocalConfig)) {
			// Merge local config into previously merged config
			$configLocal = require($fileLocalConfig);
			$config = self::mergeArray($config, $configLocal);
		}

		// Set attributes
		$this->yiiPath = $config['yiiPath'];
		if (isset($config['yiicPath']))
			$this->yiicPath = $config['yiicPath'];
		if (isset($config['yiitPath']))
			$this->yiicPath = $config['yiitPath'];
		$this->yiiDebug = $config['yiiDebug'];
		$this->yiiTraceLevel = $config['yiiTraceLevel'];
		$this->configWeb = $config['configWeb'];
		$this->configWeb['params']['environment'] = strtolower($this->_mode);
		
		// Set console attributes and related actions
		if (isset($config['configConsole']) && !empty($config['configConsole'])) {
			$this->configConsole = $config['configConsole'];

			// Process configConsole for inherits
			$this->processInherits($this->configConsole);
			
			// Write mode file for console config
			if (!file_exists($this->getModeFile())) {
				if (!file_put_contents($this->getModeFile(), strtolower($this->_mode)))
					throw new Exception('Cannot write environment mode file used for config');
			}
			
			$this->configConsole['params']['environment'] = strtolower($this->_mode);
		}

		// Set Yii statics
		$this->yiiSetPathOfAlias = $config['yiiSetPathOfAlias'];
	}

	/**
	 * Run Yii static functions.
	 * Call this function after including the Yii framework in your bootstrap file.
	 */
	public function runYiiStatics()
	{
		// Yii::setPathOfAlias();
		foreach($this->yiiSetPathOfAlias as $alias => $path) {
			Yii::setPathOfAlias($alias, $path);
		}
	}
	
	/**
	 * Show current Environment class values
	 */
	public function showDebug()
	{
		echo '<div style="position: absolute; bottom: 0; z-index: 99; height: 250px; overflow: auto; background-color: #ddd; color: #000; border: 1px solid #000; margin: 5px; padding: 5px;">
			<pre>'.htmlspecialchars(print_r($this, true)).'</pre></div>';
	}
	
	/**
	 * Get full config dir
	 * @return string absolute path to config dir with trailing slash
	 */
	protected function getConfigDir()
	{
		return dirname(__FILE__).DIRECTORY_SEPARATOR.constant(get_class($this).'::CONFIG_DIR').DIRECTORY_SEPARATOR;
	}

	/**
	 * Get mode file path
	 * @return string absolute path to mode config file
	 */
	protected function getModeFile()
	{
		return dirname(__FILE__).DIRECTORY_SEPARATOR.constant(get_class($this).'::MODE_FILE');
	}

	/**
	 * Loop through console config array, replacing values called 'inherit' by values from $this->configWeb
	 * @param type $array target array
	 * @param type $path array that keeps track of current path
	 */
	private function processInherits(&$array, $path = array())
	{
		foreach($array as $key => &$value) {
			if (is_array($value))
				$this->processInherits($value, array_merge($path, array($key)));

			if ($value == self::INHERIT_KEY)
				$value = $this->getValueFromArray($this->configWeb, array_reverse(array_merge($path, array($key))));
		}
	}

	/**
	 * Walk $array through $path until the end, and return value
	 * @param array $array target
	 * @param array $path path array, from deep key to shallow key
	 * @return mixed
	 */
	private function getValueFromArray(&$array, $path)
	{
		if (count($path)>1) {
			$key = end($path);
			return $this->getValueFromArray($array[array_pop($path)], $path);
		} else {
			return $array[reset($path)];
		}

	}

	/**
	 * Merges two arrays into one recursively.
	 * @param array $a array to be merged to
	 * @param array $b array to be merged from
	 * @return array the merged array (the original arrays are not changed.)
	 *
	 * Taken from Yii's CMap::mergeArray, since php does not supply a native
	 * function that produces the required result.
	 * @see http://www.yiiframework.com/doc/api/1.1/CMap#mergeArray-detail
	 */
	private static function mergeArray($a,$b)
	{
		foreach($b as $k=>$v)
		{
			if(is_integer($k))
				$a[]=$v;
			else if(is_array($v) && isset($a[$k]) && is_array($a[$k]))
				$a[$k]=self::mergeArray($a[$k],$v);
			else
				$a[$k]=$v;
		}
		return $a;
	}

}