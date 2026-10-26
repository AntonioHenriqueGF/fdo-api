<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application and aborts if it is not pointing to a testing database,
     * preventing traits like RefreshDatabase from wiping real data.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if (! str_ends_with($database, '_testing')) {
            throw new RuntimeException(
                "Testes abortados: o banco \"{$database}\" não é um banco de testes (deve terminar com \"_testing\")."
            );
        }

        return $app;
    }
}
