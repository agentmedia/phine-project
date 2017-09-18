<?php
require_once '../../LoadPhine.php';
use Phine\Bundles\Core\Logic\Routing\BackendRouter;
use Phine\Framework\Wording\Worder;
use Phine\Framework\Localization\PhpTranslator;
use Phine\Bundles\Core\Logic\Access\Backend\UserGuard;
use Phine\Framework\System\Http\Response;
use Phine\Bundles\Core\Modules\Backend\Overview;
use Phine\Bundles\Core\Logic\Util\PathUtil;
use Phine\Framework\System\Http\Request;
session_start();
class Login
{
    
    /**
     * The user guard
     * @var UserGuard
     */
    protected $guard;
    
    /**
     * An error string
     * @var string
     */
    protected $error;
    function __construct()
    {
        $manifest = new Phine\Bundles\Core\Manifest();
        $manifest->LoadToBackend();
        $this->InitTranslator();
        $this->InitGuard();
        if (Request::IsPost())
        {
            $this->HandlePost();
        }
    }
    
    private function HandlePost()
    {
        $data = Request::PostArray();
        if ($this->guard->Accessor()->Verify($data))
        {
            if (Request::GetData('returnUrl'))
            {
                Response::Redirect(Request::GetData('returnUrl'));
            }
            else
            {
                Response::Redirect(BackendRouter::ModuleUrl(new Overview()));
            }
        }
        else
        {
            $this->error = Trans('Core.Login.Failed');
        }
    }
    
    private function InitGuard()
    {
        $this->guard = new UserGuard();
        if (!$this->guard->Accessor()->IsUndefined())
        {
            Response::Redirect(BackendRouter::ModuleUrl(new Overview()));
        }
    }
    
    private function InitTranslator()
    {
        $translator = PhpTranslator::Singleton();
        //TODO: Get language by some kind of general settings
        require_once  PathUtil::BackendBundleTranslationFile('Core', 'en');
        $translator->SetLanguage('en');
        Worder::SetDefaultRealizer($translator);
    }
    
    
    function Render()
    {
        ob_start();
        require __DIR__ . '/login.phtml';
        return ob_get_clean();
    }
}


$login = new Login();
echo $login->Render();



