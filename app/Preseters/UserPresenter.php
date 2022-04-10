<?php

namespace App;

use Nette\Application\UI\Form;
use App\Service\UserService;
use App\Model\ProfileModel;
use App\Model\CertificatesModel;
use App\Model\CertificatesTypesModel;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use App\Model\UserModel;
use App\Model\Translator;
use App\Model\StuffModel;

class UserPresenter extends BasePresenter
{

    public $profileModel;
    public $certificatesModel;
    public $certificatesTypesModel;
    public $userModel;
    public $stuffModel;
    public $userService;
    public $userid;
    public $translator;

    public function __construct(ProfileModel $profileModel,
                                CertificatesModel $certificatesModel,
                                CertificatesTypesModel $certificatesTypesModel,
                                UserService $userService,
                                UserModel $userModel,
                                Translator $translator,
                                StuffModel $stuffModel)
    {
        $this->profileModel = $profileModel;
        $this->certificatesModel = $certificatesModel;
        $this->certificatesTypesModel = $certificatesTypesModel;
        $this->userModel = $userModel;
        $this->stuffModel = $stuffModel;
        $this->userService = $userService;
        $this->translator = $translator;
    }

    /**
     * @return Form
     */
    public function createComponentAddCertificateForm()
    {
        $form = new Form();
        $values = $this->certificatesTypesModel->loadCertificatesType();
        $form->addSelect('certtype','Druh osvědčení', $values)
              ->setRequired(true)
              ->setHtmlAttribute('class','form-control');
        $form->addText('validfrom','Datum získání')
              ->setType('date')
              ->setRequired(true)
              ->setHtmlAttribute('class','form-control');
        $form->addText('validto','Platnost do')
             ->setType('date')
             ->setHtmlAttribute('class','form-control');
        $form->addUpload('certfile','Osvědčení')
              ->setHtmlAttribute('class','form-control');
        $form->addHidden('userid');
        $form->addSubmit('submit','Přidat')
             ->setHtmlAttribute('class','form-control btn-success');
        $form->setDefaults(['userid'=>$this->userid]);
        $form->onSuccess[] = [$this, "addCertifiatesSuccess"];
        return $form;
    }

    /**
     * @param $form
     * @param $data
     * @throws \Nette\Application\AbortException
     */
    public function addCertifiatesSuccess($form, $data){
        $this->userService->addUserCertificate($data->userid, $data->certtype, $data->validfrom, $data->validto, $data->certfile);
        $this->redirect('User:userProfile', $data->userid);
    }

    /**
     * @return Form
     * @throws \Exception
     */
    public function createComponentModifyProfileForm()
    {
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
        $form->addText('birthday','Datum narození')
             ->setType('date')
             ->setHtmlAttribute('class','form-control');
        $form->addText('email','E-mail')
            ->setRequired(true)
            ->setHtmlAttribute('class','form-control');
        $form->addUpload('photo','Fotka');
        $form->addHidden('userid');
        $form->addSubmit('submit','Zapsat')
            ->setHtmlAttribute('class','form-control btn-success');
        if ($this->userid) {
            $userData = $this->userModel->loadUserData($this->userid);
            $birthday = new \DateTime($userData->birthday);
            $form->setDefaults(['userid'=>$this->userid,
                'name'=>$userData->name,
                'surname'=>$userData->surname,
                'email'=>$userData->email,
                'alias'=>$userData->alias,
                'birthday'=>$birthday->format('Y-m-d')]);
        }
        $form->onSuccess[] = [$this, "modifyProfileSuccess"];
        return $form;
    }

    /**
     * @param $form
     * @param $data
     * @throws \Nette\Application\AbortException
     */
    public function modifyProfileSuccess($form, $data){
        $this->userService->modifyUserData($data->userid, $data->name, $data->surname, $data->alias, $data->email, $data->birthday, $data->photo);
        $this->redirect('User:userProfile', $data->userid);
    }


    public function createComponentUserFormModal(){
        $form = new Form();

        $form->addText('username','Uživatelské jméno')
              ->setRequired(true)
              ->setHtmlAttribute('class','form-control');
        $form->addPassword('password','Heslo')
             ->setHtmlAttribute('class','form-control');
        $form->addText('email','E-mail')
             ->setRequired(true)
             ->setHtmlAttribute('class','form-control');
        $stuffs = $this->stuffModel->loadStuff();
        $stuffs_data = array(0=>'');
        foreach ($stuffs as $stuff)
        {
            $stuffs_data[$stuff->id] = $stuff->name.' '.$stuff->surname;
        }
        $form->addSelect('stuff_id','Zaměstnanecký profil', $stuffs_data)
             ->setRequired(true)
             ->setHtmlAttribute('class','form-control');
        $form->addCheckbox('enabled','Aktivní účet')
             ->setHtmlAttribute('class','form-control');
        $form->addHidden('userid');
        $form->addSubmit('submit','Zapsat')
             ->setHtmlAttribute('class','form-control btn-success');
        $form->setDefaults(['userid'=>$this->userid]);

        if ($this->userid){
            $user = $this->userModel->loadUser($this->userid);
            $form->setDefaults(['username'=>$user->username,
                                'email'=>$user->email,
                                'stuff_id'=>$user->stuff_id,
                                'enabled'=>$user->enabled
                ]);
        }

        $form->onSuccess[] = [$this, "userFormSuccess"];

        return $form;
    }

    public function userFormSuccess($form, $data)
    {
        if($data->userid) {
            $this->userService->modifyUser($data->userid, $data->username, $data->password, $data->email, $data->stuff_id, $data->enabled);
        }
        else {
            $this->userService->addUser($data->username, $data->password, $data->email, $data->stuff_id, $data->enabled);
        }
        $this->redirect('User:users');
    }

    public function createComponentUsersGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey("id");

        $grid->setDataSource($this->userModel->loadUsers());

        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('username','Uživatelské jméno')->setSortable();
        $grid->addColumnText('email','E-mailová adresa')->setSortable();
        $grid->addColumnText('stuff_id','Zaměstnanec')->setSortable()
              ->setRenderer(function ($datasource) {
             if ($datasource->stuff_id) {
                 return Html::el('i')->addAttributes(['class'=>'fa fa-user']);
             }
             else {
                 return Html::el('i')->addAttributes(['class'=>'fa']);
             }
        })
            ->setAlign('center');

        $grid->addColumnText('enabled','Povolen')->setSortable()
            ->setRenderer(function ($datasource) {
                switch ($datasource->enabled){
                    case 0: return Html::el('i')->addAttributes(['class'=>'fa fa-circle-o']); break;
                    case 1: return Html::el('i')->addAttributes(['class'=>'fa fa-check-circle-o']); break;
                    default: return Html::el('i')->addAttributes(['class'=>'fa fa-circle-o']); break;
                }
            })
            ->setAlign('center');
        $grid->addAction('edit','','editUser!',['id'])
            ->setTitle('Edit')
            ->setClass('no-border')
            ->setIcon('edit')
            ->setAlign('center');
        $grid->addAction('role','','editUserRoles!',['id'])
            ->setTitle('Přístupy')
            ->setClass('no-border')
            ->setIcon('users')
            ->setAlign('center');

           $grid->setTranslator($this->translator->translate());
    }

    /**
     *
     * @param $userid
     */
    public function actionUserProfile($userid){
        $image = $this->profileModel->loadUserProfileImage($userid);
        $certificates = $this->certificatesModel->loadUserCartificates($userid);
        $profileData = $this->profileModel->loadProfileData($userid);
        $this->template->profileImage = $image;
        $this->template->profileData = $profileData;
        $this->template->certificates = $certificates;
        $this->template->userid = $userid;
    }

    /**
     * @param $userid
     */
    public function handleAddCertificate($userid){
        $this->userid = $userid;
        $this->getPresenter()->template->modalTemplate = 'addCertificate.latte';
    }

    /**
     * @param $userid
     */
    public function handleModifyProfile($userid){
        $this->userid = $userid;
        $this->getPresenter()->template->modalTemplate = 'modifyProfile.latte';
    }

    /**
     * @param $userid
     * @param $certid
     * @throws \Nette\Application\AbortException
     */
    public function handleCertificate($userid, $certid)
    {
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"image.jpg\"");
        $file = $this->certificatesModel->getCertificateFilename($userid, $certid);
        readfile($file);
        $this->terminate();
    }

    /**
     * @param $id
     */
    public function handleEditUser($id)
    {
        $this->userid = $id;
        $this->getPresenter()->template->modalTemplate = 'userForm.latte';
    }

    /**
     * @param $id
     */
    public function handleEditUserRoles($id){

    }

    /**
     *
     */
    public function handleAddUser()
    {
        $this->getPresenter()->template->modalTemplate = 'userForm.latte';
    }


}