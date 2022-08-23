<?php

namespace Ospina\EasySQL;

use JsonException;
use mysqli;
use mysqli_result;
use Ospina\SmartQueryBuilder\SmartQueryBuilder;

/**
 *
 */
class EasySQL
{
    /**
     * @var mysqli
     */
    private mysqli $mysqli;
    /**
     * @var SmartQueryBuilder
     */
    private SmartQueryBuilder $smartQueryBuilder;
    /**
     * @var bool
     */
    private bool $isSelect = false;

    private $lastResult = null;

    /**
     * @param $database
     * @param $environment
     */
    public function __construct($database, $environment, $envPath = '/../../../../')
    {
        $settings = new MysqlConnectionSettings($environment,$envPath);
        $mysqli = new mysqli($settings->host, $settings->user, $settings->password, $database);
        $mysqli->set_charset('utf8'); //VERY IMPORTANTTT
        self::verifyConnectionErrors($mysqli);
        //No errors, return the instance
        $this->mysqli = $mysqli;
    }

    /**
     * @param mysqli $mysqli
     * @return void
     */
    public static function verifyConnectionErrors(mysqli $mysqli): void
    {
        if ($mysqli->connect_error) {
            $errorCode = $mysqli->connect_errno;
            $msg = $mysqli->connect_error;
            die("Connection error. Code ${errorCode} : ${msg}");
        }
    }

    /**
     * @param string $query
     * @return bool|mysqli_result
     */
    public function makeQuery(string $query)
    {
        return $this->mysqli->query($query);
    }

    /**
     * @param string $table
     * @return $this
     */
    public function table(string $table): EasySQL
    {
        $this->smartQueryBuilder = SmartQueryBuilder::table($table);
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function select(array $fields): EasySQL
    {
        $this->smartQueryBuilder->select($fields);
        $this->isSelect = true;
        return $this;
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return $this
     */
    public function where($column, $operator, $value): EasySQL
    {
        $this->smartQueryBuilder->where($column, $operator, $value);
        return $this;
    }

    /**
     * @param array $columns
     * @return bool
     */
    public function update(array $columns): bool
    {
        if ($this->verifyIsRetrieveQuery()) {
            throw new \RuntimeException('You can not mix retrieve and execution queries');
        }
        $this->smartQueryBuilder->update($columns);

        $execute = $this->makeQuery($this->logQuery());
        if ($execute === false) {
            throw new \RuntimeException('An error has occurred executing the query');
        }
        $this->lastResult = ['msg' => 'The last update statement was executed successfully'];
        return $execute;
    }

    /**
     * @param array $columns
     * @return bool
     */
    public function insert(array $columns)
    {
        if ($this->verifyIsRetrieveQuery()) {
            throw new \RuntimeException('You can not mix retrieve and execution queries');
        }
        $this->smartQueryBuilder->insert($columns);

        $execute = $this->makeQuery($this->logQuery());
        if ($execute === false) {
            throw new \RuntimeException('An error has occurred executing the query');
        }
        $this->lastResult = ['msg' => 'The last update statement was executed successfully'];
        return $execute;
    }

    private function verifyIsRetrieveQuery(): bool
    {
        return $this->isSelect === true;
    }

    /**
     * @return string
     */
    public function logQuery(): string
    {
        return $this->smartQueryBuilder->getQuery();
    }

    /**
     * @return array|mixed
     */
    public function get()
    {
        if (!$this->verifyIsRetrieveQuery()) {
            throw new \RuntimeException('Get method is only supported for SELECT, SHOW, DESCRIBE, EXPLAIN');
        }
        //Get the query
        $query = $this->logQuery();

        //This could have many
        $execute = $this->makeQuery($query);

        if ($execute === false) {
            throw new \RuntimeException('An error has occurred executing the query');
        }
        if (!($execute instanceof mysqli_result)) {
            throw new \RuntimeException('An error has ocurred, an mysqli_result instance was not able to obtain');
        }

        //The query method return a resource, lets obtain it.
        $this->lastResult = $execute->fetch_all(MYSQLI_ASSOC);
        return $this->lastResult;
    }

    /**
     * @throws JsonException
     */
    public function dd(): void
    {
        header('Content-Type: application/json;charset=utf-8');

        if ($this->lastResult === null) {
            $this->lastResult = ['msg' => 'There is no results to log'];
        }
        try {
            $encode = json_encode($this->lastResult, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonException('There was a problem logging your response: ' . $e->getMessage());
        }
        echo $encode;
        die();
    }

}