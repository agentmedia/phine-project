<?php
require_once __DIR__ . '/../../../LoadPhine.php';
if (!session_id())
{
    session_start();
}
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Content;
use Phine\Bundles\Core\Logic\Installation\InstallUtil\Page;
class Finish extends Content
{
    protected $showWarning;
    function __construct()
    {
        $this->showWarning = $this->HasWarnings();
        parent::__construct();
        $this->SetAllowed(false);
    }
    
    protected function LoginUrl()
    {
        return '../login.php';
    }
}
$pg = new Page(new Finish());
echo $pg->Render();

