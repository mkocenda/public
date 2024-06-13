<?php

namespace App\App\Presenter;

use app\App\Service\CertificatesTypesService;
use App\Model\CertificatesModel;
use App\Model\Translator;
use App\Model\Types;
use Nette\Application\UI\Form;
use Nette\ArrayHash;
use Ublaboo\DataGrid\DataGrid;

class CertificatesPresenter extends BasePresenter
{
	
	public $certificatesModel;
	public $certificatesTypesService;
	public $translator;

	public $id;
	public function __construct(CertificatesModel $certificatesModel, CertificatesTypesService $certificatesTypesService, Translator $translator)
	{
		$this->certificatesModel = $certificatesModel;
		$this->certificatesTypesService = $certificatesTypesService;
		$this->translator = $translator;
	}
	
	public function createComponentCertificatesTypesGrid($name)
	{
		$grid = new DataGrid($this, $name);
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->certificatesModel->getAllCertificatesTypes($organisation_id));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('name',$this->translator->translate('name'));
		$grid->addColumnText('backgroundcolor',$this->translator->translate('color'));
		$grid->addColumnText('status',$this->translator->translate('status'))->setRenderer(function ($datasource) {
			return ($datasource->status == 1) ? '<i class="fa fa-check-circle"></i>' : '';
		})->setTemplateEscaping(FALSE);
		$grid->addColumnText('icon',$this->translator->translate('icon'))->setRenderer(function ($datasource) {
			return (strlen($datasource->icon)) ? '<i class="fa '.$datasource->icon.'"></i>' : '';
		})->setTemplateEscaping(FALSE);
		
		$grid->addColumnCallback('backgroundcolor', function ($column, $item) {
			$td = $column->getElementPrototype('td');
			$td->style[] = 'background-color: ' . $item->backgroundcolor . ';';
		});
		
		$grid->addAction('edit', '', 'edit!', ['id'])
			->setTitle($this->translator->translate('edit'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('edit')
			->setAlign('center');
		
		$grid->setTranslator($this->translator->translator());
		
		return $grid;
	}

	public function createComponentCertificateTypeFormModal()
	{
		$form = new Form();
		
		$form->addHidden('id');
		
		$form->addText('name', $this->translator->translate('name'));
		$form->addText('backgroundcolor', $this->translator->translate('color'))->setHtmlId('color');
		$status = array(Types::DISABLED=>$this->translator->translate('disabled'),
						Types::ENABLED=>$this->translator->translate('enabled'));
		$form->addSelect('status', $this->translator->translate('name'), $status);
		$form->addText('icon', $this->translator->translate('icon'));
		
		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));
		
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		if ($this->id) {
			$certificateType = $this->certificatesModel->getCertificateType($this->id, $organisation_id);
			$form->setDefaults(array('id'=>$this->id,
				'name'=>$certificateType->name,
				'backgroundcolor'=>$certificateType->backgroundcolor,
				'status'=>$certificateType->status,
				'icon'=>$certificateType->icon));
		}
		$form->onSuccess[] = [$this, 'saveType'];
		return $form;
	}
	
	public function saveType(Form $form, ArrayHash $data)
	{
		try {
			$data->organisation_id = $this->user->identity->getData()['organisation_id'];
			$this->certificatesTypesService->saveType($data);
			$this->flashMessage($this->translator->translate('type_saved'), Types::SUCCESS);
		} catch (\Exception $e)
		{
			$this->flashMessage($this->translator->translate('type_not_saved'), Types::DANGER);
		}
	}
	
	public function handleEdit($id)
	{
		$this->id = $id;
		$this->template->modalTemplate = 'edit.latte';
	}
	
	public function handleAdd()
	{
		$this->template->modalTemplate = 'edit.latte';
	}
	
}