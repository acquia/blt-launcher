<?php

use Composer\XdebugHandler\XdebugHandler;
use DrupalFinder\DrupalFinder;
use Humbug\SelfUpdate\Updater;
use Webmozart\PathUtil\Path;

set_time_limit(0);

$autoloaders = [
  __DIR__ . '/../../../autoload.php',
  __DIR__ . '/../vendor/autoload.php'
];

foreach ($autoloaders as $file) {
  if (file_exists($file)) {
    $autoloader = $file;
    break;
  }
}

if (isset($autoloader)) {
  require_once $autoloader;
}
else {
  echo 'You must set up the blt-launcher dependencies using `composer install`' . PHP_EOL;
  exit(1);
}
$BLT_LAUNCHER_VERSION = '@git-version@';

$ROOT = FALSE;
$DEBUG = FALSE;
$VAR = FALSE;
$VERSION = FALSE;
$VERSION_LAUNCHER = FALSE;
$BLT_VERSION = NULL;
$SELF_UPDATE = FALSE;

foreach ($_SERVER['argv'] as $arg) {
  // If a variable to set was indicated on the
  // previous iteration, then set the value of
  // the named variable (e.g. "ROOT") to "$arg".
  if ($VAR) {
    $$VAR = "$arg";
    $VAR = FALSE;
  }
  else {
    switch ($arg) {
      case "-r":
        $VAR = "ROOT";
        break;
      case "--debug":
        $DEBUG = TRUE;
        break;
      case "--version":
        $VERSION = TRUE;
        break;
      case "--blt-launcher-version":
        $VERSION_LAUNCHER = TRUE;
        break;
      case "self-update":
        $SELF_UPDATE = TRUE;
        break;
    }
    if (substr($arg, 0, 7) == "--root=") {
      $ROOT = substr($arg, 7);
    }
  }
}

if ($ROOT === FALSE) {
  $ROOT = getcwd();
}
else {
  $ROOT = Path::canonicalize($ROOT);
}

$drupalFinder = new DrupalFinder();

if ($VERSION || $VERSION_LAUNCHER || $DEBUG || $SELF_UPDATE) {
  echo "BLT Launcher Version: {$BLT_LAUNCHER_VERSION}" . PHP_EOL;
}

if ($VERSION_LAUNCHER) {
  exit(0);
}

if ($SELF_UPDATE) {
  if ($BLT_LAUNCHER_VERSION === '@' . 'git-version' . '@') {
    echo "Automatic update not supported.\n";
    exit(1);
  }
  $updater = new Updater(NULL, FALSE);
  $updater->setStrategy(Updater::STRATEGY_GITHUB);
  $updater->getStrategy()->setPackageName('acquia/blt-launcher');
  $updater->getStrategy()->setPharName('blt.phar');
  $updater->getStrategy()->setCurrentLocalVersion($BLT_LAUNCHER_VERSION);
  try {
    $result = $updater->update();
    echo $result ? "Updated!\n" : "No update needed!\n";
    exit(0);
  } catch (\Exception $e) {
    echo "Automatic update failed, please download the latest version from https://github.com/acquia/blt-launcher/releases\n";
    exit(1);
  }
}

if ($DEBUG) {
  echo "ROOT: " . $ROOT . PHP_EOL;
}

if ($drupalFinder->locateRoot($ROOT)) {
  $drupalRoot = $drupalFinder->getDrupalRoot();

  $xdebug = new XdebugHandler('blt', '--ansi');
  $xdebug->check();
  unset($xdebug);

  if ($DEBUG) {
    echo "DRUPAL ROOT: " . $drupalRoot . PHP_EOL;
    echo "COMPOSER ROOT: " . $drupalFinder->getComposerRoot() . PHP_EOL;
    echo "VENDOR ROOT: " . $drupalFinder->getVendorDir() . PHP_EOL;
  }

  $_SERVER['argv'][] = '-D';
  $_SERVER['argv'][] = 'disable-targets.blt.shell-alias.init=true';
  exit(require $drupalFinder->getVendorDir() . '/acquia/blt/bin/blt-robo.php');
}

echo 'The BLT launcher could not find a Drupal site to operate on. Please do *one* of the following:' . PHP_EOL;
echo '  - Navigate to any where within your Drupal project and try again.' . PHP_EOL;
echo '  - Add --root=/path/to/drupal so BLT knows where your site is located.' . PHP_EOL;
exit(1);
