<?php

namespace App\Helpers;

use App\Services\Services;

trait LivewireHelper
{
    private function setService($service)
    {
        return Services::createInstance($service) ?? new Services();
    }
}
