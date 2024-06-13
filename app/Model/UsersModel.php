<?php

namespace App\Model;

use Nette\Utils\ArrayHash;
use Ramsey\Uuid\Uuid;
use Nette\DateTime;

class UsersModel extends DBModel
{
	const salt = '{.abcd.}';

	/**
	 * @param string $password
	 * @return string
	 */
	public function hashPassword(string $password)
	{
		return sha1($password . self::salt);
	}

	/**
	 * @param int $id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getUserByID(int $id)
	{
		return $this->db->table('users')
			->where('id', $id)
			->fetch();
	}

	/**
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getAllUsers()
	{
		return $this->db->query('SELECT u.id, u.username, p.email AS p_email, p.name AS p_name, p.surname AS p_surname, p.organisation_id AS p_organisation_id, s.email AS s_email, s.name AS s_name, s.surname AS s_surname, s.organisation_id AS s_organisation_id
									 FROM users u
									 LEFT JOIN parents p ON p.user_id = u.id
									 LEFT JOIN stuff s ON s.user_id = u.id
									 ')->fetchAll();
	}

	/**
	 * @return array
	 */
	public function getUsersSelect()
	{
		return $this->db->table('users')
			->fetchPairs('id', 'username');
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param int $deleted
	 * @param int $id
	 * @return void
	 */
	public function setUser(string $username, $password = null, $deleted = 0, int $id)
	{
		$data = array('username' => $username, 'deleted' => $deleted);
		if ($password) {
			$data['password'] = $this->hashPassword($password);
		}
		$this->db->table('users')
			->where('id', $id)
			->update($data);
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param int $deleted
	 * @return void
	 */
	public function addUser(string $username, string $password, int $deleted)
	{
		$data = array('username' => $username, 'password' => $this->hashPassword($password), 'deteled' => $deleted);
		$this->db->table('users')
			->insert($data);
	}

	/**
	 * @param int $user_id
	 * @param string $module
	 * @param string $right
	 * @return bool
	 */
	public function hasAccess(string $module, string $right, $user_id = 0)
	{
		$result = $this->db->query('SELECT 1 
										FROM rights ri
										JOIN right2roles rr ON rr.right_id = ri.id
										JOIN roles ro ON ro.id = rr.role_id
										JOIN users2roles ur ON ur.role_id = ro.id
										WHERE ur.user_id = ?
										AND ri.module = ?
										AND ri.`right` = ?', $user_id, $module, $right)
			->fetch();
		return (bool)$result;
	}

	/**
	 * @param int $user_id
	 * @return bool|\Nette\Database\IRow|\Nette\Database\Row
	 */
	public function getUserRole(int $user_id)
	{
		return $this->db->query('SELECT r.name, ur.role_id
									 FROM roles r
									 JOIN users2roles ur ON ur.role_id = r.id
									 JOIN users u ON u.id = ur.user_id
									 WHERE u.id = ?', $user_id)
			->fetch();
	}

	/**
	 * @param int $user_id
	 * @param int $role_id
	 * @return void
	 */
	public function setUserRole(int $user_id, int $role_id)
	{
		$data = array('role_id' => $role_id, 'user_id' => $user_id);
		$exist = $this->db->table('users2roles')
			->where('user_id', $user_id)
			->fetch();
		if ($exist) {
			$this->db->table('users2roles')->where('user_id', $user_id)->update($data);
		} else {
			$this->db->table('users2roles')->insert($data);
		}
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function loginUser(string $username, string $password)
	{
		return $this->db->table('users')
			->where('username', $username)
			->where('password', $this->hashPassword($password))
			->fetch();
	}
	
	/**
	 *  Set user HASH and hash_valid_to for API / login
	 * @param ArrayHash $user
	 * @return string
	 */
	public function setUserHash(ArrayHash $user)
	{
		$hash_valid_to = new DateTime('+1 hour');
		$hash = sha1($user->username.$hash_valid_to->format('Y-m-d H:i'));
		$data = array('hash_valid_to'=>$hash_valid_to, 'hash'=>$hash);
		$this->db->table('users')->where('id', $user->id)->update($data);
		return $hash;
	}
	
	/**
	 * @param string $key
	 * @param int $user_id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getSettingsByKey(string $key, int $user_id)
	{
		return $this->db->table('user_settings')
			->where('user_id', $user_id)
			->where('key', $key)
			->fetch();
	}

	/**
	 * @param string $value
	 * @param string $key
	 * @param int $user_id
	 * @return void
	 */
	public function setSettingsByKey(string $value, string $key, int $user_id)
	{
		$data = array('value' => $value);
		$this->db->table('user_settings')
			->where('user_id', $user_id)
			->where('key', $key)
			->update($data);
	}

	/**
	 * @param int $user_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getUserSettingsByKey(int $user_id, string $key)
	{
		return $this->db->query('SELECT se.`type`, se.`key`, se.name,  us.value 
									 FROM user_settings us 
									 JOIN settings se ON se.id = us.settings_id
									 WHERE us.user_id = ? and se.`key` = ?', $user_id, $key)
			->fetchAll();
	}

	/**
	 * @param string $email
	 * @param string $username
	 * @return bool|\Nette\Database\IRow|\Nette\Database\Row
	 */
	public function getUserByEmailUsername(string $email, string $username)
	{
		return $this->db->query('SELECT p.name, p.surname, u.id AS user_id, u.username
                                 FROM parents p
                                 JOIN users u ON u.id = p.user_id
                                 WHERE p.email = ?
                                 AND u.username = ?
                                 AND u.deleted = 0
                                 AND p.confirmed = 1', $email, $username)
			->fetch();
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function addConfirmHash(int $id)
	{
		$hash = Uuid::uuid4();
		$valid_to = new DateTime();
		$valid_to->modify('+10 minutes');
		$data = array('confirm_hash' => $hash, 'valid_to' => $valid_to);
		$this->db->table('users')->where('id', $id)->update($data);
		return $data;
	}

	/**
	 * @param string $config_hash
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getUserByHash(string $config_hash)
	{
		$now = new DateTime();
		return $this->db->table('users')
			->where('confirm_hash', $config_hash)
			->where('valid_to >=', $now)
			->fetch();
	}
	
	/**
	 * @param string $hash
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function isUserHashValid(string $hash)
	{
		return $this->db->table('users')
			->where('hash', $hash)
			->fetch();
	}
}