<?php
class enviromentUdv
{
    private $externalBasePath;
    private $internalBasePath;
    private $sqlDbServerName;
    private $sqlDbUserName;
    private $sqlDbPassword;
    private $sqlDbName;
    private $sqlCharset;

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
        $this->sqlDbUserName = "root";
        $this->sqlDbPassword = "";
        $this->sqlDbName = "udvide";
        $this->sqlCharset = 'utf8mb4';
    }

    //<editor-fold desc="Getters">
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

    /**
     * @return string
     */
    public function getSqlCharset(): string
    {
        return $this->sqlCharset;
    }
    //</editor-fold>
}

