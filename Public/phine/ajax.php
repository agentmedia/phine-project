<?php
require_once '../../LoadPhine.php';
use Phine\Bundles\Core\Logic\Routing\BackendRouter;
use Phine\Bundles\Core\Logic\Util\ClassFinder;
use Phine\Framework\Wording\Worder;
use Phine\Framework\Localization\PhpTranslator;
use Phine\Bundles\Core\Logic\Module\ModuleBase;
use Phine\Framework\System\Http\Request;
use Phine\Bundles\Core\Logic\Access\Backend\UserGuard;
use Phine\Database\Core\User;

session_start();
/**
 * The rendering 
 */
class AjaxIndex
{
    /**
     * The module
     * @var ModuleBase
     */
    protected $module;
    
    /**
     * The logged in user
     * @var User
     */
    protected $user;
    function __construct()
    {
        $guard = new UserGuard();
        $this->user = $guard->GetUser();
        if (!$this->user || $this->user->GetID() != Request::GetData('__backendUser'))
        {
            throw new \Exception('Security breach in ajax.php: no matching backend user logged in');
        }
        $this->InitTranslator();
        $this->InitModule();
        
    }
    
    private function InitTranslator()
    {
        $translator = PhpTranslator::Singleton();
        $translator->SetLanguage($this->user->GetLanguage()->GetCode());
        Worder::SetDefaultRealizer($translator);
    }
    
    private function InitModule()
    {
        $this->module = BackendRouter::UrlModule();
        if ($this->module)
        {
            $manifest = ClassFinder::Manifest($this->module->MyBundle());
            $manifest->LoadToBackend();
        }
    }
    
    
    /**
     * Returns the module content
     * @return string
     */
    function Render()
    {
        return $this->module->Render();
    }
}


$index = new AjaxIndex();
echo $index->Render();
