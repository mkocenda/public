<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Utils\ArrayHash;
use Nette;

class SettingsModel extends DBModel
{

    /** @var Nette\DI\Container @inject */
    public $container;
    public function __construct(Context $db, Nette\DI\Container $container)
    {
        parent::__construct($db);
        $this->container = $container;
    }

    /**
	 * @param string $module
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getSettingsByModule(string $module)
	{
		return $this->db->table('settings')->where('module', $type)->fetchAll();
	}

	/**
	 * @param string $module
	 * @param string $property
	 * @param int $organisation_id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getSettingsByModuleProperty(string $module, string $property, int $organisation_id)
	{
		return $this->db->table('settings')
			->where('module', $module)
			->where('property', $property)
			->where('organisation_id', $organisation_id)
			->select('value')->fetch();
	}

	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getSettingByOrganisationID(int $organisation_id)
	{
		return $this->db->table('settings')->where('organisation_id', $organisation_id)->fetchAll();
	}

	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getOperatorsByOrganisationID(int $organisation_id)
	{
		return $this->db->table('message_operators')->where('organisation_id', $organisation_id)->fetchAll();
	}

    /**
     * @return array
     */
    public function getGlobalSettings(){
        return $this->container->parameters;
    }

	/**
	 * @param int $organisation_id
	 * @return void
	 */
	public function resetOperatorsByOrganisationID(int $organisation_id)
	{
		$data = array('enabled'=>0);
		$this->db->table('message_operators')->where('organisation_id', $organisation_id)->update($data);
	}

	/**
	 * @param int $id
	 * @param int $organisation_id
	 * @return void
	 */
	public function setOperator(int $id, int $organisation_id)
	{
		$data = array('enabled'=>1);
		$this->db->table('message_operators')
			->where('organisation_id', $organisation_id)
			->where('id', $id)
			->update($data);
	}

	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveSettingsModuleProperty(ArrayHash $data)
	{
		$this->db->table('settings')
			->where('organisation_id', $data->organisation_id)
			->where('module', $data->module)
			->where('property', $data->property)
			->update($data);
	}
}