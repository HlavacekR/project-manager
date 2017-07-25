<?php

namespace App\Presenters;

use Nette;
use App\Model\ProjectManager;

class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    private $projectManager;

    public function __construct(ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
    }

    public function renderDefault()
    {
        $this->template->projects = $this->projectManager->getProjects();
        $this->template->projectTypes = ProjectPresenter::$projectTypes;

    }
}
