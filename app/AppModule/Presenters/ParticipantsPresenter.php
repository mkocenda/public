<?php

namespace App\App\Presenter;

use App\Model\ParticipantsModel;
use App\Model\ParentsModel;
use App\Model\InsuranceModel;
use App\Model\UsersModel;
use App\Model\Types;
use App\App\Service\ParticipantsService;
use Joseki\Application\Responses\PdfResponse;
use Nette\Application\UI\Form;
use Nette\ArrayHash;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Translator;

class ParticipantsPresenter extends BasePresenter
{

    public $participantsModel;
    public $participantsService;
    public $parentsModel;
    public $insuranceModel;
    public $usersModel;
    public $translator;

    public $participant_id;
    public $parent_id;

    public function __construct(ParticipantsModel   $participantsModel,
                                ParticipantsService $participantsService,
                                ParentsModel        $parentsModel,
                                InsuranceModel      $insuranceModel,
                                UsersModel          $usersModel,
                                Translator          $translator)
    {
        $this->participantsModel = $participantsModel;
        $this->participantsService = $participantsService;
        $this->parentsModel = $parentsModel;
        $this->insuranceModel = $insuranceModel;
        $this->usersModel = $usersModel;
        $this->translator = $translator;
    }

    public function createComponentParticipantsGrid($name)
    {
        $grid = new DataGrid($this, $name);

        $grid->setPrimaryKey("id");
        $grid->setDataSource($this->participantsModel->getAllParticipantsByOrganisation($this->user->identity->getData()['organisation_id']));
//        $grid->setColumnsHideable();
        $grid->setRefreshUrl(false);

        $grid->addColumnText('name', $this->translator->translate('firstname'));
        $grid->addColumnText('surname', $this->translator->translate('surname'));
        $grid->addColumnText('birthday', $this->translator->translate('birthday'))->setRenderer(function ($datasource) {
            $date = new DateTime($datasource->birthday);
            if ($date->format('Y-m-d') == '-0001-11-30') {
                return '';
            }
            return $date->format('d.m.Y');

        });
        $grid->addColumnText('parents_id', $this->translator->translate('parent'))->setRenderer(function ($datasource) {
            if ($datasource->parents_id) {
                $parent = $this->parentsModel->getParentByID($datasource->parents_id);
                return $parent->surname . ' ' . $parent->name . ' / ' . $parent->phone;
            }
            return '';
        });

        $grid->addColumnText('insurance_id', $this->translator->translate('insurance'))->setRenderer(function ($datasource) {
            if ($datasource->insurance_id) {
                $insurance = $this->insuranceModel->getInsuranceById($datasource->insurance_id);
                return $insurance->code . ' / ' . $insurance->name;
            }
            return '';
        });

        $grid->addAction('editParticipant', '', 'editParticipant!', ['id'])
            ->setTitle($this->translator->translate('edit'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_OK)
            ->setIcon('edit')
            ->setAlign('center');
        /*
                $grid->addAction('medical', '', 'medical!', ['id'])
                    ->setTitle($this->translator->translate('medical_records'))
                    ->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
                    ->setIcon('briefcase-medical')
                    ->setAlign('center');
        */
        $grid->addAction('print', '', 'print!', ['id'])
            ->setTitle($this->translator->translate('print'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_OK)
            ->setIcon('print')
            ->setAlign('center');

        $grid->setTranslator($this->translator->translator());

        return $grid;
    }

    public function createComponentParentsGrid($name)
    {
        $grid = new DataGrid($this, $name);

        $grid->setPrimaryKey("id");
        $grid->setDataSource($this->parentsModel->listParentsByOrganisationId($this->user->identity->getData()['organisation_id']));
        $grid->setRefreshUrl(false);
        $grid->addColumnText('name', $this->translator->translate('firstname'));
        $grid->addColumnText('surname', $this->translator->translate('surname'));
        $grid->addColumnText('email', $this->translator->translate('email'));
        $grid->addColumnText('phone', $this->translator->translate('phone'));
        $grid->addColumnText('user_id', $this->translator->translate('username'))->setRenderer(function ($datasource) {
            $user = $this->usersModel->getUserByID($datasource->user_id);
            return $user->username;
        });

        $grid->addAction('editParent', '', 'editParent!', ['id'])
            ->setTitle($this->translator->translate('edit'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_OK)
            ->setIcon('edit')
            ->setAlign('center');

        $grid->setTranslator($this->translator->translator());
        return $grid;
    }

    public function createComponentParticipantFormModal()
    {
        $form = new Form();
        $form->addHidden('id');
        $form->addText('name', $this->translator->translate('firstname'));
        $form->addText('surname', $this->translator->translate('surname'));
        $form->addText('birthday', $this->translator->translate('birthday'))->setType('date');
        $insurances = $this->insuranceModel->selectAllInsurance();
        $form->addSelect('insurance_id', $this->translator->translate('insurance'), $insurances);
        $parents = array(0 => '') + $this->parentsModel->selectParentsByOrganisationId($this->user->identity->getData()['organisation_id']);
        $form->addSelect('parents_id', $this->translator->translate('parent'), $parents);
        $form->addText('note', $this->translator->translate('note'));

        $form->addSubmit('submit', $this->translator->translate('btn_write'));
        $form->addButton('cancel', $this->translator->translate('btn_cancel'));

        if ($this->participant_id) {
            $participant = $this->participantsModel->getParticipantById($this->participant_id, $this->user->identity->getData()['organisation_id']);
            $parent_id = ($participant->parents_id > 0) ? $participant->parents_id : 0;
            $birthday = new DateTime($participant->birthday);
            $form->setDefaults(array('id' => $this->participant_id,
                'name' => $participant->name,
                'surname' => $participant->surname,
                'birthday' => $birthday->format('Y-m-d'),
                'insurance_id' => $participant->insurance_id,
                'parents_id' => $parent_id,
                'note' => $participant->note));
        }

        $form->onSuccess[] = [$this, 'saveParticipant'];
        return $form;
    }

    public function saveParticipant(Form $form, ArrayHash $data)
    {
        try {
            $organisation_id = $this->user->identity->getData()['organisation_id'];
            $this->participantsService->saveParticipant($data, $organisation_id);
            $this->flashMessage($this->translator->translate('participant_saved'), Types::SUCCESS);
            if ($this->isAjax()) {
                $this->redrawControl('participantsGrid');
            }
        } catch (\Exception $e) {
            $this->flashMessage($this->translator->translate('participant_not_saved'), Types::DANGER);
        }
    }

    public function createComponentParentFormModal()
    {
        $form = new Form();
        $form->addHidden('id');
        $form->addText('name', $this->translator->translate('firstname'));
        $form->addText('surname', $this->translator->translate('surname'));
        $form->addText('email', $this->translator->translate('email'));
        $form->addText('phone', $this->translator->translate('phone'));
        $users = array(0 => '') + $this->usersModel->getUsersSelect();
        $form->addSelect('user_id', $this->translator->translate('username'), $users);

        $form->addSubmit('submit', $this->translator->translate('btn_write'));
        $form->addButton('cancel', $this->translator->translate('btn_cancel'));

        if ($this->parent_id) {
            $parent = $this->parentsModel->getParentByID($this->parent_id);
            $form->setDefaults(array('id' => $this->parent_id,
                'name' => $parent->name,
                'surname' => $parent->surname,
                'email' => $parent->email,
                'phone' => $parent->phone,
                'user_id' => $parent->user_id
            ));
        }

        $form->onSuccess[] = [$this, 'saveParent'];
        return $form;
    }

    public function saveParent(Form $form, ArrayHash $data)
    {
        try {
            $this->participantsService->saveParent($data);
            $this->flashMessage($this->translator->translate('parent_saved'), Types::SUCCESS);
        } catch (\Exception $e) {
            $this->flashMessage($this->translator->translate('parent_not_saved'), Types::DANGER);
        }
    }


    public function handleEditParent($id)
    {
        $this->parent_id = $id;
        $this->template->modalTemplate = 'editParent.latte';
    }


    public function handleEditParticipant($id)
    {
        $this->participant_id = $id;
        $this->template->modalTemplate = 'editParticipant.latte';
    }

    public function handleMedical($id)
    {
        $this->participant_id = $id;
    }

    public function handlePrint($id)
    {
        $this->participant_id = $id;
        $participant = $this->participantsModel->getParticipantById($id, $this->user->identity->getData()['organisation_id']);
        $participant_records = $this->participantsModel->getParticipantsRecords($id);
        $participant_actions = $this->participantsModel->getParticipantAction($id);
        $insurance = $this->insuranceModel->getInsuranceById($participant->insurance_id);
        $parent = ($participant->parents_id) ? $this->parentsModel->getParentByID($participant->parents_id) : null;
        $template = $this->createTemplate();
        $template->setTranslator($this->translator->translator());
        $template->setFile(__DIR__ . '/Templates/Participants/participantCard.latte');
        $template->participant = $participant;
        $template->participant_actions = $participant_actions;
        $template->participant_records = $participant_records;
        $template->insurance = $insurance;
        $template->parent = $parent;

        $pdf = new PdfResponse($template);
        $pdf->documentTitle = 'participant_card_' . $participant->surname . '_' . $participant->name;
        $this->getPresenter()->sendResponse($pdf);
    }

    public function handleAddParent(){
        $this->template->modalTemplate = 'editParent.latte';
    }

    public function handleAddParticipant(){
        $this->template->modalTemplate = 'editParticipant.latte';
    }
}