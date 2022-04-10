<?php

namespace App\ActionsModule;

use App\ActionsModule\Model\ChildrenModel;
use App\ActionsModule\Service\ChildrenService;
use Nette;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;
use Nette\Application\UI\Form;
use App\ActionsModule\Model\InsuranceModel;

class ChildrenPresenter extends BasePresenter
{
    public $database;

    public $childrenModel;

    public $childrenSservice;

    public $insuranceModel;

    public $childId;

    public function __construct(Nette\Database\Context $database, ChildrenModel $childrenModel, ChildrenService $childrenSservice, InsuranceModel $insuranceModel)
    {
        $this->database = $database;
        $this->childrenModel = $childrenModel;
        $this->childrenSservice = $childrenSservice;
        $this->insuranceModel = $insuranceModel;
    }

    public function renderList()
    {
    }

    public function renderAddChild()
    {

    }

    public function renderEditChild()
    {

    }

    public function actionAddChild()
    {

    }



    protected  function createComponentAddChildForm()
    {
        $form = new Form();
        $form->addHidden('parent_id','');
        $form->addText('name','Jméno')->setRequired(true);
        $form->addText('surname','Příjmení')->setRequired(true);
        $form->addText('birthday','Datum narození')->setType('date')->setRequired(true);
        $insuranceList = $this->insuranceModel->loadInsuranceList();
        $insuranceData[0]="Zdravotní pojištovna";

        foreach($insuranceList as $key=>$insurance)
        {
            $insuranceData[$insurance->id] = $insurance->code.' - '.$insurance->name;
        }

        $form->addSelect('insurance_id','Zdravotní pojištovna', $insuranceData);
        $form->addTextArea('note','Poznámka','80','7');

        $form->addSubmit("submit", "Uložit")->setHtmlAttribute('class', 'btn btn-success pull-right');

        $form->setDefaults(['parent_id'=>1]);
        $form->onSuccess[] = [$this, "addChild"];

        return $form;
    }

    protected  function createComponentEditChildForm()
    {

        $form = new Form();
        $form->addHidden('parent_id','');
        $form->addHidden('child_id','');
        $form->addText('name','Jméno')->setRequired(true);
        $form->addText('surname','Příjmení')->setRequired(true);
        $form->addText('birthday','Datum narození')->setType('date')->setRequired(true);
        $insuranceList = $this->insuranceModel->loadInsuranceList();

        foreach($insuranceList as $key=>$insurance)
        {
            $insuranceData[$insurance->id] = $insurance->code.' - '.$insurance->name;
        }

        $form->addSelect('insurance_id','Zdravotní pojištovna', $insuranceData);
        $form->addTextArea('note','Poznámka','80','7');

        $form->addSubmit("submit", "Uložit")->setHtmlAttribute('class', 'btn btn-success pull-right');

        if ($this->childId){
            $childData = $this->childrenModel->loadChild($this->childId, 1);
            $form->setDefaults(['name'=>$childData->name,
                                'surname'=>$childData->surname,
                                'birthday'=>$childData->birthday->format('Y-m-d'),
                                'insurance_id'=>$childData->insurance_id,
                                'note'=>$childData->note,
                                'parent_id'=>$childData->parents_id,
                                'child_id'=>$childData->id]);
        }
        $form->onSuccess[] = [$this, "editChild"];

        return $form;
    }


    public function addChild($form, $data):void
    {
        $this->childrenSservice->addChild($data->parent_id, $data->name, $data->surname, $data->birthday, $data->insurance_id, $data->note);
        $this->getPresenter()->flashMessage('Uloženo', 'success');
        $this->getPresenter()->redirect('Children:list');
    }

    public function editChild($form, $data):void
    {
        $this->childrenSservice->editChild($data->parent_id, $data->child_id, $data->name, $data->surname, $data->birthday, $data->insurance_id, $data->note);
        $this->getPresenter()->flashMessage('Uloženo', 'success');
        $this->getPresenter()->redirect('Children:list');
    }


    public function createComponentChildrenGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey("id");

        $dataSource = $this->childrenModel->loadChildren(1);

        $grid->setDataSource($dataSource->order("id DESC"));

        $grid->setPagination(false);
        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('name','Jméno')->setSortable();
        $grid->addColumnText('surname','Příjmení')->setSortable();
        $grid->addColumnDateTime('birthday','Datum narození','')->setAlign('left')->setSortable();
        $grid->addColumnText('insurance','Zdrav. pojištovna','insurance_id')
             ->setRenderer(function ($datasource) {
             $insurance = $this->insuranceModel->loadInsurance($datasource->insurance_id);
             return $insurance->code.' - '.$insurance->name;
        })
            ->setSortable();;
        $grid->addColumnText('note','Poznámka')->setSortable();

        $grid->addAction('edit', '', 'edit!', ['id'])
            ->setIcon('edit')
            ->setTitle('Edit')
            ->setClass('btn btn-success')
            ->setAlign('right');

        $translator = new SimpleTranslator([
            'ublaboo_datagrid.no_item_found_reset' => '',
            'ublaboo_datagrid.no_item_found' => 'Nic k zobrazení',
            'ublaboo_datagrid.here' => '',
            'ublaboo_datagrid.items' => 'Záznamů',
            'ublaboo_datagrid.all' => 'Vše',
            'ublaboo_datagrid.from' => 'z',
            'ublaboo_datagrid.reset_filter' => 'Zrušit filtr',
            'ublaboo_datagrid.group_actions' => '',
            'ublaboo_datagrid.show_all_columns' => 'Zobrazit sloupce',
            'ublaboo_datagrid.hide_column' => 'Skrýt sloupce',
            'ublaboo_datagrid.action' => '',
            'ublaboo_datagrid.previous' => 'Předchozí',
            'ublaboo_datagrid.next' => 'Další',
            'ublaboo_datagrid.choose' => '',
            'ublaboo_datagrid.execute' => '',
            'ublaboo_datagrid.per_page_submit' => 'Nastavit'
        ]);

        $grid->setTranslator($translator);
        return $grid;
    }

    public function handleEdit($id)
    {
        $this->childId = $id;
        $this->getPresenter()->template->setFile(__DIR__.'/../templates/children/editChild.latte');
        $this->getPresenter()->renderEditChild();
    }
}