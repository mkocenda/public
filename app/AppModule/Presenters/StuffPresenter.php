<?php

namespace App\App\Presenter;

use App\Model\StuffModel;
use App\Model\Types;
use Nette\Application\UI\Form;
use Nette\DateTime;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Translator;
use App\Model\OrganisationModel;
use App\Model\UsersModel;
use App\Admin\Service\StuffService;
use App\Model\CertificatesModel;
use App\App\Service\CertificateService;
use App\App\Service\StuffTypeService;
class StuffPresenter extends BasePresenter
{
	
	public $stuffModel;
	public $organisationModel;
	public $usersModel;
	public $stuffService;
	public $translator;
	public $user_id;
	public $stuff_id;
	public $certificate_id;
	public $certificatesModel;
	public $certificateService;
	public $stuffType_id;
	public $stuffTypeService;
	
	public function __construct(StuffModel        $stuffModel,
	                            OrganisationModel $organisationModel,
	                            UsersModel        $usersModel,
	                            StuffService      $stuffService,
								CertificatesModel $certificatesModel,
								CertificateService  $certificateService,
	                            Translator        $translator,
								StuffTypeService    $stuffTypeService)
	{
		$this->stuffModel = $stuffModel;
		$this->organisationModel = $organisationModel;
		$this->usersModel = $usersModel;
		$this->stuffTypeService = $stuffTypeService;
		$this->certificatesModel = $certificatesModel;
		$this->certificateService = $certificateService;
		$this->stuffService = $stuffService;
		$this->translator = $translator;
	}
	
	public function createComponentStuffsGrid($name)
	{
		$grid = new DataGrid($this, $name);
		
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->stuffModel->getStuffsByOrganisationID($organisation_id));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('name', $this->translator->translate('name'));
		$grid->addColumnText('surname', $this->translator->translate('surname'));
		$grid->addColumnText('alias', $this->translator->translate('alias'));
		$grid->addColumnText('email', $this->translator->translate('email'));
		$grid->addColumnDateTime('birthday', $this->translator->translate('birthday'))->setRenderer(function ($datasource) {
			if ($datasource->birthday <> '-0001-11-30 00:00:00') {
				return $datasource->birthday;
			} else {
				return '';
			}
		})->setFormat('j.n.Y');
		$grid->addColumnDateTime('work_from', $this->translator->translate('work_from'))->setRenderer(function ($datasource) {
			if ($datasource->work_from <> '-0001-11-30 00:00:00') {
				return $datasource->work_from;
			} else {
				return '';
			}
		})->setFormat('d.m.Y');
		$grid->addColumnDateTime('work_to', $this->translator->translate('work_to'))->setRenderer(function ($datasource) {
			if ($datasource->work_to <> '-0001-11-30 00:00:00') {
				return $datasource->work_to;
			} else {
				return '';
			}
		})->setFormat('d.m.Y');
		$grid->addColumnText('active', $this->translator->translate('active'))->setRenderer(function ($datasource) {
			return ($datasource->active == 1) ? '<i class="fa fa-check-circle"></i>' : '';
		})->setTemplateEscaping(FALSE);
		
		$grid->addAction('edit', '', 'edit!', ['id'])
			->setTitle($this->translator->translate('edit'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('edit')
			->setAlign('center');
		
		$grid->addAction('certificates', '', 'certificates!', ['id'])
			->setTitle($this->translator->translate('certificates'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('graduation-cap')
			->setAlign('center');
		
		$grid->setTranslator($this->translator->translator());
		return $grid;
	}
	
	public function createComponentStuffFormModal()
	{
		$form = new Form();
		$form->addHidden('id');
		$form->addHidden('stuff_id');
		$form->addText('name', $this->translator->translate('firstname'));
		$form->addText('surname', $this->translator->translate('surname'));
		$form->addText('alias', $this->translator->translate('alias'));
		$form->addEmail('email', $this->translator->translate('email'));
		$form->addText('birthday', $this->translator->translate('birthday'))->setType('date');
		$form->addText('phone', $this->translator->translate('phone'));
		$form->addText('work_from', $this->translator->translate('work_from'))->setType('date');
		$form->addText('work_to', $this->translator->translate('work_to'))->setType('date');
		$organisationa = array(0 => '') + $this->organisationModel->selectOrganisations();
		$form->addSelect('organisation_id', $this->translator->translate('organisation_id'), $organisationa);
		$users = array(0 => '') + $this->usersModel->getUsersSelect();
		$form->addSelect('user_id', $this->translator->translate('user_id'), $users);
		$form->addCheckbox('active', $this->translator->translate('active'));
		$form->addUpload('photo', $this->translator->translate('photo'));
		
		if ($this->stuff_id > 0) {
			$stuff = $this->stuffModel->getStuffByID($this->stuff_id);
			$birthday = '';
			if ($stuff->birthday) {
				$birthday = new DateTime($stuff->birthday);
				$birthday = $birthday->format('Y-m-d');
			}
			$work_from = '';
			if ($stuff->work_from) {
				$work_from = new DateTime($stuff->work_from);
				$work_from = $work_from->format('Y-m-d');
			}
			$work_to = '';
			if ($stuff->work_to) {
				$work_to = new DateTime($stuff->work_to);
				$work_to = $work_to->format('Y-m-d');
			}
			$form->setDefaults(array('id' => $this->stuff_id,
				'name' => $stuff->name,
				'surname' => $stuff->surname,
				'alias' => $stuff->alias,
				'email' => $stuff->email,
				'birthday' => $birthday,
				'work_from' => $work_from,
				'work_to' => $work_to,
				'phone' => $stuff->phone,
				'active' => $stuff->active,
				'organisation_id' => $stuff->organisation_id,
				'user_id' => $stuff->user_id));
		}
		
		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));
		
		$form->onSuccess[] = [$this, 'saveStuff'];
		return $form;
	}
	
	public function saveStuff(Form $form, ArrayHash $data)
	{
		try {
			$this->stuffService->saveStuff($data);
			$this->flashMessage($this->translator->translate('stuff_saved'), Types::SUCCESS);
			if ($this->isAjax()) {
				$this->redrawControl('stuffsGrid');
			}
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('stuff_not_saved'), Types::DANGER);
		}
		$this->presenter->redirect(':App:Stuff:list');
	}
	
	public function createComponentStuffCertificatesGrid($name)
	{
		$grid = new DataGrid($this, $name);
		
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$grid->setPrimaryKey("certificate_id");
		$grid->setDataSource($this->stuffModel->getStuffCertificates($this->stuff_id, $organisation_id));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('name', $this->translator->translate('name'));
		$grid->addColumnDateTime('validfrom', $this->translator->translate('validfrom'))
			->setRenderer(function ($datasource) {
				if ($datasource->validfrom <> '-0001-11-30 00:00:00') {
					return $datasource->validfrom;
				} else {
					return '';
				}
			})->setFormat('d.m.Y');
		
		$grid->addColumnDateTime('validto', $this->translator->translate('validto'))
			->setRenderer(function ($datasource) {
				if ($datasource->validto <> '-0001-11-30 00:00:00') {
					return $datasource->validto;
				} else {
					return '';
				}
			})->setFormat('d.m.Y');
		
		$grid->addColumnCallback('validto', function ($column, $item) {
			$td = $column->getElementPrototype('td');
			if (!empty($item->validto)) {
				$validTo = new DateTime($item->validto);
				$now = new DateTime();
				if (($now > $validTo) && ($validTo->format('Y-m-d H:i') <> '-0001-11-30 00:00')) {
					$td->style[] = 'background-color: #FF0000;';
				}
			}
		});
		
		
		$grid->addAction('editCertificate', '', 'editCertificate!', ['certificate_id'])
			->setTitle($this->translator->translate('edit'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('edit')
			->setAlign('center')
			->addParameters(array('stuff_id'=> $this->user_id));
		
		$grid->setTranslator($this->translator->translator());
		
		return $grid;
	}
	
	public function createComponentCertificateFormModal(){
		$form = new Form();
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$form->addHidden('id');
		$form->addHidden('stuff_id');
		$certtypes = $this->certificatesModel->getAllCertificatesTypes($organisation_id);
		foreach ($certtypes as $certtype)
		{
			$types[$certtype->id] = $certtype->name;
		}
		$form->addSelect('certtype', $this->translator->translate('name'),$types);
		$form->addText('validfrom', $this->translator->translate('valid_from'))->setType('date');
		$form->addText('validto', $this->translator->translate('validto'))->setType('date');
		$form->addUpload('certfile', $this->translator->translate('certfile'));
		
		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));
		
		if ($this->certificate_id){
			$certificate = $this->certificatesModel->getCertificateByID($this->certificate_id);
			$validfrom = new DateTime($certificate->validfrom);
			$validto = new DateTime($certificate->validto);
			$form->setDefaults(array('id'=>$this->certificate_id,
								 	 'stuff_id'=>$certificate->stuff_id,
									 'name'=>$certificate->name,
									 'validfrom'=>$validfrom->format('Y-m-d'),
								 	 'validto'=>$validto->format('Y-m-d')));
		} else {
			$form->setDefaults(array('stuff_id'=>$this->stuff_id));
		}
		$form->onSuccess[] = [$this,'saveCertificate'];
		return $form;
	}
	
	public function saveCertificate(Form $form, ArrayHash $data){
		try {
			$stuff_id = $data->stuff_id;
			$this->certificateService->saveCertificate($data);
			$this->flashMessage($this->translator->translate('certificate_saved'), Types::SUCCESS);
		} catch (\Exception $e)
		{
			$this->flashMessage($this->translator->translate('certificate_not_saved'), Types::DANGER);
		}
		$this->redirect(':App:Stuff:list',array('id'=>$stuff_id, 'do'=>'certificates'));
	}
	
	public function createComponentStuffTypesGrid($name)
	{
		$grid = new DataGrid($this, $name);
		
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->stuffModel->getStuffsTypes($organisation_id));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('name', $this->translator->translate('name'));
		$grid->addColumnNumber('order', $this->translator->translate('level'));
		
		$grid->addAction('editType', '', 'editType!', ['id'])
			->setTitle($this->translator->translate('edit'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('edit')
			->setAlign('center')
			->setRenderCondition(function ($datasource)
			{
				if (empty($datasource->organisation_id)) {return false; } else {return true; }
			});
		
		$grid->setTranslator($this->translator->translator());
		return $grid;
		
	}
	
	public function createComponentTypeFormModal()
	{
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$form = new Form();

		$form->addHidden('id');
		$form->addHidden('organisation_id');
		$form->addText('name',$this->translator->translate('name'));
		$form->addInteger('order',$this->translator->translate('level'));
		
		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));
		
		if ($this->stuffType_id)
		{
			$type = $this->stuffModel->getStuffType($this->stuffType_id, $organisation_id);
			$form->setDefaults(array('id'=>$this->stuffType_id,
				'name'=>$type->name,
				'organisation_id'=>$type->organisation_id,
				'order'=>$type->order));
		} else{
			$form->setDefaults(array('organisation_id'=>$organisation_id));
		}
		
		$form->onSuccess[] = [$this, 'saveType'];
		
		return $form;
	}
	
	public function saveType(Form $form, ArrayHash $data)
	{
		try {
			$this->stuffTypeService->saveType($data);
			$this->flashMessage($this->translator->translate('type_succesfully_saved'), Types::SUCCESS);
		} catch (\Exception $e)
		{
			$this->flashMessage($this->translator->translate('type_unsuccesfully_saved'), Types::DANGER);
		}
	}
	
	public function handleEdit($id)
	{
		$this->stuff = $id;
		$this->template->modalTemplate = 'edit.latte';
	}
	
	public function handleAdd()
	{
		$this->template->modalTemplate = 'edit.latte';
	}
	
	public function handleCertificates($id)
	{
		$this->stuff_id = $id;
		$this->template->stuff_id = $id;
		$this->template->modalTemplate = 'certificates.latte';
	}
	
	public function handleAddCertificate($stuff_id)
	{
		$this->stuff_id = $stuff_id;
		$this->template->stuff_id = $stuff_id;
		$this->template->certificate_id = 0;
		$this->template->modalTemplate = 'certificate.latte';
	}
	
	public function handleEditCertificate($certificate_id, $stuff_id)
	{
		$this->stuff_id = $stuff_id;
		$this->certificate_id = $certificate_id;
		$this->template->certificate_id = $certificate_id;
		$this->template->stuff_id = $stuff_id;
		$this->template->modalTemplate = 'certificate.latte';
	}

	public function handleEditType($id)
	{
		$this->stuffType_id = $id;
		$this->template->modalTemplate = 'editType.latte';
	}

	public function handleAddType()
	{
		$this->template->modalTemplate = 'editType.latte';
	}
}