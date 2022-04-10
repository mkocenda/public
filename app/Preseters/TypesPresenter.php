<?php

namespace App;

use App\Model\TypesModel;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Translator;
use App\Service\TypesService;

class TypesPresenter extends BasePresenter
{

    public $typesModel;

    public $translator;

    public $typesService;

    public $id;

    public function __construct(Translator $translator, TypesModel $typesModel, TypesService $typesService)
    {
        $this->typesService = $typesService;
        $this->translator = $translator;
        $this->typesModel = $typesModel;
    }

    public function createComponentTypesGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey("id");

        $grid->setDataSource($this->typesModel->loadTypes());

        $grid->setPagination(false);
        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('name','Název')->setSortable();
        $grid->addColumnText('color','Barva')->setRenderer(function ($datasource) {return ' ';});

        $grid->addColumnCallback('color', function($column, $item) {
            $td = $column->getElementPrototype('td');
            $td->style[] = 'background-color: ' . $item->color . ';';
        });

        $grid->addAction('edit','','editType!',['id'])
            ->setIcon('edit')
            ->setTitle('Edit')
            ->setClass('btn btn-success')
            ->setAlign('right');


        $grid->setTranslator($this->translator->translate());
        return $grid;
    }

    public function createComponentTypesFormModal()
    {
        $form = new Form();

        $form->addText('name','Typ akce')
             ->setRequired(true)
             ->setHtmlAttribute('class','form-control');

        $form->addText('color','Barevné označení')
             ->setHtmlAttribute('class','form-control');

        $form->addSubmit('submit','Zapsat')
             ->setHtmlAttribute('class','form-control btn-success');

        $form->addHidden('id');

        if ($this->id){
            $type = $this->typesModel->loadType($this->id);
            $form->setDefaults(['name'=>$type->name,
                'color'=>$type->color,
                'id'=>$this->id]);
            }
        $form->onSuccess[] = [$this, "typeSuccess"];

     return $form;
    }

    public function typeSuccess($form, $data){

        if ($data->id){
            $this->typesService->saveType($data->name, $data->color, $data->id);
        } else{
            $this->typesServicee->saveType($data->name, $data->color);
        }
        $this->redirect('Types:list');
    }

    public function renderList(){
    }

    public function handleEditType($id){
        $this->id = $id;
        $this->getPresenter()->template->modalTemplate = 'typesFormModal.latte';
    }

    public function handleAddType(){
        $this->getPresenter()->template->modalTemplate = 'typesFormModal.latte';
    }


}