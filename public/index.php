<?php

use App\Kernel;

use MobileDetectBundle\DeviceDetector\MobileDetectorInterface;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    ini_set('memory_limit', '-1');
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

};


