<?php

namespace App\ActionsModule;

use App\ActionsModule\Model\StuffModel;
use App\Model\UserModel;
use App\ActionsModule\Service\StuffService;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

class StuffPresenter extends BasePresenter
{

    public $id;

    public $stuffModel;

    public $userModel;

    public $stuffService;

    public function __construct(StuffModel $stuffModel, UserModel $userModel, StuffService $stuffService){
        $this->stuffModel = $stuffModel;
        $this->userModel = $userModel;
        $this->stuffService = $stuffService;
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

        $grid->addAction('edit','','editAction!',['id'])
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

        $grid->setTranslator($this->translator);
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

    /**
     * @param $form
     * @param $data
     */
    public function editStuff($form, $data){
        $this->stuffService->editStuff($data->id, $data->name, $data->surname, $data->alias,  $data->stuff_id);
    }

    public function renderStaff()
    {}

    /**
     * @param $id
     */
    public function handleEditAction($id){
        $this->id = $id;
        $this->template->setFile(__DIR__.'\..\templates\stuff\edit.latte');
    }
}