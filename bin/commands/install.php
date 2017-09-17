<?php

use Pac\Console;
use Pac\Downloader;
use Pac\FileSystem;
use Pac\Process;

return function (array $command) {
    $rootDir = getcwd();
    $homeDir = Process::home() . '/.pacphp';
    $cacheDir = "$homeDir/caches";
    $jsonFile = $rootDir . '/pac.json';
    $vendorDir = $rootDir . '/php_packages';
    $autoloadFile = $vendorDir . '/__init__.php';
    $autoloadTemplate = $rootDir . '/src/__init__.php';

    if (!file_exists($jsonFile)) {
        Console::error('"pac.json" is not exists.');
        return;
    }

    if (!$homeDir && @mkdir($cacheDir, 0755, true)) {
        Console::error('"' . $cacheDir . '" can not create.');
        return;
    }

    $package = @json_decode(file_get_contents($jsonFile), JSON_OBJECT_AS_ARRAY);

    if (!is_dir($vendorDir) && !@mkdir($vendorDir, 755, true)) {
        Console::error("Dir \"$vendorDir\" can not create");
        return;
    }

    if (!empty($package['dependencies'])) {
        foreach ($package['dependencies'] as $name => $packageVersion) {
            $packageCacheDir = "$cacheDir/$name";
            $packageCacheVersionDir = $packageCacheVersionZip = null;
            $gitUrl = $gitVersion = null;
            $githubRepo = $githubVendor = $githubName = null;
            $packageVendorDir = $vendorDir . "/$name";

            if (preg_match("/^(\~|\^)\d\.\d\.\d$/", $packageVersion) > 0) {
                // Find right version
                $packageType = 'dist';
                $packageRightVersion = '1.0.0';
                $packageCacheVersionDir = "$packageCacheDir/$packageRightVersion";
            } else {
                list($gitUrl, $gitVersion) = explode('#', $packageVersion);
                $packageType = 'git';

                if (!$gitVersion) {
                    $gitVersion = 'master';
                }

                if (strpos($gitUrl, 'https://github.com/') === 0
                    || strpos($gitUrl, 'git@github.com:') === 0
                ) {
                    $packageType = 'github';
                    preg_match('/github\.com(?:\/|:)(.*?)\.git/', $gitUrl, $packageMatched);
                    $githubRepo = $packageMatched[1];
                } else if (preg_match("/^(git|ssh|http|ftp)/", $gitUrl) === 0) {
                    $packageType = 'github';
                    $githubRepo = $gitUrl;
                }

                if ($githubRepo) {
                    list($githubVendor, $githubName) = explode('/', $githubRepo);
                }

                $packageCacheVersionDir = "$packageCacheDir/$gitVersion";
                $packageCacheVersionZip = "$packageCacheDir/$gitVersion.zip";
            }

            // Download the latest
            if ($packageType === 'git') {
                if (!is_dir($packageCacheVersionDir)) {
                    Process::exec("git clone $gitUrl $packageCacheVersionDir", ['stdio' => 'pipe']);
                    list($status) = Process::exec("git checkout $gitVersion", ['stdio' => 'pipe']);
                    if ($status !== 0) {
                        Console::error("$packageVersion is not exists");
                    }
                }
            } else if ($packageType === 'github') {
                // https://github.com/composer/composer/archive/master.zip

                if (!is_dir($packageCacheVersionDir)) {
                    $downloadUrl = "https://codeload.github.com/$githubRepo/zip/$gitVersion";
                    Downloader::download($downloadUrl, $packageCacheVersionZip);

                    $zip = new ZipArchive;
                    $zip->open($packageCacheVersionZip);
                    $zip->extractTo($packageCacheDir);
                    $zip->close();

                    rename("$packageCacheDir/$githubName-$gitVersion", $packageCacheVersionDir);
                }
            } else {
                // Download
            }

            FileSystem::copy($packageCacheVersionDir, $packageVendorDir);

            // TODO: Check shasum

            // Check version

            // Check pac.json

            // Check composer.json

            // Check dependency and download

            // Resolve dependency

            // Merge dependency

            // Copy to dist

            // Build autoloader
        }
    }

    $template = file_get_contents($autoloadTemplate);
    file_put_contents($autoloadFile, str_replace('$namespace = [];', '$namespace = ' . var_export($package['namespace'], true) . ';', $template));

    Console::success('Install ok');
};
