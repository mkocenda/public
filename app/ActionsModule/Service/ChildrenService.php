<?php
namespace App\ActionsModule\Service;
use App\ActionsModule\Model\ChildrenModel;
use Nette\Utils\DateTime;
use Exception;

class ChildrenService
{
    public $childrenModel;

    public function __construct(ChildrenModel $childrenModel) {
        $this->childrenModel = $childrenModel;
    }

    /**
     * @param $parent_id
     * @param $name
     * @param $surname
     * @param $birthday
     * @param $insurance_id
     * @param $note
     * @throws Exception
     */
    public function addChild($parent_id, $name, $surname, $birthday, $insurance_id, $note)
    {
        /** @var $birthday DateTime */
        $today = new DateTime();
        if($today->diff(new DateTime($birthday))->days < (5 * 365))
        {
            throw new Exception('Věk dítěte je menší než 5 let');
        }
        try
        {
            $this->childrenModel->saveChild($parent_id,  null, $name, $surname, $birthday, $insurance_id, $note);
        }
        catch(\Exception $e)
        {

        }
    }

    /**
     * @param $parent_id
     * @param $child_id
     * @param $name
     * @param $surname
     * @param $birthday
     * @param $insurance_id
     * @param $note
     * @throws Exception
     */
    public function editChild($parent_id, $child_id, $name, $surname, $birthday, $insurance_id, $note)
    {
        /** @var $birthday DateTime */
        $today = new DateTime();
        if($today->diff(new DateTime($birthday))->days < (5 * 365))
        {
            throw new Exception('Věk dítěte je menší než 5 let');
        }
        try
        {
            $this->childrenModel->saveChild($parent_id, $child_id, $name, $surname, $birthday, $insurance_id, $note);
        }
        catch(\Exception $e)
        {

        }
    }


}