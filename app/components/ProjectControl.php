<?php

namespace App\Components;

use Nette;
use App\Model\ProjectManager;
use App\Model\PrVsUsManager;
use App\Model\UserManager;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;

class ProjectControl extends UI\Control
{

    /**
     * @var Nette\Database\Context
     */
    private $database;
    /**
     * @var define Nette even
     */
    public $onFormSuccess;
    /**
     * @var App\Model\PrVsUsManager
     */
    private $prVsUsManager;
    /**
     * @var App\Model\ProjectManager
     */
    private $projectManager;
    /**
     * @var App\Model\UserManager
     */
    private $userManager;
    /**
     * @var array list of users for select element
     */
    private $userSelect;
    /**
     * @var int Id project
     */
    private $projectId;
    /**
     * @var string Homepage link
     */
    private $homepageLink;

    /**
     * @param $projectId
     * @param App\Model\projectManager $projectManager model project
     */
    public function __construct(Nette\Database\Context $database, $projectId,$link, projectManager $projectManager, userManager $userManager, prVsUsManager $prVsUsManager)
    {
        $this->projectManager = $projectManager;
        $this->prVsUsManager = $prVsUsManager;
        $this->userManager = $userManager;
        $this->database = $database;

        // load list of users for select element
        foreach($this->userManager->getUsers() as $user) {
            $this->userSelect[$user->id] = $user->firstname." ".$user->lastname;
        }

        $this->projectId = $projectId;

        $this->homepageLink = $link;
    }

    public function createComponentMyForm()
    {

        $form = new Form;
        $form->addText('name', 'Název projektu:')
            ->setRequired();
        $form->addText('deadline', 'Datum odevzdání projektu:')
            ->setRequired();
        $form->addSelect('type', 'Typ projektu:', ProjectManager::$projectTypes)
            ->setPrompt('Zvolte typ projektu')->setRequired();
        $form->addCheckbox('web_project', 'Webový projekt:');


        $countUsers = $this->projectManager->getCountUsers();

        $i = 1;
        if($this->projectId != null) {
            $project = $this->projectManager->getProject($this->projectId);
            $users = $project->related("pr_vs_us");

            foreach ($users as $user) {
                $form->addSelect('add_user_'.$i, 'Uživatel '.$i.':', $this->userSelect)
                    ->setPrompt('Zvolte uživatele');
                $i++;
            }
        }

        for($j=$i; $j <= $countUsers  ;$j++) {
            $form->addSelect('add_user_'.$j, 'Uživatel '.$j.':', $this->userSelect)
                ->setPrompt('Zvolte uživatele');
        }
        
        $form->addSubmit('send', 'Uložit projekt');

        //bootstrap 3 renderer
        $form = $this->bootstrapRendering($form);
        $form->onSuccess[] = [$this, "processForm"]; //$this->processForm;
        
        //setting deafult values for editing
        if($this->projectId != null) {
            $form = $this->loadingDefaultData($form,$project,$users);
        }

        return $form;
    }

    public function loadingDefaultData($form,$project,$users) {


        if($project) {

            $projectAr = $project->toArray();
            $projectAr["deadline"] = $project->deadline->format("d.m.Y");

            if(!isset(ProjectManager::$projectTypes[$projectAr["type"]])) {
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

    public function bootstrapRendering($form)  {

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        $renderer->wrappers['error']['container'] = 'div class="alert alert-danger"';
        $renderer->wrappers['error']['item'] = 'p';
        
        $form->getElementPrototype()->class('form-horizontal');
        $form->onRender[] = function ($form) {
            foreach ($form->getControls() as $control) {
                $type = $control->getOption('type');
                if ($type === 'button') {
                    $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-success' : 'btn btn-default');
                    $usedPrimary = true;
                } elseif (in_array($type, ['text', 'textarea', 'select'], true)) {
                    $control->getControlPrototype()->addClass('form-control');
                } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                    $control->getSeparatorPrototype()->setName('div')->addClass($type);
                }
            }
        };

        return $form;
    }



    public function processForm($form, $values) {



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

        $idLast = $this->projectManager->saveProject($this->projectId,$values);
        if($this->projectId != null) {
            //delete all
            foreach($userArr as $userId) {
                $this->prVsUsManager->deleteRecord($userId,$this->projectId);

            }
        }

        //add only from form
        foreach($userArr as $userId) {
            if($userId !== null) {
                if ($this->projectId != null) {
                    $projectId = $this->projectId;
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

        $this->onFormSuccess();
    }

    public function render()
    {

        $this->template->editId = $this->projectId;
        $this->template->homepageLink = $this->homepageLink;

        $this->template->render(__DIR__ . '/ProjectControl.latte');

    }

}

