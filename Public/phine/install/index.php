<?php
require_once __DIR__ . '/../../../LoadPhine.php';
if (!session_id())
{
    session_start();
}
use Phine\Framework\System\IO\File;
use Phine\Framework\System\Str;
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Content;
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Page;

/**
 * The install util start page
 */
class Index extends Content
{
    /**
     * The password
     * @var string
     */
    private $password;
    
    /**
     * The password salt
     * @var string
     */
    private $salt;
    
    /**
     * True if it is the first run
     * @var boolean
     */
    protected $firstRun;
    
    function __construct()
    {
        if ($this->IsAllowed())
        {
            $this->SetAllowed(false);
        }
        $parts = parse_ini_file(__DIR__ . '/config.ini');
        $this->password = isset($parts['password']) ? $parts['password']  : '';
        $this->salt = isset($parts['salt']) ? $parts['salt']  : '';
        $this->firstRun = $this->password == '';
        parent::__construct();
    }
    
    protected function HandlePost()
    {
        $password = $this->Value('password');
        if ($this->firstRun)
        {
            if ($this->CheckFirstRun($password))
            {
                $this->SaveFirstRun($password);
            }
        }
        else if ($this->Check($password))
        {
            $this->AllowAndRedirect();
        }
    }
    
    private function AllowAndRedirect()
    {
        $this->SetAllowed(true);
        $this->GotoNext();
    }
    
    private function SaveFirstRun($password)
    {
        $salt = Str::Start(md5(uniqid(rand(100, 999), true)), 16);
        $encPassword = sha1($salt . $password);
        $text = 'password=' . $encPassword . "\r\n" .
                'salt=' . $salt;
        
        File::CreateWithText(__DIR__ . '/config.ini', $text);
        $this->AllowAndRedirect();
    }
    
    private function CheckFirstRun($password)
    {
        if (strlen($password) < 6)
        {
            $this->SetError('password', 'Password must contain six characters at least');
        }
        return !$this->HasErrors();
    }
    
    private function Check($password)
    {
        if (sha1($this->salt . $password) != $this->password)
        {
            $this->SetError('password', 'Incorrect password given');
            
        }
        return !$this->HasErrors();
    }
    
}

$page = new Page(new Index());
echo $page->Render();