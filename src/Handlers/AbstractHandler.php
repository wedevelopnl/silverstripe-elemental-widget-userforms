<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Handler;

use SilverStripe\Control\HTTPRequest;
use WeDevelop\ElementalWidget\UserForm\Widget\UserFormWidget;

abstract class AbstractHandler implements HandlerInterface
{
    public static function supports(UserFormWidget $widget, array $data, ?HTTPRequest $request): bool
    {
        return false;
    }
}
