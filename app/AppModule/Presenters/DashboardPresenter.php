<?php

namespace App\App\Presenter;

use App\Model\ActionModel;
use App\Model\ParticipantsModel;
use App\Model\Types;
use App\Model\StuffModel;
use App\Model\CertificatesModel;
use App\Model\Translator;
use App\Model\SettingsModel;
use App\App\Service\SettingsService;
// use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use App\Service\TelegramService;
use App\Tools\ExtendForm;
use Nette;

class DashboardPresenter extends BasePresenter
{

	public $actionModel;
	public $stuffModel;
	public $certificatesModel;
	public $participantsModel;

	public $settingsModel;
	public $settingsService;

    public $telegramService;
	public $translator;
	private $formUtils;
	public function __construct(ActionModel       $actionModel,
								StuffModel        $stuffModel,
								CertificatesModel $certificatesModel,
								ParticipantsModel $participantsModel,
								SettingsModel     $settingsModel,
								SettingsService   $settingsService,
                                TelegramService   $telegramService,
                                ExtendForm        $formUtils,
								Translator        $translator)
	{
		parent::__construct();
		$this->actionModel = $actionModel;
		$this->stuffModel = $stuffModel;
		$this->certificatesModel = $certificatesModel;
		$this->participantsModel = $participantsModel;
		$this->settingsModel = $settingsModel;
		$this->settingsService = $settingsService;
        $this->telegramService = $telegramService;
		$this->formUtils = $formUtils;
		$this->translator = $translator;
	}

	public function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->redirect(":User:login");
		}
		if ($this->user->getIdentity()->getRoles()) {
			foreach ($this->user->getIdentity()->getRoles() as $role)
				if ($role === 'admin') {
					$this->redirect(":Admin:Dashboard:default");
				}
		}
	}

	public function renderDefault()
	{
		$user_data = $this->user->getIdentity()->getData();
		$stuffs = $this->stuffModel->getStuffsByOrganisationID($user_data['organisation_id']);
		$medical = $this->certificatesModel->getValidStuffCertificatesByType($user_data['organisation_id'], 1);
		$children = $this->participantsModel->getAllParticipantsByOrganisation($user_data['organisation_id']);

		$planned = $this->actionModel->listActionsByStatus($user_data['organisation_id'], Types::PLANNED);
		$running = $this->actionModel->listActionsByStatus($user_data['organisation_id'], Types::RUNNING);
		$done = $this->actionModel->listActionsByStatus($user_data['organisation_id'], Types::DONE);

		$certificates = $this->stuffModel->getGroupedStuffsByCertificateType($user_data['organisation_id']);
		$certificates_panel = $this->settingsModel->getSettingsByModuleProperty('stuffs', 'CertificatesOverview', $user_data['organisation_id']);

		$ending_certificates = $this->stuffModel->getEndingStuffsCertificates($user_data['organisation_id']);
		$ending_panel = $this->settingsModel->getSettingsByModuleProperty('stuffs', 'CertificateReminder', $user_data['organisation_id']);

		$stuffs_birthday = $this->stuffModel->getStuffsBirthday($user_data['organisation_id']);
		$birthday_panel = $this->settingsModel->getSettingsByModuleProperty('stuffs', 'BirthdayReminder', $user_data['organisation_id']);

		$badge_panel = $this->settingsModel->getSettingsByModuleProperty('dashboard', 'BadgePanel', $user_data['organisation_id']);
		
		$actions_panel = $this->actionModel->getIncommingAction($user_data['organisation_id']);
		$incomming_panel = $this->settingsModel->getSettingsByModuleProperty('actions', 'IncommingActions', $user_data['organisation_id']);

		$this->template->planned = count($planned);
		$this->template->running = count($running);
		$this->template->done = count($done);

		$this->template->children = count($children);
		$this->template->stuff = count($stuffs);
		$this->template->medical = count($medical);

		$this->template->badge_panel = $badge_panel->value;

		$this->template->certificates = $certificates;
		$this->template->certificates_panel = $certificates_panel->value;

		$this->template->ending_certificates = $ending_certificates;
		$this->template->ending_panel = $ending_panel->value;

		$this->template->stuffs_birthday = $stuffs_birthday;
		$this->template->birthday_panel = $birthday_panel->value;
		
		$this->template->incomming_actions = $actions_panel;
		$this->template->actions_panel = $incomming_panel->value;
	}

	public function createComponentGlobalSettingForm()
	{
		$form = new ExtendForm();
		$user_data = $this->user->getIdentity()->getData();
		
		$form->addCheckbox('stuffs__BirthdayReminder',
			$form->tooltipForm($this->translator->translate('birthdayreminder'),
							   $this->translator->translate('birthdayreminder_hint'))
		);

		$form->addCheckbox('stuffs__CertificateReminder',
			$form->tooltipForm($this->translator->translate('certificatereminder'),
							   $this->translator->translate('certificatereminder_hint'))
		);
		
		$form->addCheckbox('stuffs__CertificatesOverview',
			$form->tooltipForm($this->translator->translate('certificatesoverview'),
							   $this->translator->translate('certificatesoverview_hint'))
		);
		
		$form->addCheckbox('dashboard__BadgePanel',
			$form->tooltipForm($this->translator->translate('badgepanel'),
							   $this->translator->translate('badgepanel_hint'))
		);

		$form->addCheckbox('actions__IncommingActions',
			$form->tooltipForm($this->translator->translate('incomming_actions'),
							   $this->translator->translate('incomming_actions_hint'))
		);

		$form->addText('slack__SLACK_URL', $this->translator->translate('slack_url'));
		$form->addText('slack__SLACK_CHANNEL', $this->translator->translate('slack_channel'));
		$form->addText('telegram__TELEGRAM_API_KEY', $this->translator->translate('telegram_api_key'));
		$form->addText('telegram__TELEGRAM_CHAT_ID', $this->translator->translate('telegram_chat_id'));

		for ($start = 0; $start <=23; $start++){
			$hours[$start] = $start;
		}
		
		$form->addSelect('actions__BeginHour',
			$form->tooltipForm($this->translator->translate('begin_hour'),
				$this->translator->translate('begin_hour_hint')), $hours);
		$form->addSelect('actions__EndHour',
			$form->tooltipForm($this->translator->translate('end_hour'),
				$this->translator->translate('end_hour_hint')), $hours);
		
		$form->addCheckbox('actions__WaitingList',
			$form->tooltipForm($this->translator->translate('waiting_list'),
							   $this->translator->translate('waiting_list_hint'))
		);

		$form->addCheckbox('participants__CheckLimitAge',
			$form->tooltipForm($this->translator->translate('checklimitage'),
							   $this->translator->translate('checklimitage_hint'))
		);

		$operators_item = array();
		$operators = $this->settingsModel->getOperatorsByOrganisationID($user_data['organisation_id']);
		foreach ($operators as $operator) {
			$operators_item[$operator->id] = strtoupper($operator->name);
		}
		$form->addCheckboxList('operators', $this->translator->translate('operators'), $operators_item);

        $form->addCheckbox('users__AllowParentsRegistration', $this->translator->translate('allow_parents_registration'));
        $form->addCheckbox('users__AllowResetPassword', $this->translator->translate('allow_reset_password'));

		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'))->setHtmlAttribute('type', 'reset');
		$user_data = $this->user->getIdentity()->getData();

		$operators_item = array();
		foreach ($operators as $operator) {
			if ($operator->enabled == Types::ENABLED) {
				$operators_item[] = $operator->id;
			}
		}
		$form->setDefaults(array('participants__CheckLimitAge' => $this->settingsModel->getSettingsByModuleProperty('participants', 'CheckLimitAge', $user_data['organisation_id'])->value,
			'stuffs__BirthdayReminder' => $this->settingsModel->getSettingsByModuleProperty('stuffs', 'BirthdayReminder', $user_data['organisation_id'])->value,
			'stuffs__CertificateReminder' => $this->settingsModel->getSettingsByModuleProperty('stuffs', 'CertificateReminder', $user_data['organisation_id'])->value,
			'stuffs__CertificatesOverview' => $this->settingsModel->getSettingsByModuleProperty('stuffs', 'CertificatesOverview', $user_data['organisation_id'])->value,
			'dashboard__BadgePanel' => $this->settingsModel->getSettingsByModuleProperty('dashboard', 'BadgePanel', $user_data['organisation_id'])->value,
			'slack__SLACK_URL' => $this->settingsModel->getSettingsByModuleProperty('slack', 'SLACK_URL', $user_data['organisation_id'])->value,
			'slack__SLACK_CHANNEL' => $this->settingsModel->getSettingsByModuleProperty('slack', 'SLACK_CHANNEL', $user_data['organisation_id'])->value,
			'telegram__TELEGRAM_API_KEY' => $this->settingsModel->getSettingsByModuleProperty('telegram', 'API_KEY', $user_data['organisation_id'])->value,
			'telegram__TELEGRAM_CHAT_ID' => $this->settingsModel->getSettingsByModuleProperty('telegram', 'CHAT_ID', $user_data['organisation_id'])->value,
			'operators' => $operators_item,
			'users__AllowParentsRegistration' =>$this->settingsModel->getSettingsByModuleProperty('users', 'AllowParentsRegistration', $user_data['organisation_id'])->value,
			'users__AllowResetPassword' =>$this->settingsModel->getSettingsByModuleProperty('users', 'AllowResetPassword', $user_data['organisation_id'])->value,
			'actions__WaitingList' =>$this->settingsModel->getSettingsByModuleProperty('actions', 'WaitingList', $user_data['organisation_id'])->value,
			'participants__CheckLimitAge' =>$this->settingsModel->getSettingsByModuleProperty('participants', 'CheckLimitAge', $user_data['organisation_id'])->value,
			'actions__BeginHour' =>$this->settingsModel->getSettingsByModuleProperty('actions', 'BeginHour', $user_data['organisation_id'])->value,
			'actions__EndHour' =>$this->settingsModel->getSettingsByModuleProperty('actions', 'EndHour', $user_data['organisation_id'])->value,
		));

		$form->onSuccess[] = [$this, 'saveSettings'];

		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveSettings(ExtendForm $form, ArrayHash $data)
	{
		try {
			$this->settingsService->saveSettings($data, $this->user->identity->getData()['organisation_id']);
			$this->flashMessage($this->translator->translate('settings_saved'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('settings_not_saved'), Types::DANGER);
		}
	}
}