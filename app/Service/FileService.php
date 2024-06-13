<?php

namespace App\Service;

use App\Model\FileModel;

class FileService
{

    public $fileModel;
    public function __construct(FileModel $fileModel)
    {
        $this->fileModel = $fileModel;
    }

    /**
     * @param string $directory
     * @param string $hashfilename
     * @return void
     */
    public function deleteFile(string $directory, string $hashfilename)
    {
        unlink(__DIR__ . '/../../data/' . $directory . '/' . $hashfilename);
    }


    /**
     * Založí adresářovou strukturu pro ukládání dat
     * @return void
     */
    public function createDataStructure()
    {
        $begin = __DIR__;

        $data = array();
        $data[] = 'data/actions/documents';
        $data[] = 'data/actions/logs';
        $data[] = 'data/actions/participants/documents';
        $data[] = 'data/material';
        $data[] = 'data/organisations';
        $data[] = 'data/stuffs';
        $data[] = 'data/users';

        foreach ($data as $path)
        {
            $dirs = explode('/', $path);
            $__dir = $begin;
            foreach ($dirs as $dir)
            {
               if (is_dir($__dir.'/'.$dir)){
                   $__dir = $__dir.'/'.$dir;
               } else {
                   chdir($__dir);
                   mkdir($dir);
               }
            }
        }
    }

}