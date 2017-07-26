<?php

namespace App\Presenters;

use Nette;
use App\Model\ProjectManager;
use App\Components\IProjectControlFactory;


class ProjectPresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var IProjectControlFactory @inject
     */
    public $projectControlFactory;
    /**
     * @var App\Model\ProjectManager
     */
    private $projectManager;
    /**
     * @var int Project id
     */
    private $id;


    public function __construct($postId = null,ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
        $this->id = $postId;

    }

    protected function createComponentProjectForm()
    {

        $form = $this->projectControlFactory->create($this->id,$this->link("Homepage:default"));

        $form->onFormSuccess[] = function () {
            $messId = ($this->id != null) ? " id #".$this->id : "" ;

            $this->flashMessage('Projekt'.$messId.' byl úspěšně uložen.', 'success');
            $this->redirect('Homepage:default');
            ;
        };

        return $form;

    }

    public function actionEdit($postId = null)
    {
        if($postId != null) {

            $this->id = $postId;
            $project = $this->projectManager->getProject($postId);
            if (!$project) {
                $this->error('Příspěvek nebyl nalezen');
            }

        }

    }

    public function actionDelete($postId)
    {
        $project = $this->projectManager->getProject($postId);
        if (!$project) {
            $this->error('Příspěvek nebyl nalezen');
        }

        $project->delete();

        $this->flashMessage("Projekt byl úspěšně smazán.", 'success');
        $this->redirect('Homepage:default');

    }
    
}
