<?php

namespace App\Model;

class RolesModel extends DBModel
{

    /**
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function getAllRoles()
    {
        return $this->db->table('roles')->fetchAll();
    }

    /**
     * @return array
     */
    public function getAllSelectRoles()
    {
        return $this->db->table('roles')->fetchPairs('id', 'name');
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getRole(int $id)
    {
        return $this->db->table('roles')
            ->where('id', $id)
            ->fetch();
    }

    /**
     * @param string $name
     * @param int $id
     * @return void
     */
    public function editRole(string $name, int $id)
    {
        $data = array('name' => $name);
        $this->db->table('roles')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * @param string $name
     * @return void
     */
    public function addRole(string $name)
    {
        $data = array('name' => $name);
        $this->db->table('roles')
            ->insert($data);
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getModuleById(int $id)
    {
        return $this->db->table('modules')->where('id', $id)->fetch();
    }

    /**
     * @param string $name
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getModuleByName(string $name)
    {
        return $this->db->table('modules')->where('name', $name)->fetch();
    }

	/**
	 * @param int $id
	 * @return array|\Nette\Database\IRow[]
	 */
    public function getRoleRights(int $id)
    {
        return $this->db->query('SELECT ro.id, riro.rights AS `rights`, mo.name
							  FROM roles ro
							  JOIN right2roles riro ON riro.role_id = ro.id
							  JOIN modules mo ON mo.id = riro.module_id
							  WHERE ro.id = ?', $id)->fetchAll();
    }

    /**
     * Set right to role
     * @param int $role_id
     * @param int $module_id
     * @param $right
     * @return void
     */
    public function setRoleRight(int $role_id, int $module_id, $right)
    {
        $data = array('role_id' => $role_id, 'module_id' => $module_id, 'rights' => $right);
        $exist = $this->db->table('right2roles')
            ->where('role_id', $role_id)
            ->where('module_id', $module_id)
            ->fetch();
        if ($exist) {
            $this->db->table('right2roles')
                ->where('role_id', $role_id)
                ->where('module_id', $module_id)
                ->update($data);
        } else {
            $this->db->table('right2roles')
                ->insert($data);
        }
    }


    /**
     * List modules and rights to role
     * @param int $id
     * @return array|\Nette\Database\IRow[]
     */
    public function getRoleModules(int $role_id)
    {
        return $this->db->query('SELECT mo.name, 
									    	mo.id, 
		 								    (SELECT 1 
		 								     FROM right2roles riro 
		 								     WHERE riro.module_id = mo.id 
		 								       AND riro.role_id = ?) AS assigned,
    										(SELECT rights 
		 								     FROM right2roles riro 
		 								     WHERE riro.module_id = mo.id 
		 								       AND riro.role_id = ?) AS rights,
    										mo.description
									 FROM modules mo
									 ORDER BY mo.name', $role_id, $role_id)->fetchAll();
    }

    public function setRoleModules(int $role_id, int $module_id)
    {
        $exist = $this->db->table('right2roles')
            ->where('role_id', $role_id)
            ->where('module_id', $module_id)
            ->fetch();
        if ($exist) {
            $this->db->table('right2roles')
                ->where('role_id', $role_id)
                ->where('module_id', $module_id)
                ->delete();
        } else {
            $data = array('role_id' => $role_id, 'module_id' => $module_id);
            $this->db->table('right2roles')->insert($data);
        }
    }
}