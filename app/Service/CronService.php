<?php

namespace App\Service;

use App\Model\CronModel;
use DateTime;
use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use App\Model\LogModel;
use App\Model\Translator;
class CronService
{

	public $cronModel;
	public $jobs;
	public $client;
	public $logModel;
	public $translator;
	public function __construct(GuzzleHttp\Client $client, CronModel $cronModel, LogModel $logModel, Translator $translator)
	{
		$this->cronModel = $cronModel;
		$this->client = $client;
		$this->logModel = $logModel;
		$this->translator = $translator;
	}
	
	/**
	 * @return void
	 */
	public function parseJobs()
	{
		$jobs = $this->cronModel->getJobs();
		$now = new DateTime();
		$dayOfWeek = $now->format('w');
		$hour = $now->format('H');
		$minute = $now->format('i');
		$cronJobs = array();
		foreach ($jobs as $job) {
			if (($job->dayofweek == '*') || ($job->dayofweek == $dayOfWeek))
			{
				if (($job->hour == '*') || ($job->hour == $hour))
				{
					$minutes = explode('/', $job->minutes);
					/* Vícekrát za hodinu */
					if ($minutes[0] == '*'){
						if (((int)$minute % (int)$minutes[1]) == 0)
						{
							$cronJobs[] = array('id'=>$job->id,
								'name' => $job->name,
								'presenter' => $job->presenter,
								'action' => $job->action,
								'dayOfWeek' => $job->dayofweek,
								'hour' => $job->hour,
								'minutes' => $job->minutes);
							$this->cronModel->setLock($job->id);
						}
					} else {
						/* Přesně na čas */
						if ($minute == $minutes[0]){
							$cronJobs[] = array('id'=>$job->id,
								'name' => $job->name,
								'presenter' => $job->presenter,
								'action' => $job->action,
								'dayOfWeek' => $job->dayofweek,
								'hour' => $job->hour,
								'minutes' => $job->minutes);
							$this->cronModel->setLock($job->id);
						}
					}
				}
			}
			$this->jobs = $cronJobs;
		}
	}
	
	/**
	 * @return void
	 */
	public function executeJobs()
	{
		$this->parseJobs();
		if (isset($this->jobs)) {
			foreach ($this->jobs as $job) {
				$this->call('http://adam.devel/'.$job['presenter'].'/'.$job['action'].'/');
				/* Odemknout po spuštění */
				$this->cronModel->unLock($job['id']);
			}
		}
	}
	
	/**
	 * @param string $url
	 * @return int|void
	 */
	public function call(string $url){
		try {
			$response = $this->client->request('GET', $url);
			$this->logModel->log($url,$this->translator->translate('task_run_successfully'));
			return $response->getStatusCode();
		} catch (GuzzleException $e)
		{
			$this->logModel->log($url,$this->translator->translate('task_run_unsuccessfully'));
		}
	}
}