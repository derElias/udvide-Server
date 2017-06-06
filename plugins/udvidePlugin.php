<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.06.2017
 * Time: 15:04
 */
interface udvidePlugin
{
    /**
     * Your code to modify the target on create before returning
     * return true if successful
     * @param $target
     * @return mixed
     */
    public function onTargetCreate(&$target);
}