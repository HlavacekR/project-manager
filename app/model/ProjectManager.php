<?php
namespace App\Model;

use Nette;

class ProjectManager
{
    use Nette\SmartObject;

    /**
     * @var Nette\Database\Context
     */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        
        $this->database = $database;
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
}