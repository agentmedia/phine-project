<?php
require_once __DIR__ . '/../../../LoadPhine.php';
if (!session_id())
{
    session_start();
}
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Content;
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Page;
use Phine\Framework\Database\Mysqli\Connection;
use Phine\Bundles\Core\Logic\Installation\Installer;
use Phine\Framework\System\IO\File;
use Phine\Bundles\Core\Logic\Util\PathUtil;
use App\Phine\Database\Core\InstalledBundle;
use Phine\Bundles\Core\Logic\Util\ClassFinder;
use Phine\Framework\System\IO\Path;


/**
 * The connection form in the install util
 */
class Database extends Content
{
    /**
     * The database connection
     * @var Connection
     */
    protected $connection = null;
    
    /**
     *
     * @var Connection
     */
    private $newConnection = null;
    /**
     * The field names
     * @var string[]
     */
    protected $fields;
    
    /**
     * Bundle names as keys, versions as
     * @var array
     */
    private $installedBundles;
    
    /**
     * True if database is out of sync with code
     * @var bool
     */
    protected $needsUpdate = false;
    
    function __construct()
    {
        $this->fields =array('server', 'database', 'port', 'socket', 'username', 'password');
        $this->installedBundles = array();
        $this->RetrieveInstalledDB();
        $this->CheckNeedsUpdate();
        parent::__construct();
    }
    
    private function CheckNeedsUpdate()
    {
        if (!$this->connection)
        {
            return;
        }
        $bundles = PathUtil::Bundles();
        foreach ($bundles as $bundle)
        {
            $manifest = ClassFinder::Manifest($bundle); 
            $installed = InstalledBundle::Schema()->ByBundle($bundle);
            if (!$installed || version_compare($installed->GetVersion(), $manifest->Version()) < 0)
            {
                $this->needsUpdate = true;
            }
        }
    }
    private function RetrieveInstalledDB()
    {
        try
        {
            if (!File::Exists(Path::Combine(PHINE_PATH, 'App/Phine/Database/Access.php')))
            {
                return;
            }
            if (!class_exists('App\Phine\Database\Access'))
            {
                return;
            }
        }
        catch (\Exception $exc)
        {
            return;
        }
        try
        {
            $this->connection = App\Phine\Database\Access::Connection();
        }
        catch (\Exception $ex)
        {
            $this->SetError('-overall-', 'Database connection found, but credentials are invalid');
            $this->connection = null;
        }
    }
   
  
    protected function HandlePost()
    {
        $statusFile = __DIR__ . '/progress.json';
        if ($this->Check())
        {
            set_time_limit(0);
            $installer = new Installer($this->newConnection, $statusFile);
            $success = $installer->RunSql();
            if ($success)
            {
                $installer->CreateDBObjects();
            }
            foreach ($installer->FailedBundles() as $bundle=>$message)
            {
                $this->AddWarning("Bundle '$bundle' not properly installed; it will not work. Error message: " . $message);
            }
            if ($success)
            {
                File::CreateWithText($statusFile, '');
                $this->GotoNext();
            }
        }
    }
    
    private function IsNewConnection()
    {
        if (!$this->connection)
        {
            return true;
        }
        foreach ($this->fields as $field)
        {
            if ($this->Value($field) != $this->DefaultValue($field))
            {
                return true;
            }
        }
        return false;
    }
    
    protected function DefaultValue($field)
    {
        if (!$this->connection)
        {
            return $this->EmptyDefaultValue($field);
        }
        switch ($field)
        {
            case 'server':
                return $this->connection->Server();
                
            case 'database':
                return $this->connection->DatabaseName();
                
            case 'port':
                return $this->connection->Port();
                
            case 'socket':
                return $this->connection->Socket();
                
            case 'username':
                return $this->connection->Username();
                
            case 'password':
                return $this->connection->Password();
        }
        return parent::DefaultValue($field);
    }
    
    private function EmptyDefaultValue($field)
    {
        switch ($field)
        {
            case 'port':
                return '3306';
                
            case 'server':
                return 'localhost';
        }
        return parent::DefaultValue($field);
    }
    
    
    function Check()
    {
        foreach ($this->fields as $field)
        {
            if ($field != 'socket' && !$this->Value($field))
            {
                $this->SetError($field, 'This field is required');
            }
        }
        if ($this->HasErrors())
        {
            return false;
        }
        try
        {
            if ($this->IsNewConnection())
            {
                $this->newConnection = new Connection($this->Value('server'), 
                        $this->Value('username'), $this->Value('password'), 
                        $this->Value('database'), $this->Value('port'), 
                        $this->Value('socket'));
            }
            else
            {
                $this->newConnection = $this->connection;
            }
        }
        catch (\Exception $exc)
        {
            $this->SetError('-overall-', 'Could not connect to database: ' . $exc->getMessage());
        }
        return !$this->HasErrors();
    }
    
}

$page = new Page(new Database());
echo $page->Render();

