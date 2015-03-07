<?php

require __DIR__ . '/atoum.phar';

spl_autoload_register(function($className)
{
    $className = __DIR__ . '/../src/' . str_replace('\\', '/', $className) . '.php';
    if(file_exists($className)) require $className;
});
