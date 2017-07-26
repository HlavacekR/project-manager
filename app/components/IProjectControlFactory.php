<?php

namespace App\Components;



interface IProjectControlFactory
{
    /**
     * @param $projectId
     * @param $link
     * @return ProjectControl
     */
    public function create($projectId,$link);
}