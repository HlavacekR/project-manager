<?php
namespace App\Model;

use Nette;
use Nette\SmartObject;

class ProjectManager
{

    /**
     * @var App\Model\PrVsUsManager
     */
    private $prVsUsManager;
    /**
     * @var Nette\Database\Context
     */
    private $database;
    public static $projectTypes = [
        '1' => 'časově omezený projekt',
        '2' => 'Continuous integration'
    ];

    public function __construct(Nette\Database\Context $database, prVsUsManager $prVsUsManager)
    {
        
        $this->database = $database;
        $this->prVsUsManager = $prVsUsManager;
    }

    public function getProject($id)
    {
        return $this->database->table('project')->get($id);
            
    }
    
    public function getProjects()
    {
        return $this->database->table('project')
            ->order('id DESC');
    }

    public function saveProject($id,$values)
    {
        if ($id) {
            $project = $this->database->table('project')->get($id);
            $project->update($values);
            return false;
        } else {
            return $this->database->table('project')->insert($values);
        }
    }

    public function getCountUsers()
    {
        return $this->database->table('user')->count('*');
    }

    public function loadingDefaultData($form,$project,$users) {


        if($project) {

            $projectAr = $project->toArray();
            $projectAr["deadline"] = $project->deadline->format("d.m.Y");

            if(!isset(self::$projectTypes[$projectAr["type"]])) {
                unset($projectAr["type"]);
            }

            $i = 1;
            foreach($users as $user) {
                $projectAr["add_user_".$i] = $user->user_id;
                $i++;
            }

            $form->setDefaults($projectAr);
        }

        return $form;
    }

    public function processFormProject($form, $values, $projectId ) {

        try {
        $date = new \Nette\Utils\DateTime($values["deadline"]);
        } catch(\Exception $e) {
            $form->addError("Datum je ve špatném formátu.");
            return false;

        }

        $values["deadline"] = $date;
        $userArr = [];
        foreach($values as $ind => $val) {
            $testInd = explode("add_user_",$ind);
            if(isset($testInd[1])) {
                $userArr[$testInd[1]] = $val;
                unset($values[$ind]);
            }
        }

        $idLast = $this->saveProject($projectId,$values);
        if($projectId != null) {
            //delete all
            foreach($userArr as $userId) {
                $this->prVsUsManager->deleteRecord($userId,$projectId);

            }
        }

        //add only from form
        foreach($userArr as $userId) {
            if($userId !== null) {
                if ($projectId != null) {
                    $projectId = $projectId;
                } else {
                    $projectId = $idLast->id;
                }

                $values = [
                    "user_id" => $userId,
                    "project_id" => $projectId
                ];
                $this->prVsUsManager->insertRecord($values);
            }
        }
        
    }
}