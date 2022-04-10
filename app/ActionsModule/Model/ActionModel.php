<?php
namespace App\ActionsModule\Model;

use Nette;

class ActionModel
{
    public $database;

    public function __construct(Nette\Database\Context $database){
        $this->database = $database;
    }

    public function loadActions(){
        return $this->database->table('action')->fetchAll();
    }

    public function loadAvailableActions(){
        $ids = [];
        $alocations = $this->database->query('SELECT a.id, a.`limit`, COUNT(*) AS occupancy
                                                  FROM children2action c2a
                                                  JOIN children c ON c.id = c2a.children_id
                                                  JOIN actions a ON a.id = c2a.action_id
                                                  WHERE a.starttime > now()  
                                                  GROUP BY c2a.action_id, a.`limit`')->fetchAll();
        foreach($alocations as $key=>$alocation){
            if ($alocation->limit > $alocation->occupancy) {$ids[] = $alocation->id; }
        }

        $actions = $this->database->table('actions')->where('id', $ids)->fetchAll();
        return $actions;
    }



    public function loadPlannedActions($limit = false){
        $ids = [];
        $alocations = $this->database->query('SELECT a.id, a.`limit`, COUNT(*) AS occupancy
                                                  FROM children2action c2a
                                                  JOIN children c ON c.id = c2a.children_id
                                                  JOIN actions a ON a.id = c2a.action_id
                                                  WHERE a.starttime > now()  
                                                  GROUP BY c2a.action_id, a.`limit`')->fetchAll();
        foreach($alocations as $key=>$alocation){
            if (($alocation->limit > $alocation->occupancy) && $limit) {$ids[] = $alocation->id; }
            else {$ids[] = $alocation->id; };
        }

        $actions = $this->database->table('actions')->where('id', $ids);
        return $actions;
    }

    public function loadProcessActions($limit = false){
        $ids = [];
        $alocations = $this->database->query('SELECT a.id, a.`limit`, COUNT(*) AS occupancy
                                                  FROM children2action c2a
                                                  JOIN children c ON c.id = c2a.children_id
                                                  JOIN actions a ON a.id = c2a.action_id
                                                  WHERE now() BETWEEN a.starttime AND a.stoptime  
                                                  GROUP BY c2a.action_id, a.`limit`')->fetchAll();
        foreach($alocations as $key=>$alocation){
            if (($alocation->limit > $alocation->occupancy) && $limit) {$ids[] = $alocation->id; }
            else {$ids[] = $alocation->id; };
        }

        $actions = $this->database->table('actions')->where('id', $ids);
        return $actions;
    }

    public function loadDoneActions($limit = false){
        $ids = [];
        $alocations = $this->database->query('SELECT a.id, a.`limit`, COUNT(*) AS occupancy
                                                  FROM children2action c2a
                                                  JOIN children c ON c.id = c2a.children_id
                                                  JOIN actions a ON a.id = c2a.action_id
                                                  WHERE a.stoptime < now()  
                                                  GROUP BY c2a.action_id, a.`limit`')->fetchAll();
        foreach($alocations as $key=>$alocation){
            if (($alocation->limit > $alocation->occupancy) && $limit) {$ids[] = $alocation->id; }
            else {$ids[] = $alocation->id; };
        }

        $actions = $this->database->table('actions')->where('id', $ids);
        return $actions;
    }



    public function loadActionStuff($action_id){
        $records = $this->database->query('SELECT a2s.id, s.name, s.surname, s.alias, s.photo, s2t.name AS stuff_name
                                               FROM action2stuff a2s
                                               JOIN stuff s ON s.id = a2s.stuff_id
                                               JOIN stuff2type s2t ON s2t.id = a2s.stuff_type
                                               WHERE a2s.action_id = ? ORDER BY s2t.order',$action_id)
                                  ->fetchAll();
        return $records;
    }

    public function loadActionEvaluation($action_id){
        $records = $this->database->query('SELECT SUM(a2e.points) / COUNT(*) AS stars
                                               FROM action2evaluation a2e
                                               WHERE a2e.action_id = ?',$action_id)
            ->fetch();
        return $records;
    }

    public function loadAction($action_id){
        $action = $this->database->table('actions')->where('id = ?', $action_id)->fetch();
        return $action;
    }

}