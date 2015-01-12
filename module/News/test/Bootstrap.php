<?php
namespace News\Test;

define('APPLICATION_ROOT', '../../../');
require_once APPLICATION_ROOT . 'init_tests_autoloader.php';

use UnitTestBootstrap;

class NewsBootstrap extends UnitTestBootstrap\UnitTestBootstrap
{}

NewsBootstrap::init();