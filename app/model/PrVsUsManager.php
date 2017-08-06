<?php
namespace App\Model;

use Nette;

class PrVsUsManager
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

    public function deleteRecord($userId,$projectId)
    {
        $pr_vs_us = $this->database->table('pr_vs_us')->where("user_id",$userId)->where("project_id",$projectId);
        $pr_vs_us->delete();
           
    }
    
    public function insertRecord($values)
    {
        return $this->database->table('pr_vs_us')->insert($values);
    }
}