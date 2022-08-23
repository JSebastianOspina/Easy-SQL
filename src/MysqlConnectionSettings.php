<?php

namespace Ospina\EasySQL;

use Dotenv\Dotenv;

require 'vendor/autoload.php';

class MysqlConnectionSettings
{

    public string $host;
    public string $user;
    public string $password;

    public function __construct(string $environment, string $envPath)
    {

        $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . $envPath);
        $dotenv->load();

        $this->host = $environment === 'local' ? getenv('DB_LOCAL_HOST') : getenv('DB_PROD_HOST');
        $this->user = $environment === 'local' ? getenv('DB_LOCAL_USERNAME') : getenv('DB_PROD_USERNAME');
        $this->password = $environment === 'local' ? getenv('DB_LOCAL_PASSWORD') : getenv('DB_PROD_PASSWORD');

    }

}