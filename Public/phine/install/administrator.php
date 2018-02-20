<?php
require_once __DIR__ . '/../../../LoadPhine.php';
if (!session_id())
{
    session_start();
}
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Page;
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Content;
use Phine\Framework\Validation\DatabaseCount;
use Phine\Framework\Validation\PhpFilter;
use Phine\Framework\System\Str;
use App\Phine\Database\Core\Language;
use App\Phine\Database\Core\User;
use App\Phine\Database\Access;

class Administrator extends Content
{
    /**
     * The form fields
     * @var string[]
     */
    protected $fields;
    
    /**
     * The languages available in backend
     * @var Language[]
     */
    protected $languages;
    public function __construct()
    {
        $adminCnt = User::Schema()->CountByIsAdmin(false, true);
        if ($adminCnt > 0)
        {
            $this->GotoNext();
        }
        $sql = Access::SqlBuilder();
        $tbl = Language::Schema()->Table();
        $where = $sql->Equals($tbl->Field('IsBackendTranslated'), $sql->Value(true));
        $orderBy = $sql->OrderList($sql->OrderAsc($tbl->Field('Name')));
        $this->languages = Language::Schema()->Fetch(false, $where, $orderBy);
        
        $this->fields = array('name', 'password', 'language', 'email', 'firstname', 'lastname');
        parent::__construct();
    }
    protected function HandlePost()
    {
        if ($this->Check())
        {
            $this->Save();
            $this->GotoNext();
        }
    }
    
    private function Check()
    {
        foreach ($this->fields as $field)
        {
            if (!$this->Value($field))
            {
                $this->SetError($field, 'This field is required');
            }
        }
        
        $this->CheckName();
        $this->CheckPassword();
        $this->CheckEMail();
        
        return !$this->HasErrors();
    }
    
    private function CheckPassword()
    {
        if (!$this->HasError('password') && strlen($this->Value('password')) < 6)
        {
            $this->SetError('password', 'Password must have at least six characters');
        }
    }
    
    private function CheckName()
    {
        if (!$this->HasError('name'))
        {
            $validator = DatabaseCount::UniqueFieldAnd(new User(), 'Name');
            if (!$validator->Check($this->Value('name')))
            {
                $this->SetError('name', 'This name is already in use');
            }
        }
    }
   
    private function CheckEMail()
    {
        if (!$this->HasError('email'))
        {
            $validator = PhpFilter::EMail();
            if (!$validator->Check($this->Value('email')))
            {
                $this->SetError('email', 'This is not a valid e-mail address');
            }
        }
    }



    private function Save()
    {
        $user = new User();
        $user->SetName($this->Value('name'));
        $user->SetFirstName($this->Value('firstname'));
        $user->SetLastName($this->Value('lastname'));
        $user->SetLanguage(Language::Schema()->ByID($this->Value('language')));
        $user->SetEMail($this->Value('email'));
        $password = $this->Value('password');
        
        $salt = Str::Start(md5(uniqid(microtime())), 8);
        $pwHash = hash('sha256', $password . $salt);
        $user->SetPassword($pwHash);
        $user->SetPasswordSalt($salt);
        $user->SetIsAdmin(true);
        $user->Save();
    }
    
}

$page = new Page(new Administrator());
echo $page->Render();