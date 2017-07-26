<?php
namespace App\Model;

use Nette;

class UserManager
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

    public function getUser($id)
    {
        return $this->database->table('user')->get($id);
            
    }
    
    public function getUsers()
    {
        return $this->database->table('user')
            ->order('lastname ASC');
    }
}