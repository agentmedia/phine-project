<?php
namespace App\Phine\Project;
use Phine\Framework\System\IO\Folder;
use Phine\Framework\System\IO\Path;
use Phine\Framework\System\IO\File;
use Composer\Script\Event;
use Composer\Install\PackageEvent;
use Composer\Composer;
/**
 * Bootstraps the project
 */
class Bootstrap
{
    public static function onUpdate(Event $event) {
        self::updateBundlesCache($event->getComposer());
    }

    public static function onPackageInstall(PackageEvent $event) {
       self::updateBundlesCache($event->getComposer());
    }
    
    private static function updateBundlesCache(Composer $composer)
    {
        // we get ALL installed packages
        $packages = $composer->getRepositoryManager()
                ->getLocalRepository()->getPackages();
        $installationManager = $composer->getInstallationManager();
        $bundleInfos = array();
        foreach ($packages as $package) {
            if ($package->getType() === 'phine-bundle') {
                self::addPackageBundleInfo($installationManager->getInstallPath($package), $bundleInfos);
            }
        }
        self::writeBundleInfos($bundleInfos);
    }
    
    private static function bundleInfosFile() {
        return __DIR__ . '/../Cache/Bundles/packages.json';
    }
    
    private static function writeBundleInfos(array $bundleInfos)
    {
        File::CreateWithText(self::bundleInfosFile(), json_encode($bundleInfos));
    }
    
    private static function addPackageBundleInfo($packagePath, array& $bundleInfos) {
        $subFolders = Folder::GetSubFolders($packagePath);
        if (count($subFolders) !== 1) {
            throw new \LogicException('Invalid Phine bundle found. It must contain a single directory with the name of the bundle)');
        }
        $name = $subFolders[0];
        if (isset($bundleInfos[$name])) {
            throw new \LogicException("Ambiguous Phine bundle name '$name'");
        }
        $bundleInfos[$name] = Path::Combine($packagePath, $name);
    }
}