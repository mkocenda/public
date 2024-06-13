<?php

namespace App\Model;

use Nette\Neon\Neon;
use Nette\Security\Identity;
use Nette\Utils\Json;

class MenuModel
{

    private $menu;
    private $roles;
    public function __construct($type = null, $roles = null)
    {
        $this->menu = $this->loadMenu($type);
        $this->roles = (object)$roles;
    }

    /**
     * @param string $type
     * @return false|mixed
     */
    private function loadMenu(string $type)
    {
        if ($type) {
            $filename = __DIR__ . '/../config/' . $type . 'menu.neon';
            $file = fopen($filename, 'r');
            $content = fread($file, filesize($filename));
            fclose($file);
            return Neon::decode($content);
        }
        return false;
    }

    /**
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    public function getMenu()
    {
        $jsonData = JSON::encode($this->menu);
        return JSON::decode($jsonData);
    }

    /**
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    public function getItems()
    {
        $jsonData = JSON::encode($this->menu);
        $menu = JSON::decode($jsonData);
        $roles = (array)$this->roles;
        foreach ($menu->menu->app->items as $item) {
            if (isset($item->items)) {
                foreach ($item->items as $key => $_item) {
                    /** ToDo opravit role */
//                    if ($_item->role <> $roles['name']) {
//                        $key = null;
//                    }
                }
            }
        }
        return $menu->menu->app->items;
    }
}