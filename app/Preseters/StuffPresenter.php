<?php

namespace App;

use App\ActionsModule\Model\StuffModel;
use App\Model\UserModel;
use App\ActionsModule\Service\StuffService;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\Html;
use App\Model\Translator;

class StuffPresenter extends BasePresenter
{

    public $id;
    public $stuffModel;
    public $userModel;
    public $stuffService;
    public $translator;

    public function __construct(StuffModel $stuffModel, UserModel $userModel, StuffService $stuffService, Translator $translator){
        $this->stuffModel = $stuffModel;
        $this->userModel = $userModel;
        $this->stuffService = $stuffService;
        $this->translator = $translator;
    }

    /**
     * @param $name
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentStuffGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey("id");

        $grid->setDataSource($this->stuffModel->loadStuff());

        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('name','Jméno')->setSortable();
        $grid->addColumnText('surname','Příjmení')->setSortable();
        $grid->addColumnDateTime('alias','Přezdívka','')->setAlign('left')->setSortable();

        $grid->addColumnText('email','E-mail')
             ->setRenderer(function ($datasource) {
                $userData = $this->stuffModel->loadUserStuffData($datasource->id);
                 if ($userData) {return $userData->email;}
                           else {return '';}
             })
             ->setSortable();
        $grid->addColumnText('active','Aktivní')
            ->setRenderer(function ($datasource) {
                switch ($datasource->active){
                    case 0: return Html::el('i')->addAttributes(['class'=>'fa fa-circle-o']); break;
                    case 1: return Html::el('i')->addAttributes(['class'=>'fa fa-check-circle-o']); break;
                    default: return Html::el('i')->addAttributes(['class'=>'fa fa-circle-o']); break;
                }
            })
            ->setSortable()
            ->setAlign('center');

        $grid->addAction('edit','','EditAction!',['id'])
            ->setTitle('Edit')
            ->setClass('no-border')
            ->setIcon('edit')
            ->setAlign('center');

        $grid->setTranslator($this->translator->translate());
        return $grid;
    }


    /**
     * @return Form
     */
    protected  function createComponentEditStuffForm()
    {
        $form = new Form();
        $form->addHidden('id','');
        $form->addText('name','Jméno')->setRequired(true);
        $form->addText('surname','Příjmení')->setRequired(true);
        $form->addText('alias','Přezdívka')->setRequired(true);
        $form->addSubmit("submit", "Uložit")->setHtmlAttribute('class', 'btn btn-success pull-right');

        $userData = $this->userModel->loadUsers();

        $data[0]="E-mail";

        foreach($userData as $key=>$user)
        {
            $data[$user->id] = $user->username.' - '.$user->email;
        }

        $form->addSelect('stuff_id','Uživatel', $data);

        if ($this->id) {
            $stuff = $this->stuffModel->loadStuffData($this->id);
            $user = $this->stuffModel->loadUserStuffData($this->id);

            if (!$user) {$user = new ArrayHash(); $user->id = 0;}

            $form->setDefaults(['id' => $stuff->id,
                'name' => $stuff->name,
                'surname' => $stuff->surname,
                'alias' => $stuff->alias,
                'stuff_id' => $user->id]);
        }

        $form->onSuccess[] = [$this, "editStuff"];

        return $form;
    }

    public function createComponentStuffFormModal(){
        $form = new Form();

        $form->addText('name','Jméno')
             ->setRequired(true)
             ->setHtmlAttribute('class','form-control');
        $form->addText('surname','Příjmení')
            ->setRequired(true)
            ->setHtmlAttribute('class','form-control');
        $form->addText('alias','Přezdívka')
            ->setRequired(true)
            ->setHtmlAttribute('class','form-control');
        if ($this->id){
            $stuff = $this->stuffModel->loadStuffData($this->id);
            $form->setDefaults(['name'=>$stuff->name,
                                'surname'=>$stuff->surname,
                                'alias'=>$stuff->alias]);
        }

        $form->addSubmit("submit", "Uložit")->setHtmlAttribute('class', 'btn btn-success pull-right');
        $form->onSuccess[] = [$this, "addStuff"];

        return $form;
    }

    /**
     * @param $form
     * @param $data
     */
    public function addStuff($form, $data){
        $this->stuffService->addStuff($data->name, $data->surname, $data->alias);
        $this->redirect('Stuff:stuff');
    }

    /**
     * @param $form
     * @param $data
     */
    public function editStuff($form, $data){
        $this->stuffService->editStuff($data->stuff_id, $data->name, $data->surname, $data->alias);
        $this->redirect('Stuff:stuff');
    }

    public function renderStaff()
    {}

    /**
     * @param $id
     */
    public function handleEditAction($id){
      $user = $this->userModel->loadUserFromStuff($id);
      if ($user){
          $this->redirect('User:userProfile',$user->id);
      } else {
          $this->id = $id;
          $this->getPresenter()->template->modalTemplate = 'add.latte';
      }


    }

    public function handleAddStuff(){
        $this->getPresenter()->template->modalTemplate = 'add.latte';

    }
}