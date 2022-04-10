<?php

namespace App;


use App\Model\CertificatesTypesModel;
use App\Model\Translator;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;
use App\Service\CertificatesTypesService;

class CertificatesTypesPresenter extends BasePresenter
{
    public $certificatesTypesModel;

    public $certificatesTypesService;

    public $translator;

    public $id;

    public function __construct(CertificatesTypesModel $certificatesTypesModel,CertificatesTypesService $certificatesTypesService, Translator $translator)
    {
        $this->certificatesTypesModel = $certificatesTypesModel;
        $this->certificatesTypesService = $certificatesTypesService;
        $this->translator = $translator;
    }


    public function createComponentCertificatesTypesGrid($name){
        $grid = new DataGrid($this, $name);

        $grid->setPrimaryKey("id");

        $grid->setDataSource($this->certificatesTypesModel->loadAllCertificatesType());

        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('name','Název')
            ->setSortable(true);

        $grid->addColumnText('backgroundcolor','Barva')->setRenderer(function ($datasource) {return ' ';});

        $grid->addColumnCallback('backgroundcolor', function($column, $item) {
            $td = $column->getElementPrototype('td');
            $td->style[] = 'background-color: ' . $item->backgroundcolor . ';';
        });

        $grid->addAction('edit','','editType!', ['id'])
            ->setTitle('Edit')
            ->setClass('no-border')
            ->setIcon('edit')
            ->setAlign('center');

        $grid->setTranslator($this->translator->translate());
        return $grid;
    }

    public function createComponentCertificatesTypesFormModal()
    {
        $form = new Form();
        $form->addText('name','Název')
            ->setRequired(true)
            ->setHtmlAttribute('class','form-control');

        $form->addText('backgroundcolor','Barva')
             ->setHtmlAttribute('class','form-control');
        $form->addCheckbox('status','Platné')
             ->setHtmlAttribute('class','form-control');


        $form->addHidden('id');
        $form->addSubmit('submit','Zapsat')
            ->setHtmlAttribute('class','form-control btn-success');

        if ($this->id){
            $certificateTypes = $this->certificatesTypesModel->loadCertificatesTypeById($this->id);
            $form->setDefaults(['id'=>$this->id,
                                'name'=>$certificateTypes->name,
                                'backgroundcolor'=>$certificateTypes->backgroundcolor,
                                'status'=>$certificateTypes->status]);
        }

        $form->onSuccess[] = [$this, "certifitcateTypeSuccess"];

        return $form;
    }

    public function certifitcateTypeSuccess($form, $data){
        if ($data->id){
            $this->certificatesTypesService->editCertificateType($data->name, $data->backgroundcolor, 1, $data->id);
        } else{
            $this->certificatesTypesService->addCertificateType($data->name, $data->backgroundcolor, 1);
        }


    }


    public function handleEditType($id){
        $this->id = $id;
        $this->getPresenter()->template->modalTemplate = 'certificatesTypeForm.latte';

    }

    public function handleAddCertificatesType(){
        $this->getPresenter()->template->modalTemplate = 'certificatesTypeForm.latte';
    }
}