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
        self::createHtaccess();
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
    
    private static function createHtaccess()
    {
        $pubFolder = __DIR__ . '/../../../Public/';
        $htaccessFile = $pubFolder . '.htaccess';
        if (!file_exists($htaccessFile)) {
            $initialFile = $pubFolder . '.htacess.initial';
            $contents = file_get_contents($initialFile);
            $fixedLineEndings = preg_replace('~(*BSR_ANYCRLF)\R~', "\r\n", $contents);
            file_put_contents($htaccessFile, $fixedLineEndings);
        }
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
            if ($file != '.' && $file != '..' && is_dir($dir . '/' . $file)) {
                $result[] = $file;
            }
            $file = readdir($handle);
        }
        closedir($handle);
        return $result;
    }
    private static function addPackageBundleInfo($packagePath, array& $bundleInfos) {
        
        $subFolders = self::getSubFolders($packagePath . '/src');
        if (count($subFolders) !== 1) {
            throw new \LogicException('Invalid Phine bundle found. It must contain a single directory below ' . $packagePath . ' with the name of the bundle; found: ' .  var_export($subFolders, true));
        }
        $name = $subFolders[0];
        if (isset($bundleInfos[$name])) {
            throw new \LogicException("Ambiguous Phine bundle name '$name'");
        }
        $bundleInfos[$name] = rtrim($packagePath, '/\\') . '/src/' . ltrim($name, '/\\');
    }
}