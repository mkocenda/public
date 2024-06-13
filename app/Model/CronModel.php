<?php

namespace App\Model;

use DateTime;
class CronModel extends DBModel
{
	
	/**
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getJobs()
	{
		return $this->db->table('cron')
			->where('active',Types::ENABLED)
			->where('lock',Types::UNLOCK)
			->fetchAll();
	}
	
	/**
	 * @param $data
	 * @return void
	 */
	public function addJob($data)
	{
		unset($data->cancel);
		$this->db->table('cron')->insert($data);
	}
	
	/**
	 * @param int $id
	 * @return void
	 */
	public function updateRun(int $id)
	{
		$last_run = new DateTime();
		$this->db->table('cron')
			->where('id', $id)
			->update(array('last_run'=>$last_run));
	}
	
	/**
	 * @param int $id
	 * @return void
	 */
	public function setLock(int $id){
		$this->db->table('cron')
			->where('id', $id)
			->update(array('lock'=>Types::LOCK));
	}
	
	/**
	 * @param int $id
	 * @return void
	 */
	public function unLock(int $id){
		$this->db->table('cron')
			->where('id', $id)
			->update(array('lock'=>Types::UNLOCK));
	}
}