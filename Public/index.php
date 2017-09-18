<?php
require_once __DIR__ . '/../LoadPhine.php';
use Phine\Framework\System\Http\Request;
use Phine\Framework\System\Http\Response;
use Phine\Database\Core\Site;
use Phine\Database\Core\Page;
use Phine\Database\Access;
use Phine\Framework\System\Str;
use Phine\Bundles\Core\Logic\Rendering\PageRenderer;
use Phine\Bundles\Core\Logic\Routing\Rewriter;
use Phine\Bundles\Core\Logic\Access\Frontend\MemberGuard;
use Phine\Framework\Access\Base\Action;
use Phine\Framework\Access\Base\GrantResult;
use Phine\Database\Core\PageUrl;
use Phine\Bundles\Core\Logic\Tree\PageTreeProvider;
use Phine\Bundles\Core\Logic\Routing\FrontendRouter;
use Phine\Bundles\Core\Logic\Translation\ContentTranslator;
use Phine\Framework\Wording\Worder;
use Phine\Bundles\Core\Logic\Bundle\BundleManifest;
use Phine\Bundles\Core\Logic\Sitemap\Generator;
use Phine\Bundles\BuiltIn\Logic\Logging\ContainerReferenceResolver;
use Phine\Bundles\Core\Logic\DBEnums\PageType;

/**
 * The responder for the http request
 */
class CmsResponder
{
    /**
     * The website as calculated from the current request
     * @var Site
     */
    private $site = null;
    
    /**
     * The requested, relative page url
     * @var type 
     */
    private $pageUrl;
    function __construct()
    {
       if (!session_id() && !Request::GetData('__noSession'))
       {
           session_start();
       }
    }
    /**
     * 
     * @return Site
     */
    private function FindSite()
    {
        $sites = Site::Schema()->Fetch();
        foreach ($sites as $site)
        {
            if (Str::StartsWith($site->GetUrl(), Request::FullUrl(), true))
            {
                $this->site = $site;
                return;
            }
        }
    }
    
    private function IsSitemapUrl() 
    {
        return $this->pageUrl == 'sitemap.xml';
    }
    /**
     * Finds the page
     * @param Site $site
     * @return Page
     * 
     */
    private function FindPage(Site $site)
    {
        $this->CalcPageUrl($site);
        if ($this->IsSitemapUrl()) {
            return null;
        }
        $sql = Access::SqlBuilder();
        $tblPage = Page::Schema()->Table();
        $where = $sql->Equals($tblPage->Field('Site'), 
                    $sql->Value($site->GetID()))->
                And_($sql->Equals($tblPage->Field('Url'), $sql->Value($this->pageUrl)));
        
        return Page::Schema()->First($where);
    }
    
    /**
     * Calculates the url from request that shall determine the page
     * @param Site $site
     * @return string
     */
    private function CalcPageUrl(Site $site)
    {
        $requestUrl = Request::GetData(Rewriter::PAGE_URL_PARAM);
        
        if (!$requestUrl)
        {
            $pagePart = Str::Part(Request::FullUrl(), Str::Length($site->GetUrl()));
            $requestUrl = Str::TrimLeft($pagePart, '/');
            $qmIndex = strpos($requestUrl, '?');
            if ($qmIndex !== false)
            {
                $requestUrl = substr($requestUrl, 0, $qmIndex);
            }
        }
        if (!$requestUrl)
        {
            $requestUrl = 'index.html';
        }
        $this->pageUrl = $requestUrl;
    }
    
    function Render()
    {
        $this->FindSite();
        if (!$this->site || Page::Schema()->CountBySite(false, $this->site) == 0)
        {
            return 'No active Phine website is matching your request';
        }
        BundleManifest::LoadInstalledToFrontend($this->site);
        $page = $this->FindPage($this->site);
        
        if (!$page)
        {
            if ($this->IsSitemapUrl() && $this->site->GetSitemapActive()){
                return $this->RenderSitemap();
            }
            return $this->Realize404();
        }
        //todo: handle member protected pages!!!
        $this->InitTranslator();
        $result = $this->HandlePageProtection($page);
        if ($result)
        {
            return $result;
        }
        $this->HandleRedirect($page);
        $renderer = new PageRenderer();
        $renderer->SetPage($page);
        return $renderer->Render();
    }
    
    /**
     * Handles redirect type page by redirecting
     * @param Page $page The currently found page
     */
    private function HandleRedirect(Page $page)
    {
        $type = $page->GetType();
        if ($type == (string)PageType::Normal() || 
                $type == (string)PageType::NotFound())
        {
            return;
        }
        $target = $page->GetRedirectTarget();
        if (!$target)
        {
            $this->Realize404();
        }
        $targetUrl = FrontendRouter::Url($target);
        if ($type == (string)PageType::RedirectPermanent())
        {
            Response::Redirect301($targetUrl);
        }
        else if ($type == (string)PageType::RedirectTemporary())
        {
            Response::Redirect($targetUrl);
        }
    }
    
    /**
     * Redirects to the 404 page or renders a default one
     * @return string Returns an ugly page not found string if no 404 page was found
     */
    private function Realize404()
    {
        $page404 = FrontendRouter::Page404($this->site);
        if ($page404)
        {
            Response::Redirect(FrontendRouter::PageUrl($page404));
            return '';
        }
        header("HTTP/1.0 404 Not Found");
        return 'Page not found';
       
    }
    
    private function HandlePageProtection(Page $page)
    {
        $guard = new MemberGuard();
        $result = $guard->Grant(Action::Read(), $page);
        switch ($result)
        {
            case GrantResult::NoAccess():
                return $this->Realize404();
            
            case GrantResult::LoginRequired():
                Response::Redirect(FrontendRouter::PageUrl($this->LoginPage()));
                return '';
            
            default:
                return '';
        }
        
    }
    
    /**
     * Gets a login page
     * @todo get login page
     * @return Page Returns the best match for a login page
     */
    private function LoginPage()
    {
        $tree = new PageTreeProvider($this->site);
        return $tree->TopMost();
    }
    
    private function RenderSitemap()
    {
        header('Content-Type: application/xml');
        $generator = new Generator($this->site, new ContainerReferenceResolver());
        $generator->Generate();
        return $generator->GetXml();
    }
    
    private function InitTranslator()
    {
        $translator = ContentTranslator::Singleton();
        $translator->SetLanguage($this->site->GetLanguage()->GetCode());
        Worder::SetDefaultRealizer($translator);
    }
 
}
$responder = new CmsResponder();
echo $responder->Render();