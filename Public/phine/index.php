<?php
require_once '../../LoadPhine.php';

use Phine\Bundles\Core\Logic\Routing\BackendRouter;
use Phine\Bundles\Core\Logic\Util\ClassFinder;
use Phine\Framework\Wording\Worder;
use Phine\Framework\Localization\PhpTranslator;
use Phine\Bundles\Core\Modules\Backend\SideNav;
use Phine\Bundles\Core\Logic\Module\BackendModule;
use Phine\Bundles\Core\Logic\Access\Backend\UserGuard;
use Phine\Framework\System\Http\Response;
use Phine\Framework\System\Http\Request;
use Phine\Bundles\Core\Modules\Backend\Overview;
use Phine\Bundles\Core\Logic\Bundle\BundleManifest;

session_start();
class Index
{
    /**
     *
     * @var BackendModule
     */
    protected $module;
    
    /**
     * The side navigation
     * @var SideNav
     */
    protected $sideNav;
    
    /**
     * The language code
     * @var string
     */
    protected $language;
    /**
     * The user guard
     * @var UserGuard
     */
    protected $guard;
    function __construct()
    {
        if (!Request::GetData('module'))
        {
            Response::Redirect(BackendRouter::ModuleUrl(new Overview()));
        }
        $this->InitGuard();
        $this->InitTranslator();
        $this->InitBundles();
        $this->InitModule();
        $this->sideNav = new SideNav();
        $this->HandleLogout();
    }
    
    private function InitBundles()
    {
        BundleManifest::LoadInstalledToBackend();
    }
    
    private function HandleLogout()
    {
        if (Request::IsPost() && Request::PostData('logout'))
        {
            $this->guard->Accessor()->Undefine();
            Response::Redirect('login.php');
        }
    }
    
    private function InitGuard()
    {
        $this->guard = new UserGuard();
        if ($this->guard->Accessor()->IsUndefined())
        {
            Response::Redirect('login.php?returnUrl=' . urlencode(Request::Uri()));
        }
    }
    
    private function InitTranslator()
    {
        $translator = PhpTranslator::Singleton();
        $this->language = $this->guard->GetUser()->GetLanguage()->GetCode();
        $translator->SetLanguage($this->language);
        Worder::SetDefaultRealizer($translator);
    }
    
    private function InitModule()
    {
        $this->module = BackendRouter::UrlModule();
        if (!$this->module)
        {
            Response::Redirect(BackendRouter::ModuleUrl(new Overview()));
        }
    }
    function Render()
    {
        ob_start();
        require __DIR__ . '/index.phtml';
        return ob_get_clean();
    }
}


$index = new Index();
echo $index->Render();

