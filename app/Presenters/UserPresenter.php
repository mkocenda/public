<?php

namespace App\Presenter;

use App\Model\Types;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use App\Service\LoginService;
use App\Model\UsersModel;
use App\Model\SettingsModel;

class UserPresenter extends BasePresenter
{

	public $loginService;
	public $userModel;
	public $confirmHash;
	public $settingsModel;

	public function __construct(LoginService $loginService, UsersModel $usersModel, SettingsModel $settingsModel)
	{
		$this->loginService = $loginService;
		$this->userModel = $usersModel;
		$this->settingsModel = $settingsModel;
	}

	public function createComponentSignForm($name)
	{
		$form = new Form();

		$form->addText('username', $this->translator->translate('username'))->setAttribute('placeholder', $this->translator->translate('username'));
		$form->addPassword('email', $this->translator->translate('email'))->setAttribute('placeholder', $this->translator->translate('email'));
		$form->addPassword('password', $this->translator->translate('password'))->setAttribute('placeholder', $this->translator->translate('password'));
		$form->addSubmit('submit', $this->translator->translate('btn_login'));

		$form->onSuccess[] = [$this, "sign"];
		return $form;
	}

	public function loginForm(Form $form, ArrayHash $data)
	{
		$this->loginService->loginUser($data->username, $data->password);
	}

	public function renderLogin()
	{
		/** ToDo vyřešit organizace  */
		$this->template->allow_reset = $this->settingsModel->getSettingsByModuleProperty('users', 'AllowResetPassword', 2)->value;
		$this->template->allow_signup = $this->settingsModel->getSettingsByModuleProperty('users', 'AllowParentsRegistration', 2)->value;
	}

	public function createComponentForgetForm()
	{
		$form = new Form();

		$form->addText('username', $this->translator->translate('username'))->setAttribute('placeholder', 'Uživatelské jméno');
		$form->addText('email', $this->translator->translate('email'))->setAttribute('placeholder', 'E-mail');

		$form->addSubmit('submit', $this->translator->translate('btn_send'))->setHtmlAttribute('style', 'margin-left: 0px;');
		$form->onSuccess[] = [$this, "forget"];
		return $form;
	}

	public function createComponentLogoutForm()
	{
		$form = new Form();

		$form->addSubmit('logout', $this->translator->translate('btn_logout'))->setHtmlAttribute('style', 'margin-left: 0px;');
		$form->onSuccess[] = [$this, "logout"];
		return $form;
	}

	public function logout(Form $form, ArrayHash $data)
	{
		$this->log('user/logout', 'Odhlášení proběhlo úspěšně.', Types::LOG_INFO);
		$this->user->logout(TRUE);
		$this->session->destroy();
		$this->redirect(':User:login');
	}

	public function forget(Form $form, ArrayHash $data)
	{
		try {
			$this->loginService->forget($data->email, $data->username);
		} catch (\Exception $e) {
		}
		$this->redirect(':User:forgetinfo');
	}

	public function actionReset($confirm_hash)
	{
		$this->confirmHash = $confirm_hash;
	}

	public function renderReset($confirm_hash)
	{
		$user = $this->userModel->getUserByHash($confirm_hash);
		if ($user) {
			$this->template->valid_hash = Types::ENABLED;
			$this->template->hash = '';
		} else {
			$this->template->valid_hash = Types::DISABLED;
			$this->template->hash = 'Platnost odkazu vypršela.';
		}
	}

	public function createComponentResetPasswordForm()
	{
		$form = new Form();
		$form->addPassword('new_password_1', $this->translator->translate('new_password'));
		$form->addPassword('new_password_2', $this->translator->translate('new_password_again'));
		$form->addHidden('confirm_hash');
		$form->addSubmit('submit', $this->translator->translate('btn_reset'))->setHtmlAttribute('style', 'margin-left: 0px;');
		$form->setDefaults(array('confirm_hash' => $this->confirmHash));
		$form->onValidate[] = [$this, 'validatePasswords'];
		$form->onSuccess[] = [$this, "resetPassword"];
		return $form;
	}

	public function validatePasswords(Form $form, ArrayHash $data)
	{
		if ($data->new_password_1 <> $data->new_password_2) {
			$form->addError('Hesla nesouhlasí.');
		}
		$this->template->form = $form;
	}

	public function resetPassword(Form $form, ArrayHash $data)
	{
		try {
			$user = $this->userModel->getUserByHash($data->confirm_hash);
			$this->loginService->resetPassword($user->username, $data->new_password_1, $user->id);
		} catch (\Exception $e) {
		}
		$this->redirect(':User:resetsuccess');
	}
}