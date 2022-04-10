<?php

namespace App\ActionsModule;

use App\ActionsModule\Model\ActionModel;
use Nette\Application\UI\Form;
use Nette\ArrayHash;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;
use App\ActionsModule\Model\ActiontypeModel;
use App\Model\Translator;


class ActionsPresenter extends BasePresenter {

    public $id;

    public $action_id;

    public $user;

    public $actionModel;

    public $actiontypeModel;

    public $datasource;

    public $allowEditAction;

    public $translator;

    public function __construct(ActionModel $actionModel, ActiontypeModel $actiontypeModel, Translator $translator) {
        $this->actionModel = $actionModel;
        $this->actiontypeModel = $actiontypeModel;
        $this->translator = $translator;
    }

    public function actionDefault() {
        $actions = $this->actionModel->loadPlannedActions(true);
        $actionAllData = new ArrayHash();
        /** @var ActionModel $action */
        foreach($actions as $key=>$action){
            $actionData = new ArrayHash();
            $actionData->id = $action->id;
            $actionData->name = $action->name;
            $actionData->motto = $action->motto;
            $actionData->address = $action->address;
            $starttime = new \DateTime($action->starttime);
            $stoptime = new \DateTime($action->stoptime);
            $actionData->stars = $this->actionModel->loadActionEvaluation($action->id)->stars ? strval($this->actionModel->loadActionEvaluation($action->id)->stars) : '0' ;
            $actionData->starttime = $starttime->format('d.m.Y');
            $actionData->stoptime = $stoptime->format('d.m.Y');
            $actionData->photo = $action->photo;
            $actionData->stuff = $this->actionModel->loadActionStuff($action->id);
            $actionAllData[$key] = $actionData;
        }
        $this->template->actions = $actionAllData;

    }

    public function actionDetail($action_id) {
        $action = $this->actionModel->loadAction($action_id);
        $actionData = new ArrayHash();
        /** @var ActionModel $action */
            $actionData->id = $action->id;
            $actionData->name = $action->name;
            $actionData->motto = $action->motto;
            $actionData->address = $action->address;
            $starttime = new \DateTime($action->starttime);
            $stoptime = new \DateTime($action->stoptime);
            $actionData->stars = $this->actionModel->loadActionEvaluation($action->id)->stars ? strval($this->actionModel->loadActionEvaluation($action_id)->stars) : '0' ;
            $actionData->starttime = $starttime->format('d.m.Y');
            $actionData->stoptime = $stoptime->format('d.m.Y');
            $actionData->photo = $action->photo;
            $actionData->stuff = $this->actionModel->loadActionStuff($action_id);
            $actionAllData = $actionData;
        $this->template->action = $actionData;
    }


    public function actionFree() {
        $actions = $this->actionModel->loadPlannedActions(true);
        $actionAllData = new ArrayHash();
        /** @var ActionModel $action */
        foreach($actions as $key=>$action){
            $actionData = new ArrayHash();
            $actionData->id = $action->id;
            $actionData->name = $action->name;
            $actionData->motto = $action->motto;
            $actionData->address = $action->address;
            $starttime = new \DateTime($action->starttime);
            $stoptime = new \DateTime($action->stoptime);
            $actionData->stars = $this->actionModel->loadActionEvaluation($action->id)->stars ? strval($this->actionModel->loadActionEvaluation($action->id)->stars) : '0' ;
            $actionData->starttime = $starttime->format('d.m.Y');
            $actionData->stoptime = $stoptime->format('d.m.Y');
            $actionData->photo = $action->photo;
            $actionData->stuff = $this->actionModel->loadActionStuff($action->id);
            $actionAllData[$key] = $actionData;
        }
        $this->template->actions = $actionAllData;
    }

    public function actionPlanned(){
        $actions = $this->actionModel->loadAvailableActions();
        $actionAllData = new ArrayHash();
        /** @var ActionModel $action */
        foreach($actions as $key=>$action){
            $actionData = new ArrayHash();
            $actionData->id = $action->id;
            $actionData->name = $action->name;
            $actionData->motto = $action->motto;
            $actionData->address = $action->address;
            $starttime = new \DateTime($action->starttime);
            $stoptime = new \DateTime($action->stoptime);
            $actionData->stars = $this->actionModel->loadActionEvaluation($action->id)->stars ? strval($this->actionModel->loadActionEvaluation($action->id)->stars) : '0' ;
            $actionData->starttime = $starttime->format('d.m.Y');
            $actionData->stoptime = $stoptime->format('d.m.Y');
            $actionData->photo = $action->photo;
            $actionData->stuff = $this->actionModel->loadActionStuff($action->id);
            $actionAllData[$key] = $actionData;
        }
        $this->template->actions = $actionAllData;

    }

    public function renderAdminPlanned()
    {
        $this->allowEditAction = 1;
        $this->template->setFile(__DIR__.'/../templates/Actions/adminPlanned.latte');
        $this->datasource = $this->actionModel->loadPlannedActions();
    }


    public function actionAdminInprocess(){
        $this->allowEditAction = 0;
        $this->datasource = $this->actionModel->loadProcessActions();
    }

    public function actionAdminDone(){
        $this->allowEditAction = 0;
        $this->datasource = $this->actionModel->loadDoneActions();
    }

    public function createComponentActionsGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey("id");

        $grid->setDataSource($this->datasource->order("id DESC"));

        $grid->setPagination(true);
        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('name','Název')->setSortable()->setFilterText();
        $grid->addColumnText('motto','Téma')->setSortable()->setFilterText();
        $grid->addColumnDateTime('starttime','Začátek','')->setAlign('left')->setSortable()->setFilterDate();
        $grid->addColumnDateTime('stoptime','Konec','')->setAlign('left')->setSortable()->setFilterDate();
        $grid->addColumnText('limit','Max. počet dětí','')->setAlign('left')->setSortable()->setFilterText();
        $grid->addColumnText('agefrom','Od věku','')->setAlign('left')->setSortable()->setFilterText();

        $grid->addColumnText('type_id','Typ akce','type_id')
            ->setRenderer(function ($datasource) {
                $type = $this->actiontypeModel->loadType($datasource->type_id);
                return $type->name;
            })
            ->setSortable()
            ->setFilterSelect($this->actiontypeModel->loadTypes());


    if ($this->allowEditAction) {
        $grid->addAction('editAction', '', 'editAction', ['id'])
            ->setIcon('edit')
            ->setTitle('Edit')
            ->setClass('btn btn-success')
            ->setAlign('right');
        }

        if ($this->allowEditAction) {
            $grid->addAction('editStuff', '', 'editStuff', ['id'])
                ->setIcon('users')
                ->setTitle('Personál')
                ->setClass('btn btn-success')
                ->setAlign('right');
        }

        $grid->setTranslator($this->translator->translate());
        return $grid;
    }


    protected  function createComponentEditActionForm()
    {
        $form = new Form();
        $form->addHidden('id','');
        $form->addText('name','Název')->setRequired(true);
        $form->addText('motto','Téma')->setRequired(true);
        $form->addTextArea('description','Popis','80','7');
        $form->addText('starttime','Začátek')->setType('date')->setRequired(true);
        $form->addText('stoptime','Konec')->setType('date')->setRequired(true);
        $form->addInteger('limit','Počet dětí')->setRequired(true);
        $form->addInteger('agefrom','Minimální věk')->setRequired(true);
        $form->addInteger('ageto','Maximální věk')->setRequired(true);
        $form->addUpload('photo','Obrázek')->setRequired(true);

        $form->addSubmit("submit", "Uložit")->setHtmlAttribute('class', 'btn btn-success pull-right');

        $action = $this->actionModel->loadAction($this->id);

        $starttime = new \DateTime($action->starttime);
        $stoptime = new \DateTime($action->stoptime);
        $form->setDefaults(["name"=>$action->name,
                            "motto"=>$action->motto,
                            "description"=>$action->description,
                            "starttime"=>$starttime->format('Y-m-d'),
                            "stoptime"=>$stoptime->format('Y-m-d'),
                            "limit"=>$action->limit,
                            "agefrom"=>$action->agefrom,
                            "ageto"=>$action->ageto,
            ]);
        $form->onSuccess[] = [$this, "editAction"];

        return $form;
    }


    public function createComponentStuffGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey("id");

        $grid->setDataSource($this->actionModel->loadActionStuff($this->action_id));
        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('name','Jméno')->setSortable();
        $grid->addColumnText('surname','Příjmení')->setSortable();
        $grid->addColumnDateTime('alias','Přezdívka','')->setAlign('left')->setSortable();
        $grid->addColumnText('stuff_name','Funkce','stuff_name')->setSortable();;

        $grid->addAction('editActionStuff','','editActionStuff',['id'])
            ->setIcon('edit')
            ->setTitle('Upravit')
            ->setClass('btn btn-success')
            ->setAlign('right');

        $grid->addAction('removeActionStuff','','removeActionStuff',['id'])
            ->setIcon('trash')
            ->setTitle('Odstranit')
            ->setClass('btn btn-danger')
            ->setAlign('right');


        $grid->setTranslator($this->translator->translate());

        return $grid;
    }

    public function actionEditAction($id)
    {
        $this->id = $id;
        $this->template->setFile(__DIR__.'/../templates/Actions/editAction.latte');
    }

    public function actionEditStuff($id)
    {
        $this->action_id = $id;
        $this->template->setFile(__DIR__.'/../templates/Actions/editStuff.latte');
    }

}
