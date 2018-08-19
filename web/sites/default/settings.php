<?php
/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * The settings.pantheon.php file makes some changes that affect all
 * environments that this site exists in.  Always include this file, even in
 * a local development environment, to ensure that the site settings remain
 * consistent.
 */
include __DIR__ . "/settings.pantheon.php";

if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    $primary_domain = $_SERVER['HTTP_HOST']; // 'custom domain here';
  }
  else {
    $primary_domain = $_SERVER['HTTP_HOST'];
  }

  if ($_SERVER['HTTP_HOST'] != $primary_domain
    || !isset($_SERVER['HTTP_X_SSL'])
    || $_SERVER['HTTP_X_SSL'] != 'ON') {

    # Name transaction "redirect" in New Relic for improved reporting (optional)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }

    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://' . $primary_domain . $_SERVER['REQUEST_URI']);
    exit();
  }

  // Drupal 8 Trusted Host Settings
  if (is_array($settings)) {
    $settings['trusted_host_patterns'] = array('^' . preg_quote($primary_domain) . '$');
  }

  // This is necessary for onelogin php-saml.
  if (isset($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] === 'ON') {
    $_SERVER['SERVER_PORT'] = 443;
  }
  else {
    $_SERVER['SERVER_PORT'] = 80;
  }
}

/**
 * Place the config directory outside of the Drupal root.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config/common',
);

/**
 * Only enable dev config outside of pantheon or in pantheon dev.
 */
if (!isset($_SERVER['PANTHEON_ENVIRONMENT']) || ($_ENV['PANTHEON_ENVIRONMENT'] == 'dev')) {
  $config['config_split.config_split.development']['status'] = TRUE;
}
else {
  $config['config_split.config_split.development']['status'] = FALSE;
}


/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Always install the 'minimal' profile to stop the installer from
 * modifying settings.php.
 */
$settings['install_profile'] = 'config_installer';
