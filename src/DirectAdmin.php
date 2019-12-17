<?php
namespace DirectAdmin;

/**
 * The DirectAdmin API
 */
class DirectAdmin {
    
    private $adapter;
    
    public $service;
    public $loginKey;
    public $mailQueue;
    
    public $reseller;
    public $package;
    public $transfer;

    public $user;
    public $backup;
    public $database;
    public $ftpAccount;
    public $phpConfig;
    
    public $domainPtr;
    public $subdomain;
    public $redirect;

    public $email;
    public $forwarder;
    public $responder;
    public $vacation;

    public $directory;
    public $file;
    
    
    /**
     * Creates a new DirectAdmin instance
     * @param string  $host
     * @param integer $port
     * @param string  $username
     * @param string  $password
     * @param string  $subuser  Optional.
     * @param string  $domain   Optional.
     */
    public function __construct(string $host, int $port, string $username, string $password, string $subuser = "", string $domain = "") {
        $this->adapter    = new Adapter($host, $port, $username, $subuser, $password, $domain);
        
        $this->service    = new Admin\Service($this->adapter);
        $this->loginKey   = new Admin\LoginKey($this->adapter);
        $this->mailQueue  = new Admin\MailQueue($this->adapter);
        
        $this->reseller   = new Reseller\Reseller($this->adapter);
        $this->package    = new Reseller\Package($this->adapter);
        $this->transfer   = new Reseller\Transfer($this->adapter);
        
        $this->user       = new User\User($this->adapter);
        $this->backup     = new User\Backup($this->adapter);
        $this->database   = new User\Database($this->adapter);
        $this->ftpAccount = new User\FTPAccount($this->adapter);
        $this->phpConfig  = new User\PHPConfig($this->adapter);
        
        $this->domainPtr  = new Domain\DomainPtr($this->adapter);
        $this->subdomain  = new Domain\Subdomain($this->adapter);
        $this->redirect   = new Domain\Redirect($this->adapter);

        $this->email      = new Email\Email($this->adapter);
        $this->forwarder  = new Email\Forwarder($this->adapter);
        $this->responder  = new Email\Responder($this->adapter);
        $this->vacation   = new Email\Vacation($this->adapter);
        
        $this->directory  = new File\Directory($this->adapter);
        $this->file       = new File\File($this->adapter);
    }
    
    
    
    /**
     * Returns the Server IP
     * @return string
     */
    public function getHost(): string {
        return $this->adapter->getHost();
    }
    
    /**
     * Returns the Server Port
     * @return integer
     */
    public function getPort(): int {
        return $this->adapter->getPort();
    }

    /**
     * Sets the Subuser and Domain for the Adapter
     * @param string $subuser
     * @param string $domain
     * @return void
     */
    public function setUser(string $subuser, string $domain): void {
        $this->adapter->setUser($subuser, $domain);
    }
}
