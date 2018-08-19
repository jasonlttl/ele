# ELE

[![CircleCI](https://circleci.com/gh/jasonlttl/ele.svg?style=shield)](https://circleci.com/gh/jasonlttl/ele)
[![Dashboard ele](https://img.shields.io/badge/dashboard-ele-yellow.svg)](https://dashboard.pantheon.io/sites/7ed780fb-f05f-4119-9526-e0afaf179704#dev/code)
[![Dev Site ele](https://img.shields.io/badge/site-ele-blue.svg)](http://dev-ele.pantheonsite.io/)

## Summary
A personal blog, named after my dog, E.L.E.

## Workflow

The project is meant to run in Pantheon with automated deployments via CircleCI
using a variant of standard terminus build tools pull request workflow. 

## Local Development

### Requirements
* Composer
* PHP 7.2
* MySQL

### Quickstart 
```
# Clone the project
git clone git@github.com/jasonlttl/ele.git

# Change directory to project and install code dependencies.
cd ele
composer install

# Change directory to drupal webroot and install Drupal.
# Note: uses a localhost only mysql account, see custom/sites/settings.local.php
cd web
../vendor/bin/drush site-install config_installer

# Run the site
../vendor/bin/drush runserver
```