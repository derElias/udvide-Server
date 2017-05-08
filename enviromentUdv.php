<?php
class enviromentUdv
{
    private $externalBasePath;
    private $internalBasePath;
    private $sqlDbServerName;
    private $sqlDbUserName;
    private $sqlDbPassword;
    private $sqlDbName;

    /**
     * enviroment constructor.
     */
    public function __construct()
    {
        $host= gethostname();
        $ip = gethostbyname($host);
        $this->externalBasePath = $ip;
        $this->internalBasePath = "C:\\Users\\User\\Documents\\udvide-Server";
        $this->sqlDbServerName = "localhost";
        $this->sqlDbUserName = "username";
        $this->sqlDbPassword = "password";
        $this->sqlDbName = "udvide";
    }

    /**
     * @return string
     */
    public function getExternalBasePath()
    {
        return $this->externalBasePath;
    }

    /**
     * @return string
     */
    public function getInternalBasePath()
    {
        return $this->internalBasePath;
    }

    /**
     * @return string
     */
    public function getSqlDbServerName()
    {
        return $this->sqlDbServerName;
    }

    /**
     * @return string
     */
    public function getSqlDbUserName()
    {
        return $this->sqlDbUserName;
    }

    /**
     * @return string
     */
    public function getSqlDbPassword()
    {
        return $this->sqlDbPassword;
    }

    /**
     * @return string
     */
    public function getSqlDbName()
    {
        return $this->sqlDbName;
    }
}
