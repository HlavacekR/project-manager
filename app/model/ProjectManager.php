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
    public static $projectTypes = [
        '1' => 'časově omezený projekt',
        '2' => 'Continuous integration'
    ];

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

    public function saveProject($id,$values)
    {
        if ($id) {
            $project = $this->database->table('project')->get($id);
            $project->update($values);
        } else {
            $this->database->table('project')->insert($values);
        }
    }
    

}