<?php

namespace App\App\Presenter;

use App\Model\PartsModel;
use App\Model\WarehouseModel;
use App\Model\ReservationsModel;
use App\App\Service\ReservationsService;
use App\Model\ActionModel;
use App\Model\Translator;
use App\Model\Types;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\DataGrid;
use App\App\Service\PartsService;
use App\Model\StuffModel;

class PartsPresenter extends BasePresenter
{

    public $partsModel;
    public $partsService;
    public $warehouseModel;
	public $reservationsModel;
	public $reservationsService;
	public $actionModel;
	public $stuffModel;
    public $translator;
    public $organisation_id;
    public $id;

    public function __construct(PartsModel $partsModel,
                                WarehouseModel $warehouseModel,
                                PartsService $partsService,
                                ReservationsModel $reservationsModel,
								ReservationsService $reservationsService,
								ActionModel $actionModel,
								StuffModel $stuffModel,
                                Translator $translator)
    {
        $this->partsModel = $partsModel;
        $this->partsService = $partsService;
        $this->warehouseModel = $warehouseModel;
		$this->reservationsModel = $reservationsModel;
		$this->reservationsService = $reservationsService;
		$this->actionModel = $actionModel;
		$this->stuffModel = $stuffModel;
        $this->translator = $translator;
    }

    public function createComponentPartsGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $this->organisation_id = $this->user->identity->getData()['organisation_id'];
        $grid->setPrimaryKey('id');
        $grid->setRefreshUrl(false);
        $grid->setDataSource($this->partsModel->getAllPartsByOrganisationId($this->organisation_id));
        $grid->addColumnText('part_no', $this->translator->translate('part_no'));
        $grid->addColumnText('description', $this->translator->translate('description'));
        $grid->addColumnText('warehouse', $this->translator->translate('warehouse'))
            ->setRenderer(function ($datasource) {
                $warehouse = $this->warehouseModel->getWarehouseById($datasource->warehouse_id, $this->organisation_id);
                return $warehouse->name;
            });
        $grid->addColumnNumber('price', $this->translator->translate('price'))
	        ->setFormat(2,'.',' ');
        $grid->addColumnNumber('qty', $this->translator->translate('qty_total'))
	        ->setFormat(0,'.',' ');
        $grid->addColumnNumber('qty_available', $this->translator->translate('qty_available'))->setRenderer(function ($datasource){
			$used = $this->partsModel->getUsedPartQty($datasource->id, $datasource->organisation_id);
			return number_format($datasource->qty - $used->used, 0,'.',' ');
        });

        $grid->addAction('edit', '', 'edit!', ['id'])
            ->setTitle($this->translator->translate('edit'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_OK)
            ->setIcon('edit')
            ->setAlign('center');
	    
	    $grid->addAction('delete', '', 'delete!', ['id'])
		    ->setTitle($this->translator->translate('delete'))
		    ->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
		    ->setIcon('trash')
		    ->setAlign('center')
		    ->setRenderCondition(function ($datasource){
				return !$this->partsModel->isUsedPart($datasource->id, $datasource->organisation_id);
		    });

        $grid->setTranslator($this->translator->translator());
        return $grid;
    }

    public function createComponentPartFormModal()
    {
        $form = new Form();
        $organisation_id = $this->user->identity->getData()['organisation_id'];

        $form->addHidden('id');
        $form->addText('part_no', $this->translator->translate('part_no'));
        $form->addText('description', $this->translator->translate('description'));
        $form->addText('qty', $this->translator->translate('qty_total'));
        $form->addText('price', $this->translator->translate('price'));
        $form->addUpload('image_id', $this->translator->translate('image'));
        $warehouses = $this->warehouseModel->getWarehousesByOrganisationId($organisation_id);
        $_warehouses = array();
        foreach ($warehouses as $warehouse) {
            $_warehouses[$warehouse->id] = $warehouse->name;
        }
        $form->addSelect('warehouse_id', $this->translator->translate('warehouse'), $_warehouses);

        $form->addSubmit('submit', $this->translator->translate('btn_write'));
        $form->addButton('cancel', $this->translator->translate('btn_cancel'));

        if ($this->id) {
            $part = $this->partsModel->getPartById($this->id, $organisation_id);
            $form->setDefaults(array('id' => $this->id,
                'part_no' => $part->part_no,
                'description' => $part->description,
                'qty' => $part->qty,
                'price' => $part->price,
                'image_id' => $part->image_id,
                'warehouse_id' => $part->warehouse_id));
        }

        $form->onSuccess[] = [$this, 'save'];
        return $form;
    }

    public function save(Form $form, ArrayHash $data)
    {
        try {
            $data->organisation_id = $this->user->identity->getData()['organisation_id'];
            $this->partsService->savePart($data);
            $this->flashMessage($this->translator->translate('part_saved'), Types::SUCCESS);
        } catch (\Exception $e) {
            $this->flashMessage($this->translator->translate('part_not_saved'), Types::DANGER);
        }
    }
	
	public function createComponentReservationsGrid($name)
	{
		$grid = new DataGrid($this, $name);
        $this->organisation_id = $this->user->identity->getData()['organisation_id'];
        $grid->setPrimaryKey('id');
        $grid->setRefreshUrl(false);
        $grid->setDataSource($this->reservationsModel->getReservationsByOrganisationId($this->organisation_id));
		$grid->addColumnText('action', $this->translator->translate('actions'))->setRenderer(function ($datasource){
			$action = $this->actionModel->getAction($datasource->action_id);
			return $action->name;
		});
		$grid->addColumnText('part', $this->translator->translate('part_no'))->setRenderer(function ($datasource){
			$part = $this->partsModel->getPartById($datasource->part_id, $this->user->identity->getData()['organisation_id']);
			return $part->part_no;
		});
		$grid->addColumnNumber('amount', $this->translator->translate('amount'));
		$grid->addColumnText('stuff_id', $this->translator->translate('stuff'))->setRenderer(function ($datasource){
			if ($datasource->stuff_id)
			{
				$stuff = $this->stuffModel->getStuffByID($datasource->stuff_id);
				return $stuff->surname . ' ' . $stuff->name;
			} else {
				return '';
			}
		});
		$grid->addColumnDateTime('reserved_date', $this->translator->translate('reserved_date'))->setFormat('d.m.Y');

		$grid->setTranslator($this->translator->translator());
		return $grid;
	}
	
	public function handleDelete($id)
	{
		try {
			$this->partsService->deletePart($id, $this->user->identity->getData()['organisation_id']);
			$this->flashMessage($this->translator->translate('part_deleted'), Types::SUCCESS);
		} catch (\Exception $e)
		{
			$this->flashMessage($this->translator->translate('part_not_deleted'), Types::DANGER);
		}
	}
		
    public function handleEdit($id)
    {
        $this->id = $id;
        $this->template->modalTemplate = 'editPart.latte';
    }

    public function handleAddPart()
    {
        $this->template->modalTemplate = 'editPart.latte';
    }
}