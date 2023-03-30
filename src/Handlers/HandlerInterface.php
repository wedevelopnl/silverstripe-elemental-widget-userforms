<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Handler;

use SilverStripe\Control\HTTPRequest;
use WeDevelop\ElementalWidget\UserForm\Widget\UserFormWidget;

interface HandlerInterface
{
    public static function handle(UserFormWidget $widget, array $data, ?HTTPRequest $request): void;
    public static function supports(UserFormWidget $widget, array $data, ?HTTPRequest $request): bool;
}
