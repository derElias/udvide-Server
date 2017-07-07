<?php
require_once 'vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: User
 * Date: 28.06.2017
 * Time: 21:48
 */
class editor extends udvide_entity
{
    /** @var string */
    private $user;
    /** @var string */
    private $target;

    public static function readAll()
    {
        $sql = 'SELECT tName, uName FROM udvide.Editors';
        return access_DB::prepareExecuteFetchStatement($sql);
    }

    public static function readAllTargetsFor(string $user) {
        return (new self())->setUser($user)->readAllTargets();
    }

    public static function readAllUsersFor(string $target) {
        return (new self())->setTarget($target)->readAllUsers();
    }

    public function readAllTargets()
    {
        $sql = 'SELECT tName as "0" FROM udvide.Editors WHERE uName = ?';
        $db = access_DB::prepareExecuteFetchStatement($sql, [$this->user]);
        if($db !== false) {
            foreach ($db as $k => $v) {
                $db[$k] = $v[0];
            }
        }
        return $db;
    }

    public function readAllUsers()
    {
        $sql = 'SELECT uName as "0" FROM udvide.Editors WHERE tName = ?';
        $db = access_DB::prepareExecuteFetchStatement($sql, [$this->target]);
        if($db !== false) {
            foreach ($db as $k => $v) {
                $db[$k] = $v[0];
            }
        }
        return $db;
    }

    public function create()
    {
        $logRole = user::getLoggedInUser()->getRole();
        $logName = user::getLoggedInUser()->getUsername();
        if (!($logRole >= MIN_ALLOW_TARGET_ASSIGN
            || ($logRole >= MIN_ALLOW_TARGET_SELF_ASSIGN
                && $logName == $this->user)
            || ($logRole >= MIN_ALLOW_TARGET_SELF_ASSIGN_OWN
                && $logName == target::fromDB($this->target)->getOwner())))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $sql = 'INSERT INTO udvide.Editors VALUES (?,?)';
        access_DB::prepareExecuteFetchStatement($sql,[$this->target,$this->user]);
    }

    public function delete()
    {
        $logRole = user::getLoggedInUser()->getRole();
        $logName = user::getLoggedInUser()->getUsername();
        if (!($logRole >= MIN_ALLOW_TARGET_DIVEST
            || ($logRole >= MIN_ALLOW_TARGET_SELF_DIVEST
                && $logName == $this->user)
            || ($logRole >= MIN_ALLOW_TARGET_SELF_DIVEST_OWN
                && $logName == target::fromDB($this->target)->getOwner())))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $sql = 'DELETE FROM udvide.Editors WHERE tName = ? AND uName = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$this->target,$this->user]);
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
     * @param user|string $user
     * @return editor
     */
    public function setUser($user = null): editor
    {
        if (isset($user)) {
            if (is_string($user)) {
                $this->user = $user;
            } else {
                $this->user = $user->getUsername();
            }
        }
        return $this;
    }

    /**
     * @param target|string $target
     * @return editor
     */
    public function setTarget($target = null): editor
    {
        if (isset($target)) {
            if (is_string($target)) {
                $this->target = $target;
            } else {
                $this->target = $target->getName();
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTName(): string
    {
        return $this->getTarget();
    }

    /**
     * @return string
     */
    public function getUName(): string
    {
        return $this->getUser();
    }
    //</editor-fold>
}