<?php

namespace App\Model;

use Nette\Utils\ArrayHash;

class CertificatesModel extends DBModel
{

    /**
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function listAllCertificates()
    {
        return $this->db->table('certificates')->fetchAll();
    }
	
	/**
	 * @param int $id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getCertificateByID(int $id)
	{
		return $this->db->query('SELECT c.id, ct.name, c.validfrom, c.validto, c.certfile, c.stuff_id
									 FROM certificates c
									 JOIN certificates_type ct ON ct.id = c.certtype
									 WHERE c.id = ?', $id)->fetch();
	}
	
	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getAllCertificatesTypes(int $organisation_id)
	{
		return $this->db->table('certificates_type')->where('organisation_id', $organisation_id)->fetchAll();
	}
	
	public function getCertificateType(int $id, int $organisation_id)
	{
		return $this->db->table('certificates_type')->where('id', $id)->where('organisation_id', $organisation_id)->fetch();
	}
	
	
    /**
     * @param int $stuff_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getStuffAllCertificates(int $stuff_id)
    {
        return $this->db->query('SELECT c.*
                                    FROM certificates c
                                    JOIN stuff s ON s.id = c.stuff_id
                                    JOIN users u ON u.id = s.user_id
                                    WHERE s.id = ?', $stuff_id)->fetchAll();
    }

    /**
     * @param int $stuff_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getStuffValidCertificates(int $stuff_id)
    {
        return $this->db->query('SELECT c.*
                                    FROM certificates c
                                    JOIN stuff s ON s.id = c.stuff_id
                                    JOIN users u ON u.id = s.user_id
                                    WHERE s.id = ?
                                    AND c.validto < NOW()', $stuff_id)->fetchAll();
    }

    /**
     * @param int $organisation_id
     * @param int $cert_type
     * @return array|\Nette\Database\IRow[]
     */
    public function getValidStuffCertificatesByType(int $organisation_id, int $cert_type)
    {
        $organisation_where = $organisation_id > 0 ? ' AND s.organisation_id = ? ' : ' and 0 = ?';
        return $this->db->query('SELECT s.*, c.validfrom, c.validto, c.certfile, c.status
                                     FROM stuff s
                                     JOIN users u ON u.id = s.user_id
                                     JOIN certificates c ON c.stuff_id = s.id
                                     JOIN certificates_type ct ON ct.id = c.certtype
                                     WHERE ct.id = ? ' . $organisation_where,
            $cert_type, $organisation_id)->fetchAll();
    }
	
	/**
	 * @param $data
	 * @return void
	 */
	public function addCertificateType($data){
		$this->db->table('certificates_type')->insert($data);
	}
	
	/**
	 * @param $data
	 * @return void
	 */
	public function updateCertificateType($data){
		$this->db->table('certificates_type')->where('id',$data->id)->update($data);
	}
	
	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveCertificate(ArrayHash $data)
	{
		if ($data->id)
		{
			$this->db->table('certificates')->where('id', $data->id)->update($data);
		} else
		{
			$this->db->table('certificates')->insert($data);
		}
	}
	
}