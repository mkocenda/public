<?php

namespace App\Model;

class FileModel extends DBModel
{

	/**
	 * Hash filename
	 * @param string $filename
	 * @return string
	 */
	public function hashFileName (string $filename){
		return hash('md5',$filename.time());
	}

	/**
	 * Return image file in BASE64
	 * @param string $filename
	 * @param string $directory
	 * @return false|string|void
	 */
	public function getFileBase64(string $filename, string $directory, $hashfilename = null)
	{
		try {
			if ($hashfilename)
			{
				$file = $this->db->table('files')->where('hashfilename',$hashfilename)->fetch();
				$filename = $file->filename;
			}
			$fileExist = file_exists(__DIR__ . '/../../data/' . $directory . '/' . $filename);
			if ($fileExist) {
				$file = fopen(__DIR__ . '/../../data/' . $directory . '/' . $filename, 'r');
				$content = base64_encode(
					fread($file, filesize(__DIR__ . '/../../data/' . $directory . '/' . $filename))
				);
				fclose($file);
				return $content;
			}
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Get file content
	 * @param string $filename
	 * @param string $directory
	 * @return false|string|void
	 */
	public function getFileContent(string $filename, string $directory, $hashfilename = null)
	{
		try {
			if ($hashfilename)
			{
				$file = $this->db->table('files')->where('hashfilename',$hashfilename)->fetch();
				$filename = $file->filename;
			}
			$fullfilename = __DIR__ . '/../../data/' . $directory . '/' . $filename;
			if (file_exists($fullfilename)) {
				$file = fopen($fullfilename, 'r');
				$content = fread($file, filesize($fullfilename));
				fclose($file);
				return $content;
			}
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Return file record from table
	 * @param int $file_id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getFileName(int $file_id)
	{
		return $this->db->table('files')->where('id', $file_id)->fetch();
	}

	/**
	 * Save file to disk, write to file table
	 * @param string $filename
	 * @param string $directory
	 * @param string $tmpFile
	 * @param $user_id
	 * @param $organisation_id
	 * @return false|string
	 */
	public function saveFile(string $filename, string $directory, string $tmpFile, $user_id = null, $organisation_id = null)
	{
		try {
			$hashFileName = $this->hashFileName($filename);
			$file = __DIR__ . '/../../data/' . $directory . '/' . $hashFileName;
			move_uploaded_file($tmpFile, $file);
			$today = new \DateTime();
			$data = array('origfilename' => $filename, 'hashfilename'=>$hashFileName, 'path' => '/data/' . $directory . '/', 'created_at' => $today, 'user_id' => $user_id, 'organisation_id' => $organisation_id);
			$row = $this->db->table('files')->insert($data);
			return $row->id;
		} catch (\Exception $exception) {
			return false;
		}
	}

    /**
     * Delete file from OS and delete file record's from table
     * @param int $id
     * @return void
     */
    public function deleteFile(int $id)
    {
		$fileRecord = $this->db->table('files')->where('id', $id);
		unlink(__DIR__ . '/../../data/' . $fileRecord->path . '/' . $fileRecord->hashfilenmame);
        $this->db->table('files')->where('id', $id)->delete();
    }
}