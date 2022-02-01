<?php

namespace App\Helpers;

class PromptHelper
{
    public const DEFAULTS = [
        'type'                  => 'ask',
        'prompt'                => '',
        'default'               => null,
        'defaultCallback'       => null,
        'nonInteractiveDefault' => null,
        'choices'               => [],
    ];

    public static function config(array $config): array
    {
        return array_merge(self::DEFAULTS, $config);
    }

    public static function default(array $config)
    {
        if (!is_null($config['defaultCallback'])) {
            return call_user_func($config['defaultCallback']);
        }

        return $config['default'];
    }
}
