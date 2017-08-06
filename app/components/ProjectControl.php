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
     * @param $link
     * @param App\Model\projectManager $projectManager model project
     * @param App\Model\userManager $userManager model project
     * @param App\Model\prVsUsManager $prVsUsManager model project
     */
    public function __construct($projectId,$link, projectManager $projectManager, userManager $userManager, prVsUsManager $prVsUsManager)
    {
        $this->projectManager = $projectManager;
        $this->prVsUsManager = $prVsUsManager;
        $this->userManager = $userManager;


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
            $form = $this->projectManager->loadingDefaultData($form,$project,$users);
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

        $this->projectManager->processFormProject($form, $values,$this->projectId);
        $this->onFormSuccess();
    }

    public function render()
    {

        $this->template->editId = $this->projectId;
        $this->template->homepageLink = $this->homepageLink;

        $this->template->render(__DIR__ . '/ProjectControl.latte');

    }

}

