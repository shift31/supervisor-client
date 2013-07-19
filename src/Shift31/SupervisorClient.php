<?php

namespace Shift31;

use Zend\XmlRpc\Client;

class SupervisorClient
{
	protected $_service;
	protected $_logger;


	/**
	 * @param string     $username
	 * @param string     $password
	 * @param string     $hostname
	 * @param int        $port
	 * @param null|mixed $logger
	 */
	public function __construct($username, $password, $hostname = '127.0.0.1', $port = 9001, $logger = null)
	{
		$this->_logger = $logger;

		// Connect to Supervsior XMLRPC server
		// MUST add '/RPC2' to URI!!!
		$xmlRpcClient = new Client("http://$username:$password@$hostname:$port/RPC2");
		$xmlRpcClient->setSkipSystemLookup(true);

		$this->_service = $xmlRpcClient->getProxy();

	}


	public function getState()
	{
		$supervisorState = null;

		try {
			$state = $this->_service->supervisor->getState();
			$supervisorState = $state['statename'];
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		return $supervisorState;
	}


	public function getAllProcessInfo()
	{
		$allProcessInfo = null;

		try {
			$allProcessInfo = $this->_service->supervisor->getAllProcessInfo();
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		return $allProcessInfo;
	}


	/**
	 * @return array
	 */
	public function getAllProcessesByPid()
	{
		$processes = array();

		try {
			$allProcessInfo = $this->getAllProcessInfo();

			foreach ($allProcessInfo as $process) {
				$processes[$process['pid']] = $process;
			}
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		return $processes;
	}


	/**
	 * @param $name
	 *
	 * @return null|string
	 */
	public function getProcessInfo($name)
	{
		$processInfo = null;

		try {
			$processInfo = $this->_service->supervisor->getProcessInfo($name);
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		return $processInfo;
	}


	/**
	 * @param $name
	 *
	 * @return null|string
	 */
	public function getProcessState($name)
	{
		$processState = null;

		try {
			$processInfo = $this->_service->supervisor->getProcessInfo($name);
			$processState = $processInfo['statename'];
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		return $processState;
	}


	/**
	 * @param      $name
	 * @param bool $wait
	 *
	 * @return bool
	 */
	public function startProcess($name, $wait = true)
	{

		$result = false;

		try {
			$result = $this->_service->supervisor->startProcess($name, $wait);
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		if ($result === true) {
			$this->_log('notice', "Started process '$name'");
		} else {
			$this->_log('err', "Failed to start process '$name'");
		}

		return $result;
	}


	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function stopProcess($name)
	{

		$result = false;

		try {
			$result = $this->_service->supervisor->stopProcess($name);
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		if ($result === true) {
			$this->_log('notice', "Stopped process '$name'");
		} else {
			$this->_log('err', "Failed to stop process '$name'");
		}

		return $result;
	}


	/**
	 * @param string	$name
	 * @param bool		$wait
	 *
	 * @return bool
	 */
	public function startProcessGroup($name, $wait = true)
	{

		$result = false;

		try {
			$result = $this->_service->supervisor->startProcessGroup($name, $wait);
		} catch (\Exception $e) {
			$this->_log('crit', "Exception: " . $e->getCode() . " - " . $e->getMessage());
			$this->_log('debug', "Exception Stack Trace: " . $e->getTraceAsString());
		}

		if ($result === true) {
			$this->_log('notice', "Started process group '$name'");
		} else {
			$this->_log('err', "Failed to start process '$name'");
		}

		return $result;
	}


	/**
	 * @param string $priority
	 * @param string $message
	 */
	protected function _log($priority, $message)
	{
		if ($this->_logger != null) {
			$class = str_replace(__NAMESPACE__ . "\\", '', get_called_class());
			$this->_logger->$priority("[$class] - $message");
		}
	}
}