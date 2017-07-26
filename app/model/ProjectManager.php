<?php
namespace App\Model;

use Nette;
use Nette\SmartObject;

class ProjectManager
{
    
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
            return false;
        } else {
            return $this->database->table('project')->insert($values);
        }
    }

    public function getCountUsers()
    {
        return $this->database->table('user')->count('*');
    }

    public function getLastId() {

        return $this->database->getInsertId('project');
    }

}