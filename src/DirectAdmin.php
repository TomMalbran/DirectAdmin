<?php
namespace DirectAdmin;

/**
 * The DirectAdmin API
 */
class DirectAdmin {

    private Context $context;
    public string   $id;

    public Admin\Service   $service;
    public Admin\LoginKey  $loginKey;
    public Admin\MailQueue $mailQueue;

    public Reseller\Reseller $reseller;
    public Reseller\User     $user;
    public Reseller\Package  $package;
    public Reseller\Transfer $transfer;

    public User\Account    $account;
    public User\Backup     $backup;
    public User\Database   $database;
    public User\FTPAccount $ftpAccount;
    public User\CronJob    $cronJob;
    public User\PHPConfig  $phpConfig;

    public Domain\DomainPtr $domainPtr;
    public Domain\Subdomain $subdomain;
    public Domain\Redirect  $redirect;

    public Email\Email     $email;
    public Email\Forwarder $forwarder;
    public Email\Responder $responder;
    public Email\Vacation  $vacation;

    public File\Directory $directory;
    public File\File      $file;


    /**
     * Creates a new DirectAdmin instance
     * @param string  $host
     * @param integer $port
     * @param string  $username
     * @param string  $password
     * @param string  $ip       Optional.
     */
    public function __construct(string $host, int $port, string $username, string $password, string $ip = "") {
        $this->context    = new Context($host, $port, $username, $password, $ip);

        $this->service    = new Admin\Service($this->context);
        $this->loginKey   = new Admin\LoginKey($this->context);
        $this->mailQueue  = new Admin\MailQueue($this->context);

        $this->reseller   = new Reseller\Reseller($this->context);
        $this->user       = new Reseller\User($this->context);
        $this->package    = new Reseller\Package($this->context);
        $this->transfer   = new Reseller\Transfer($this->context);

        $this->account    = new User\Account($this->context);
        $this->backup     = new User\Backup($this->context);
        $this->database   = new User\Database($this->context);
        $this->ftpAccount = new User\FTPAccount($this->context);
        $this->cronJob    = new User\CronJob($this->context);
        $this->phpConfig  = new User\PHPConfig($this->context);

        $this->domainPtr  = new Domain\DomainPtr($this->context);
        $this->subdomain  = new Domain\Subdomain($this->context);
        $this->redirect   = new Domain\Redirect($this->context);

        $this->email      = new Email\Email($this->context);
        $this->forwarder  = new Email\Forwarder($this->context);
        $this->responder  = new Email\Responder($this->context);
        $this->vacation   = new Email\Vacation($this->context);

        $this->directory  = new File\Directory($this->context);
        $this->file       = new File\File($this->context);
    }



    /**
     * Returns the Server Host
     * @return string
     */
    public function getHost(): string {
        return $this->context->host;
    }

    /**
     * Returns the Server Port
     * @return integer
     */
    public function getPort(): int {
        return $this->context->port;
    }

    /**
     * Returns the Server IP
     * @return string
     */
    public function getIP(): string {
        return $this->context->ip;
    }

    /**
     * Returns the Context Reseller
     * @return string
     */
    public function getReseller(): string {
        return $this->context->reseller;
    }

    /**
     * Returns the Context User
     * @return string
     */
    public function getUser(): string {
        return $this->context->user;
    }

    /**
     * Returns the Context Domain
     * @return string
     */
    public function getDomain(): string {
        return $this->context->domain;
    }

    /**
     * Returns if Context Domain is Delegated
     * @return string
     */
    public function isDelegated(): string {
        return $this->context->isDelegated;
    }

    /**
     * Returns the Public Path
     * @param string $path Optional.
     * @return string
     */
    public function getPublicPath(string $path = ""): string {
        return $this->context->getPublicPath($path);
    }



    /**
     * Sets the External ID
     * @param integer $id
     * @return DirectAdmin
     */
    public function setExternalID(int $id): DirectAdmin {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the Reseller for the Context
     * @param string $reseller
     * @return DirectAdmin
     */
    public function setReseller(string $reseller): DirectAdmin {
        $this->context->setReseller($reseller);
        return $this;
    }

    /**
     * Sets the User and Domain for the Context
     * @param string  $user
     * @param string  $domain
     * @param boolean $isDelegated Optional.
     * @return DirectAdmin
     */
    public function setUser(string $user, string $domain, bool $isDelegated = false): DirectAdmin {
        $this->context->setUser($user, $domain, $isDelegated);
        return $this;
    }
}
