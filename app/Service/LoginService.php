<?php

namespace App\Service;

use App\Model\UsersModel;
use PHPMailer;
use App\Service\ConfigService;

class LoginService
{

	public $usersModel;
	public $phpMailer;
	public $configService;

	public function __construct(UsersModel $usersModel, PHPMailer $phpMailer, ConfigService $configService)
	{
		$this->usersModel = $usersModel;
		$this->phpMailer = $phpMailer;
		$this->configService = $configService;
		$this->MailInit();
	}

	public function MailInit()
	{
		$config = $this->configService->readConfig('smtp');
		$this->phpMailer->SMTPDebug = $config['SMTPDebug'];
		$this->phpMailer->isSMTP();
		$this->phpMailer->Host = $config['Host'];
		$this->phpMailer->SMTPAuth = $config['SMTPAuth'];
		if ($config['SMTPAuth']) {
			$this->phpMailer->Username = $config['Username'];
			$this->phpMailer->Password = $config['Password'];
		}
		if ($config['SMTPSecure'] <> '') {
			$this->phpMailer->SMTPSecure = $config['SMTPSecure'];
		}
		$this->phpMailer->Port = $config['Port'];
		$this->phpMailer->CharSet = $config['CharSet'];
	}

	/**
	 * @param int $user_id
	 * @return string
	 */
	public function createConfirmLink(int $user_id, string $text){
		$confirm_data = $this->usersModel->addConfirmHash($user_id);
		$link = '<a href="http://adam.czlc/reset/'.$confirm_data['confirm_hash'].'/">'.$text.'</a>';
		return $link;
	}

	public function forget(string $email, string $username)
	{
		$user = $this->usersModel->getUserByEmailUsername($email, $username);
		$link = $this->createConfirmLink($user->user_id,'zde');
		$this->phpMailer->setFrom('info@adam.czlc', 'Automat ADAM');
		$this->phpMailer->Subject = 'Obnova zapomenutého hesla';
		$body = "Dobrý den,<br><br>někdo, pravdědpodobě Vy jste si zažádal o obnovu hesla. Nové heslo si můžete nastavit : ".$link." <br><br>
				 Pokud jste o obnovu nezažádal můžete email klidně ignorovat, platnost odkazu je 10minut.<br><br>
				 Na email neodpovídejte, je automaticky generován.<br>
				 S pozdravem A.D. a M.";
		$this->phpMailer->Body = $body;
		$this->phpMailer->addAddress($email, $user->surname . ' ' . $user->name);
		$this->phpMailer->isHTML(true);
		try {
			$this->phpMailer->send();
		} catch (\Exception $e) {

		}
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param int $id
	 * @return void
	 */
	public function resetPassword(string $username, string $password, int $id){
		$this->usersModel->setUser($username, $password, 0, $id);
	}
}