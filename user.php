<?php
require_once 'udvideV3.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 14.06.2017
 * Time: 18:37
 */
class user
{
    /** @var user */
    private static $loggedInUser;

    /** @var string max 127 S/G prevent change if logged in*/
    private $username;
    /** @var bool G prevent change*/
    private $deleted;
    /** @var string max 255 S prevent change if logged in*/
    private $passHash;
    /** @var int S/G prevent change if logged in conditionally */
    private $role;
    /** @var int */
    private $targetCreateLimit;

    /** @var  bool */
    private $isLoggedIn;

    //<editor-fold desc="Constructors">
    /**
     * user constructor.
     */
    public function __construct()
    {
        $this->isLoggedIn = false;
    }

    /**
     * indirect constructor
     * @param null $username
     * @return user
     */
    public static function fromDB($username = null) {
        $instance = new self();
        if (isset($username)) {
            $instance->setUsername($username)->read();
        }
        return $instance;
    }

    /**
     * indirect constructor
     * @param array $array
     * @return user
     */
    public static function fromArray(array $array = null) {
        $instance = new self();
        if (!empty($array))
            $instance->set($array);
        return $instance;
    }

    /**
     * indirect constructor
     * @param string $json
     * @return user
     */
    public static function fromJSON($json = '') {
        $instance = new self();
        if (!empty($json))
            $instance->set(json_decode($json, true));
        return $instance;
    }
    //</editor-fold>

    /**
     * @return user
     */
    public static function getLoggedInUser(): user
    {
        return self::$loggedInUser;
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
        $sql = <<<'SQL'
SELECT `username`, `role`
FROM udvide.users
WHERE deleted = 0 or deleted = false
SQL;
        $db = access_DB::prepareExecuteFetchStatement($sql);
        foreach ($db as $key => $userArr)
            $db[$key] = (new self())->set($userArr);
        return $db;
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
(deleted,`passHash`, `username`, `role`)
VALUES (FALSE,?,?,?);
SQL;
        $values = [
            $this->passHash,
            $this->username,
            isset($this->role) ? $this->role : 0
        ];
        access_DB::prepareExecuteStatementGetAffected($sql,$values);
        return $this;
    }

    /**
     * @return $this
     * @throws PermissionException
     */
    public function update() {
        // If not allowed to update and self-update (in case of self update)
        if (user::$loggedInUser->role < MIN_ALLOW_USER_UPDATE
            && !($this->isLoggedIn && $this->role < MIN_ALLOW_SELF_UPDATE))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $updateDB = false;
        $sql = '';

        foreach ($this as $key => $value) {
            if($key != 'isLoggedIn'
                && $key != 'deleted'
                && $key != 'old_username'
                &&isset($this->{$key})) {

                $sql .= " $key = ? , ";
                $ins[] = $value;
                $updateDB = true;
            }
        }

        if ($updateDB) {
            $sql = <<<SQL
DECLARE @dummy int;
UPDATE udvide.users
SET 
$sql
@dummy = 0
WHERE username = ?;
SQL;
            $ins[] = $this->username;
            access_DB::prepareExecuteFetchStatement($sql, $ins);
        }
        return $this;
    }

    /**
     * @return $this
     * @throws PermissionException
     */
    public function deactivate() {
        if (user::$loggedInUser->role < MIN_ALLOW_USER_DEACTIVATE
            && !($this->isLoggedIn && $this->role < MIN_ALLOW_SELF_DEACTIVATE))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);
        $sql = 'UPDATE udvide.users SET deleted = TRUE WHERE username = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$this->username]);
        $this->deleted = true;
        return $this;
    }

    /**
     * @throws PermissionException
     */
    public function delete() {
        if (user::$loggedInUser->role < MIN_ALLOW_USER_DELETE
            && !($this->isLoggedIn && $this->role < MIN_ALLOW_SELF_DELETE))
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        if (!$this->deleted) {
            // never delete directly
            $this->deactivate();
            return;
        }
        $sql = 'DELETE FROM udvide.Users WHERE username = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$this->username]);

        // Logout
        $this->isLoggedIn = false;
        if ($this === user::$loggedInUser)
            user::$loggedInUser = null;
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
            if (!$this->pepperedPassCheck($this->passHash, $userArr['passHash']))
                throw new LoginException(ERR_LOGIN_WRONGPASSWD,3);

            // fill this with the db values
            $this->set($userArr);
            // minimize risk of password readout
            $this->passHash = null;
            // lock object down
            $this->isLoggedIn = true;
        }

        // enable user to perform actions as themselves
        user::$loggedInUser = $this;

        return $this;
    }

    //<editor-fold desc="Password Utility">
    /**
     * Compares a sent in passHash (password) with a peppered and salted passHash
     * @param string $userPassHash the sent password value (should be hashed client-side)
     * @param string $serverPassHash the db stored password
     * @return bool
     */
    private function pepperedPassCheck(string $userPassHash,string $serverPassHash):bool
    {
        $keys = json_decode(file_get_contents('keys.json'));
        return password_verify(sha1($userPassHash . $keys->pepper), $serverPassHash);
    }

    /**
     * generates a peppered and salted passHash
     * @param string $new_users_password
     * @return bool|string
     */
    private function pepperedPassGen(string $new_users_password)
    {
        $keys = json_decode(file_get_contents('keys.json'));
        return password_hash(sha1($new_users_password . $keys->pepper), PASSWORD_DEFAULT);
    }
    //</editor-fold>

    /**
     * Fills the target from an array
     * @param array $data
     * @return $this
     */
    public function set(array $data)
    {
        foreach ($data AS $key => $value) {
            $this->__set($key, $value); // To use setters behind permission and type verification
        }
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
            $this->passHash = $this->pepperedPassGen($passHash);
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
            if ($role <= user::$loggedInUser->role)
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
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * @return int
     */
    public function getTargetCreateLimit(): int
    {
        return $this->targetCreateLimit;
    }
    //</editor-fold>
}