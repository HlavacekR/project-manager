<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class ProjectPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    private $database;
    static $projectTypes = array(
        '1' => 'časově omezený projekt',
        '2' => 'Continuous integration'
    );

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
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
        $form->onSuccess[] = [$this, 'postFormSucceeded'];

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
                $this->redirect("Project:edit",array("postId" => $postId));
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
            $project = $this->database->table('project')->insert($values);
        }

        $this->flashMessage("Projekt byl úspěšně uložen.", 'success');
        $this->redirect('Homepage:default');

    }

    public function actionEdit($postId)
    {
        $project = $this->database->table('project')->get($postId);
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

    public function renderEdit($postId)
    {
        $this->template->edit_id = $postId;


    }

}
