<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use ReflectionClass;

class Services extends Controller
{
    public static function createInstance($className)
    {
        $className = "App\Services\\$className";

        if (!class_exists($className)) {
            throw new \Exception("Class '$className' does not exist.");
        }

        $reflection = new ReflectionClass($className);

        return $reflection->newInstance();
    }

    public static function modelInstance($className)
    {
        $className = "App\Models\\$className";

        if (!class_exists($className)) {
            throw new \Exception("Class '$className' does not exist.");
        }

        $reflection = new ReflectionClass($className);

        return $reflection->newInstance();
    }

    public static function exportExcel($className)
    {
        $className = "App\Exports\\$className";

        if (!class_exists($className)) {
            throw new \Exception("Class '$className' does not exist.");
        }

        $reflection = new ReflectionClass($className);

        return $reflection->newInstance();
    }
}
