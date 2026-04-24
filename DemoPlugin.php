<?php

namespace App\Plugins\Demo;

use App\Shared\Plugin\Abstracts\AbstractPlugin;

class DemoPlugin extends AbstractPlugin
{
    protected static bool $locked = true;

    public static function getName(): string
    {
        return 'DemoPlugin';
    }

    public static function getVersion(): string
    {
        return '1.0.0';
    }

    public static function getAuthor(): string
    {
        return 'Generated';
    }

    public static function getDescription(): string
    {
        return 'A plugin to manage demo.';
    }
}
