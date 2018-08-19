<?php

$databases['default']['default'] = array (
  'database' => 'ele',
  'username' => 'root',
  'password' => '',
  'prefix' => '',
  'host' => '127.0.0.1',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

// Pantheon explicitly sets this somewhere.
$settings['hash_salt'] = md5('Pantheon sets me. ' . gethostname());
