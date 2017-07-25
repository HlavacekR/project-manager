<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;
use App\Model\ProjectManager;

class ProjectPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    private $projectManager;
    private $id;
    static $projectTypes = [
        '1' => 'časově omezený projekt',
        '2' => 'Continuous integration'
    ];

    public function __construct(ProjectManager $projectManager,$id = null)
    {
        $this->projectManager = $projectManager;

    }

    protected function createComponentProjectForm()
    {
        $form = new Form;
        $form->addText('name', 'Název projektu:')
            ->setRequired();
        $form->addText('deadline', 'Datum odevzdání projektu:')
            ->setRequired();
        $form->addSelect('type', 'Typ projektu:', self::$projectTypes)
            ->setPrompt('Zvolte typ projektu')->setRequired();
        $form->addCheckbox('web_project', 'Webový projekt:');

        $form->addSubmit('send', 'Uložit projekt');

        //$form->onSuccess[] = [$this, 'postFormSucceeded'];


        //bootstrap 3 renderer
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


    public function postFormSucceeded($form, $values)
    {
        $postId = $this->getParameter('postId');


        try {
            $date = new \Nette\Utils\DateTime($values["deadline"]);
        } catch(\Exception $e) {
            $this->flashMessage("Datum je ve špatném formátu.", 'error');
            if ($postId) {
                $this->redirect("Project:edit",["postId" => $postId]);
            } else {
                $this->redirect("Project:create");
            }

            return false;

        }

        $values["deadline"] = $date;

        if ($postId) {
            $project = $this->database->table('project')->get($postId);
            $project->update($values);
        } else {
            $this->database->table('project')->insert($values);
        }

        $this->flashMessage("Projekt byl úspěšně uložen.", 'success');
        $this->redirect('Homepage:default');

    }

    public function actionEdit($postId)
    {
        $project = $this->projectManager->getProject($postId);
        if (!$project) {
            $this->error('Příspěvek nebyl nalezen');
        }

        $projectAr = $project->toArray();
        $projectAr["deadline"] = $project->deadline->format("d.m.Y");

        if(!isset(ProjectPresenter::$projectTypes[$projectAr["type"]])) {
            unset($projectAr["type"]);
        }

        $this['projectForm']->setDefaults($projectAr);

    }
    public function actionDelete($postId)
    {
        $project = $this->database->table('project')->get($postId);
        if (!$project) {
            $this->error('Příspěvek nebyl nalezen');
        }

        $project->delete();

        $this->flashMessage("Projekt byl úspěšně smazán.", 'success');
        $this->redirect('Homepage:default');

    }

    public function renderEdit($postId)
    {
        $this->template->edit_id = $postId;


    }

}
