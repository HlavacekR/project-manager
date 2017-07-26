<?php

namespace App\Components;

use App\Model\ProjectManager;
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
     * @var App\Model\ProjectManager
     */
    private $projectManager;

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
    public function __construct($projectId,$link, projectManager $projectManager)
    {
        $this->projectManager = $projectManager;
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

        $form->addSubmit('send', 'Uložit projekt');

        //bootstrap 3 renderer
        $form = $this->bootstrapRendering($form);
        $form->onSuccess[] = [$this, "processForm"]; //$this->processForm;


        //setting deafult values for editing
        if($this->projectId != null) {
            $form = $this->loadingDefaultData($form, $this->projectId);
        }
        
        return $form;
    }

    public function loadingDefaultData($form,$projectId) {


        $project = $this->projectManager->getProject($projectId);

        if($project) {

            $projectAr = $project->toArray();
            $projectAr["deadline"] = $project->deadline->format("d.m.Y");

            if(!isset(ProjectManager::$projectTypes[$projectAr["type"]])) {
                unset($projectAr["type"]);
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
            $this->presenter->flashMessage("Datum je ve špatném formátu.", 'error');
            $this->redirect("this");

            return false;

        }

        $values["deadline"] = $date;

        $this->projectManager->saveProject($this->projectId,$values);

        $this->onFormSuccess();
    }

    public function render()
    {

        $this->template->editId = $this->projectId;
        $this->template->homepageLink = $this->homepageLink;

        $this->template->render(__DIR__ . '/ProjectControl.latte');

    }

}

