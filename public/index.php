<?php

use ExampleApp\Steam;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$steam = new Steam();

$steam->join();
