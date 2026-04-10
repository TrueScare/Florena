<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

passthru('php bin/console doctrine:database:drop -f --if-exists --env=test');
// create fresh db
passthru('php bin/console doctrine:database:create --if-not-exists --env=test');
// get done with the migration processes
passthru('echo yes | php bin/console doctrine:migrations:migrate --env=test --no-interaction');
