<?php
require_once 'udvide.php';

/**
 * Created by PhpStorm.
 * User: User
 * Date: 28.06.2017
 * Time: 21:48
 */
class editor extends udvide_entity
{
    /** @var user */
    private $user;
    /** @var target */
    private $target;

    public static function readAll()
    {
        $sql = 'SELECT tName, uName FROM udvide.Editors';
        return access_DB::prepareExecuteFetchStatement($sql);
    }

    public static function readAllTargetsFor(user $user) {
        return (new self())->setUser($user)->readAllTargets();
    }

    public static function readAllUsersFor(target $target) {
        return (new self())->setTarget($target)->readAllUsers();
    }

    public function readAllTargets()
    {
        $sql = 'SELECT tName FROM udvide.Editors WHERE uName = ?';
        return access_DB::prepareExecuteFetchStatement($sql, [$this->uName]);
    }

    public function readAllUsers()
    {
        $sql = 'SELECT uName FROM udvide.Editors WHERE tName = ?';
        return access_DB::prepareExecuteFetchStatement($sql, [$this->uName]);
    }

    public function create()
    {
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_TARGET_ASSIGN
            || (user::getLoggedInUser()->getRole() < MIN_ALLOW_TARGET_SELF_ASSIGN
                && user::getLoggedInUser()->getUsername() == $this->uName)
            || (user::getLoggedInUser()->getRole() < MIN_ALLOW_TARGET_SELF_ASSIGN_OWN
                && user::getLoggedInUser()->getUsername() == $this->target->getOwner()))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $sql = 'INSERT INTO udvide.Editors VALUES (?,?)';
        access_DB::prepareExecuteFetchStatement($sql,[$this->tName,$this->uName]);
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function __set(string $name, $value)
    {
        switch($name) {
            case 'target':
                return $this->setTarget($value);
            case 'user':
                return $this->setUser($value);
            /*
            // Hm.. not sure about this code here...
            case 'tName': // Use with care!
                return $this->setTarget(target::fromDB($value));
            case 'uName': // Use with care!
                return $this->setUser(user::fromDB($value));
            */
            default:
                return $this;
        }
    }

    public function __get(string $name)
    {
        switch($name) {
            case 'target':
                return $this->getTarget();
            case 'user':
                return $this->getUser();
            case 'tName':
                return $this->getTName();
            case 'uName':
                return $this->getUName();
            default:
                return null;
        }
    }

    //<editor-fold desc="Setter / Getter">
    /**
     * @param user $user
     * @return editor
     */
    public function setUser(user $user = null): editor
    {
        if (isset($user)) {
            $this->user = $user;
        }
        return $this;
    }

    /**
     * @param target $target
     * @return editor
     */
    public function setTarget(target $target = null): editor
    {
        if (isset($target)) {
            $this->target = $target;
        }
        return $this;
    }

    /**
     * @return target
     */
    public function getTarget(): target
    {
        return $this->target;
    }

    /**
     * @return user
     */
    public function getUser(): user
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTName(): string
    {
        return $this->target->getName();
    }

    /**
     * @return string
     */
    public function getUName(): string
    {
        return $this->user->getUsername();
    }
    //</editor-fold>
}