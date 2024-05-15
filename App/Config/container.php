<?php

use \Psr\Container\ContainerInterface;

$container = $app->getContainer();

require_once 'mysql.php';

foreach(glob(__DIR__ . '/../Controllers' . "/*.php") as $file){
    require_once $file;
    $name = basename($file, ".php");
    $container->set($name, function (ContainerInterface $container) use ($name) {
        $class = 'App\Controllers\\'.$name;
        return new $class($container);
    });
}

$container->set('JwtSecretsMap', function (ContainerInterface $container) {
    $jwts = [];
    $jwts["Professor"] = "Hxr3%=me@%>N";
    $jwts["Aluno"] = "Vc&2B?*!@Jph7*cq";
    $jwts["Administrador"] = "V2vuyEq5q8sV*CxD";

    return $jwts;
});

/*
$container->set('IndexController', function (ContainerInterface $container) {
    return new App\Controllers\IndexController($container);
});*/