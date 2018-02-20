<?php
namespace App\Phine\Project;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
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
        file_put_contents(self::bundleInfosFile(), json_encode($bundleInfos));
    }
    private static function getSubFolders($dir) {
        $handle = opendir($dir);
        $result = array();
        
        $file = readdir($handle);
        while ($file !== false)
        {   
            if ($file != '.' && $file != '..' && is_dir($file)) {
                $result[] = $file;
            }
            $file = readdir($handle);
        }
        closedir($handle);
        return $result;
    }
    private static function addPackageBundleInfo($packagePath, array& $bundleInfos) {
        $subFolders = self::getSubFolders($packagePath);
        if (count($subFolders) !== 1) {
            throw new \LogicException('Invalid Phine bundle found. It must contain a single directory below ' . $packagePath . ' with the name of the bundle)');
        }
        $name = $subFolders[0];
        if (isset($bundleInfos[$name])) {
            throw new \LogicException("Ambiguous Phine bundle name '$name'");
        }
        $bundleInfos[$name] = rtrim($packagePath, '/\\') . '/' . ltrim($name, '/\\');
    }
}