<?php

namespace App\App\Presenter;

use App\Model\Types;
use App\Model\WarehouseModel;
use App\App\Service\WarehouseService;
use App\Model\Translator;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\DataGrid;
use App\Model\StuffModel;

class WarehousePresenter extends BasePresenter
{

    public $warehouseModel;
    public $warehouseService;
    public $stuffModel;
    public $translator;
    public $organisation_id;
    public $id;

    public function __construct(WarehouseModel $warehouseModel, WarehouseService $warehouseService, StuffModel $stuffModel, Translator $translator)
    {
        $this->warehouseModel = $warehouseModel;
        $this->warehouseService = $warehouseService;
        $this->stuffModel = $stuffModel;
        $this->translator = $translator;
    }

    public function createComponentWarehousesGrid($name){

        $grid = new DataGrid($this, $name);

        $this->organisation_id = $this->user->identity->getData()['organisation_id'];
        $grid->setPrimaryKey('id');
        $grid->setRefreshUrl(false);
        $grid->setDataSource($this->warehouseModel->getWarehousesByOrganisationId($this->organisation_id));
        $grid->addColumnText('name', $this->translator->translate('name'));
        $grid->addColumnText('location', $this->translator->translate('location'));
        $grid->addColumnText('storeuser_id', $this->translator->translate('storeuser'))
	        ->setRenderer(function ($datasource){
           $stuff = $this->stuffModel->getStuffByID($datasource->storeuser_id);
           return $stuff->surname.' '.$stuff->name;
        });
        $grid->addAction('edit', '', 'edit!', ['id'])
            ->setTitle($this->translator->translate('edit'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_OK)
            ->setIcon('edit')
            ->setAlign('center');

        $grid->setTranslator($this->translator->translator());
        return $grid;
    }

    public function createComponentWarehouseFormModal()
    {
        $organisation_id = $this->user->identity->getData()['organisation_id'];

        $form = new Form();

        $form->addHidden('id');
        $form->addText('name', $this->translator->translate('name'));
        $form->addText('location', $this->translator->translate('location'));
        $storeusers = $this->stuffModel->getStuffsByOrganisationID($organisation_id);
        $users = array();
        foreach ($storeusers as $storeuser)
        {
            $users[$storeuser->id] = $storeuser->surname.' '.$storeuser->name;
        }
        $form->addSelect('storeuser_id', $this->translator->translate('storeuser'), $users);

        $form->addSubmit('submit', $this->translator->translate('btn_write'));
        $form->addButton('cancel', $this->translator->translate('btn_cancel'));

        if ($this->id){
            $warehouse = $this->warehouseModel->getWarehouseById($this->id, $organisation_id);
            $form->setDefaults(array('id'=>$this->id,
                'name'=>$warehouse->name,
                'location'=>$warehouse->location,
                'storeuser_id'=>$warehouse->storeuser_id));
        }

        $form->onSuccess[] = [$this, 'save'];
        return $form;
    }

    public function save(Form $form, ArrayHash $data)
    {
        try {
            unset($data->cancel);
            $data->organisation_id = $this->user->identity->getData()['organisation_id'];
            $this->warehouseService->saveWarehouse($data);
            $this->flashMessage($this->translator->translate('warehouse_saved'), Types::SUCCESS);
        } catch (\Exception $e){
            $this->flashMessage($this->translator->translate('warehouse_not_saved'), Types::DANGER);
        }
    }

    public function handleEdit($id){
        $this->id = $id;
        $this->template->modalTemplate = 'editWarehouse.latte';
    }

	public function handleAddWarehouse()
	{
        $this->template->modalTemplate = 'editWarehouse.latte';
	}
}