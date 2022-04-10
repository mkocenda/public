<?php
namespace App\ActionsModule;

use App\ActionsModule\Model\ActionModel;
use Nette\DateTime;
use Nette\Utils\Json;
use Nette\Application\Responses\JsonResponse;

class ActionsApiPresenter extends BasePresenter
{

    public $actionModel;

    public function __construct(ActionModel $actionModel)
    {
        $this->actionModel = $actionModel;
    }

    public function actionPlannedAction(){
        $data = $this->actionModel->loadAvailableActions();
        $jsonData = [];
        $path = $this->container->parameters['dataPath'];
        foreach ($data as $record) {
            $startTime = new DateTime($record->starttime);
            $stopTime = new DateTime($record->stoptime);
            $jsonData[]  = array('name'=>$record->name,
                            'motto'=>$record->motto,
                            'description'=>$record->description,
                            'starttime'=>$startTime->format('d.m.Y'),
                            'stoptime'=>$stopTime->format('d.m.Y'),
                            'agefrom'=>$record->agefrom,
                            'photo'=>$path.'/'.$record->photo);
        }
        $this->sendResponse(new JsonResponse($jsonData));
    }


}