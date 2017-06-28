<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.06.2017
 * Time: 15:04
 */
interface udvidePlugin
{
    //<editor-fold desc="Target">
    /**
     * Your code to modify the target before creation
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * @param target $target
     * @return boolean
     */
    public function onTargetCreate(target &$target):bool;
    /**
     * Your code to modify the target before change
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * $target are the values written to what was previously $subject
     * so make $target what you want in the end and $subject-s name what target should be deleted
     * return false and create a new target yourself, if you want to prevent the original from being deleted
     * @param target $target
     * @param target $subject
     * @return boolean
     */
    public function onTargetUpdate(target &$target, target &$subject):bool;
    /**
     * Your code to modify the target before deletion
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * @param target $target
     * @return boolean
     */
    public function onTargetDelete(target &$target):bool;

    // public function onTargetRead(&$target); // stretch goal since todo objects aren't read as objects
    //</editor-fold>


    //<editor-fold desc="User">
    /**
     * Your code to modify the user before creation
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * @param user $user
     * @return boolean
     */
    public function onUserCreate(user &$user):bool;
    /**
     * Your code to modify the user before change
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * $user are the values written to what was previously $subject
     * so make $user what you want in the end and $subject-s name what target should be deleted
     * return false and create a new user yourself, if you want to prevent the original from being deleted // todo docu example
     * @param user $user
     * @param user $subject
     * @return boolean
     */
    public function onUserUpdate(user &$user, user $subject):bool;
    /**
     * Your code to modify the user before deletion
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * @param user $user
     * @return boolean
     */
    public function onUserDelete(&$user):bool;

    // public function onUserRead(&$target); // stretch goal since todo objects aren't read as objects
    //</editor-fold>


    //<editor-fold desc="Map">
    /**
     * Your code to modify the map before creation
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * @param map $map
     * @return boolean
     */
    public function onMapCreate(map &$map):bool;
    /**
     * Your code to modify the map before change
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * $map are the values written to what was previously $subject
     * so make $map what you want in the end and $subject-s name what target should be deleted
     * return false and create a new user yourself, if you want to prevent the original from being deleted // todo docu example
     * @param map $map
     * @param map $subject
     * @return boolean
     */
    public function onMapUpdate(map &$map, map &$subject):bool;
    /**
     * Your code to modify the map before deletion
     * return true as "go-ahead" and false to abort silently
     * throw a pluginException to indicate a problem to the user
     * @param map $map
     * @return bool
     */
    public function onMapDelete(map &$map):bool;

    // public function onUserRead(&$target); // stretch goal since todo objects aren't read as objects
    //</editor-fold>


    /**
     * Your code to modify a user before he logs in
     * only a valid User can be logged in
     * @param user $user
     * @return bool
     */
    public function onLogin(user &$user):bool;


    /**
     * Your code to modify a Editor-permission grant
     * @param user $user
     * @param target $target
     * @param $editor
     * @return bool
     */
    public function onEditorAssign(user &$user, target &$target, &$editor):bool; // todo
    /**
     * Your code to modify a Editor-permission revoke
     * @param user $user
     * @param target $target
     * @param $editor
     * @return bool
     */
    public function onEditorDivest(user &$user, target &$target, &$editor):bool; // todo

    // public function onLog(&$log); // stretch goal since todo low reward

    /**
     * Your code to modify a Read Access from a mobile Client
     * @param target $target
     * @return bool
     */
    public function onMobileRead(target &$target):bool; // the content of the Target is what is sent in the end
}