<?php
namespace App\Model;

use Nette;

class MemberManager
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
        return $this->database->table('member')->get($id);
            
    }
    
    public function getUsers()
    {
        return $this->database->table('member')
            ->order('lastname ASC');
    }
}