<?php

/**
 * @file
 * Contains \DrupalProject\composer\ScriptHandler.
 */

namespace DrupalProject\composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ScriptHandler
{

  protected static function getDrupalRoot($project_root)
  {
    return $project_root .  '/web';
  }

  public static function createRequiredFiles(Event $event)
  {
    $fs = new Filesystem();

    $drupal_root = static::getDrupalRoot(getcwd());
    $project_root = getcwd();

    $dirs = [
      'modules',
      'profiles',
      'themes',
    ];

    // Required for unit testing
    foreach ($dirs as $dir) {
      if (!$fs->exists($project_root . '/'. $dir)) {
        $fs->mkdir($project_root . '/'. $dir);
        $fs->touch($project_root . '/'. $dir . '/.gitkeep');
      }
    }

    // Create the files directory with chmod 0777
    if (!$fs->exists($project_root . '/sites/default/files')) {
      $oldmask = umask(0);
      $fs->mkdir($project_root . '/sites/default/files', 0777);
      umask($oldmask);
      $event->getIO()->write("Create a sites/default/files directory with chmod 0777");
    }

    // Removing some files we don't want to exist.
    $fs->remove($drupal_root . '/sites/default/settings.php');
    $fs->remove($drupal_root . '/sites/default/settings.local.php');

    // Link files from custom to their destinations within the drupal install.
    $fs->symlink('../../custom/modules', $drupal_root . '/modules/custom');
    $fs->symlink('../../custom/profiles', $drupal_root . '/profiles/custom');
    $fs->symlink('../../custom/themes', $drupal_root . '/themes/custom');

    // Drush site-install sometimes renders things in the site folders unwriteable.
    // Conversely it sometimes seems to need to write to them.
    $sites_default =  "{$drupal_root}/sites/default";
    $settings = "{$sites_default}/settings.php";

    // Working around drush site-install problems.
    $sites_default_perm = fileperms($drupal_root . '/sites/default');
    $settings_perm = file_exists($settings) ? fileperms($settings) : FALSE;

    $fs->chmod($drupal_root . '/sites/default', 755);
    if (file_exists($drupal_root . '/sites/default/settings.php')) {
      $fs->chmod($drupal_root . '/sites/default/settings.php', 755);
    }

    // Our local settings file should get linked as well.
    $fs->symlink('../../../custom/site/settings.local.php', $drupal_root . '/sites/default/settings.local.php');

    // We copy this file out because drush site-installs like to write over it.
    $fs->copy($project_root . '/custom/site/settings.php', $drupal_root . '/sites/default/settings.php');

    // We should set these back to whatever they were before.
    if ($settings_perm) {
      $fs->chmod($drupal_root . '/sites/default/settings.php', $settings_perm);
    }
    $fs->chmod($drupal_root . '/sites/default', $sites_default_perm);

  }

  // This is called by the QuickSilver deploy hook to convert from
  // a 'lean' repository to a 'fat' repository. This should only be
  // called when using this repository as a custom upstream, and
  // updating it with `terminus composer <site>.<env> update`. This
  // is not used in the GitHub PR workflow.
  public static function prepareForPantheon()
  {
    // Get rid of any .git directories that Composer may have added.
    // n.b. Ideally, there are none of these, as removing them may
    // impair Composer's ability to update them later. However, leaving
    // them in place prevents us from pushing to Pantheon.
    $dirsToDelete = [];
    $finder = new Finder();
    foreach (
      $finder
        ->directories()
        ->in(getcwd())
        ->ignoreDotFiles(false)
        ->ignoreVCS(false)
        ->depth('> 0')
        ->name('.git')
      as $dir) {
      $dirsToDelete[] = $dir;
    }
    $fs = new Filesystem();
    $fs->remove($dirsToDelete);

    // Fix up .gitignore: remove everything above the "::: cut :::" line
    $gitignoreFile = getcwd() . '/.gitignore';
    $gitignoreContents = file_get_contents($gitignoreFile);
    $gitignoreContents = preg_replace('/.*::: cut :::*/s', '', $gitignoreContents);
    file_put_contents($gitignoreFile, $gitignoreContents);
  }
}
