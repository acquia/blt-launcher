# BLT Launcher

A small wrapper around BLT for your global $PATH.

## Why?

BLT must be installed on a per-project basis via Composer (`composer require acquia/blt`). This makes BLT available to your project by placing it at `vendor/bin/blt`.

However, it is inconvenient to type `vendor/bin/blt` in order to execute BLT commands.  By installing the BLT launcher globally on your local machine, you can simply type `blt` on the command line, and the launcher will find and execute the project specific version of BLT located in your project's `vendor` directory.

## Installation - Phar

1. Download latest stable release via CLI (code below) or browse to https://github.com/acquia/blt-launcher/releases/latest.

    OSX:
    ```Shell
    curl -OL https://github.com/acquia/blt-launcher/releases/latest/download/blt.phar
    ```

    Linux:

    ```Shell
    wget -O blt.phar https://github.com/acquia/blt-launcher/releases/latest/download/blt.phar
    ```
1. Make downloaded file executable: `chmod +x blt.phar`
1. Move blt.phar to a location listed in your `$PATH`, rename to `blt`:

    ```Shell
    sudo mv blt.phar /usr/local/bin/blt
    ```

1. Windows users: create a blt.bat file in the same folder as blt.phar with the following lines. This gets around the problem where Windows does not know that .phar files are associated with `php`:

    ``` Bat
    @echo off
    php "%~dp0\blt.phar" %*
    ```

## Update

The BLT Launcher Phar is able to self update to the latest release.

```Shell
    blt self-update
```

## Alternatives

If you only have one codebase on your system (typical with VMs, Docker, etc,), you should add `/path/to/vendor/bin` to your $PATH. BLT is smart enough to find the PROJECT_ROOT and DRUPAL_ROOT when it is run from the bin directory.

## Xdebug compatibility

BLT Launcher, like Composer automatically disables Xdebug by default. This improves performance substantially. You may override this feature by setting an environment variable. ``BLT_ALLOW_XDEBUG=1 blt [command]``

## Credit / Kudos

Many thanks to the Drush team for their excellent [Drush Launcher](https://github.com/drush-ops/drush-launcher), upon which BLT Launcher is based.
