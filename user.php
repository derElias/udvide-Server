<?php
require_once 'vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 14.06.2017
 * Time: 18:37
 */

class user extends udvide
{
    /** @var user */
    private static $loggedInUser;

    /** @var string max 127 S/G prevent change if logged in*/
    protected $username;
    /** @var bool G prevent change*/
    private $deleted;
    /** @var string max 255 S prevent change if logged in*/
    private $passHash;
    /** @var int S/G prevent change if logged in conditionally */
    protected $role;
    /** @var int */
    protected $targetCreateLimit;

    /** @var  bool */
    private $isLoggedIn;

    /**
     * user constructor.
     */
    public function __construct()
    {
        $this->isLoggedIn = false;
    }

    /**
     * @return user
     */
    public static function getLoggedInUser(): user
    {
        return isset(self::$loggedInUser) ? self::$loggedInUser : null;
    }

    //<editor-fold desc="CRUD DB">
    /**
     * @return $this
     */
    public function read()
    {
        // fill this with the db values
        $db = $this->readComplete();
        // do not give away private information
        $this->set($db[0])
            ->passHash = null;
        return $this;
    }

    /**
     * @return array|false
     */
    public static function readAll() {
        if (user::$loggedInUser->role < MIN_ALLOW_USER_READALL) {
            return [[
                "username" => user::getLoggedInUser()->getUsername(),
                "role" => user::getLoggedInUser()->getRole()
            ]];
        } else {
            $sql = <<<'SQL'
SELECT `username`, `role`
FROM udvide.users
WHERE deleted = 0 OR deleted = FALSE
SQL;
            $db = access_DB::prepareExecuteFetchStatement($sql);
            /*foreach ($db as $key => $userArr)
                $db[$key] = (new self())->set($userArr);*/
            return $db;
        }
    }

    /**
     * @return $this
     * @throws IncompleteObjectException
     * @throws PermissionException
     */
    public function create() {
        if (user::$loggedInUser->role < MIN_ALLOW_USER_CREATE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);
        if (!isset($this->username) || !isset($this->passHash))
            throw new IncompleteObjectException(ERR_USER_DATASET_INVALID,1);

        $sql = <<<'SQL'
INSERT INTO udvide.users
(deleted,`passHash`, `username`, `role`, targetCreateLimit)
VALUES (FALSE,?,?,?,?);
SQL;
        $this->role = isset($this->role) ? $this->role : 0;
        $this->targetCreateLimit = isset($this->targetCreateLimit) ? $this->targetCreateLimit :
            ($this->role < MIN_ALLOW_TARGET_CREATE ? 0 : -1);

        $values = [
            helper::pepperedPassGen($this->passHash),
            $this->username,
            $this->role,
            $this->targetCreateLimit
        ];
        access_DB::prepareExecuteStatementGetAffected($sql,$values);
        return $this;
    }

    /**
     * @param string|null $subject
     * @return $this
     * @throws PermissionException
     */
    public function update(string $subject = null) {

        $subject = empty($subject) ? $this->username : $subject;

        // If not allowed to update and self-update (in case of self update)
        if (user::$loggedInUser->role < MIN_ALLOW_USER_UPDATE
            && !($this->isLoggedIn && $this->role < MIN_ALLOW_SELF_UPDATE))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $updateDB = false;
        $sql = '';

        foreach ($this as $key => $value) {
            if($key != 'isLoggedIn'
                && $key != 'deleted'
                && $key != 'editors'
                &&isset($this->{$key})) {

                if ($key == 'passHash') {
                    $value = helper::pepperedPassGen($value);
                }

                $sql .= " $key = ? , ";
                $ins[] = $value;
                $updateDB = true;
            }
        }

        $sql = rtrim(rtrim($sql),',');

        if ($updateDB) {
            $sql = <<<SQL
UPDATE udvide.users
SET $sql
WHERE username = ?;
SQL;
            $ins[] = $subject;
            access_DB::prepareExecuteFetchStatement($sql, $ins);
        }
        return $this;
    }

    /**
     * @return $this
     * @throws PermissionException
     */
    public function delete() {
        if (user::$loggedInUser->role < MIN_ALLOW_USER_DEACTIVATE
            && !($this->isLoggedIn && $this->role < MIN_ALLOW_SELF_DEACTIVATE))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);
        $sql = 'UPDATE udvide.users SET deleted = TRUE WHERE username = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$this->username]);
        $this->deleted = true;

        $this->isLoggedIn = false;
        if ($this->username === user::$loggedInUser->username)
            user::$loggedInUser = null;
        return $this;
    }

    /**
     * @return array|false
     */
    private function readComplete()
    {
        $sql = <<<'SQL'
SELECT case when u.deleted = 1 or u.deleted = true then true else false end as deleted,`passHash`, `role`, `targetCreateLimit`
FROM udvide.users u
WHERE username = ?
SQL;
        return access_DB::prepareExecuteFetchStatement($sql, [$this->username]);
    }
    //</editor-fold>

    /**
     * Login the current User ($this) - all actions will be performed with his rights and he is blocked from any forbidden change
     * @return $this
     * @throws LoginException
     */
    public function login()
    {
        if (!$this->isLoggedIn) { // if they already did log in routine with this connection: don't check again
            $db = $this->readComplete();

            if ($db === false)
                throw new LoginException(ERR_LOGIN_USERUNKNOWN,1);
            $userArr = $db[0];
            if ($userArr['deleted'] === true)
                throw new LoginException(ERR_LOGIN_USERDELETED,2);
            if (!helper::pepperedPassCheck($this->passHash, $userArr['passHash']))
                throw new LoginException(ERR_LOGIN_WRONGPASSWD,3);
            // fill this with the db values
            $this->set($userArr);
            // role is prevented to be used as permission leverage so we have to set it manually
            $this->role = $userArr['role']; // todo make obsolete by adapting / testing the setter
            // minimize risk of password readout
            $this->passHash = null;
            // lock object down
            $this->isLoggedIn = true;
        }

        // enable user to perform actions as themselves
        user::$loggedInUser = $this;

        return $this;
    }

    /**
     * Set via available Fluent Setter or return $this
     * @param string $name
     * @param mixed $value
     * @return user
     */
    public function __set(string $name, $value): user
    {
        switch($name) {
            case 'name':
            case 'username':
                return $this->setUsername($value);
            case 'passHash':
                return $this->setPassHash($value);
            case 'role':
                return $this->setRole($value);
            case 'targetCreateLimit':
                return $this->setTargetCreateLimit($value);
            default:
                return $this;
        }
    }

    /**
     * Get via available Getter or return null
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        switch($name) {
            case 'username':
                return $this->getUsername();
            case 'role':
                return $this->getRole();
            case 'targetCreateLimit':
                return $this->getTargetCreateLimit();
            default:
                return null;
        }
    }

    //<editor-fold desc="Fluent Setters with validation">
    public function setName(string $name = null): user
    {
        return $this->setUsername($name);
    }
    /**
     * @param string $username
     * @return user
     */
    public function setUsername(string $username = null): user
    {
        if (isset($username)) {
            $this->username = $username;
        }
        return $this;
    }

    /**
     * @param string $passHash
     * @return user
     */
    public function setPassHash(string $passHash = null): user
    {
        if (isset($passHash)) {
            $this->passHash = $passHash;
        }
        return $this;
    }

    /**
     * @param int $role
     * @return user
     */
    public function setRole(int $role): user
    {
        if (isset($role)) {
            // prevent permission leverage
            if (is_null(user::$loggedInUser) || $role <= user::$loggedInUser->role)
                $this->role = $role;
        }
        return $this;
    }

    /**
     * @param int $targetCreateLimit
     * @return user
     */
    public function setTargetCreateLimit(int $targetCreateLimit): user
    {
        if (isset($targetCreateLimit)) {
            $this->targetCreateLimit = $targetCreateLimit;
        }
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="Getter">
    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return int|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return int|null
     */
    public function getTargetCreateLimit()
    {
        return $this->targetCreateLimit;
    }

    /**
     * @return string|null
     */
    public function getPassHash()
    {
        return $this->passHash;
    }
    //</editor-fold>
}