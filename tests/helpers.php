<?php

use Dotenv\Repository\Adapter\PutenvAdapter;

function configureEnv(): void
{
    if (! file_exists(__DIR__.'/.env')) {
        return;
    }

    $repositoryRepository = \Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()
        ->addWriter(PutenvAdapter::class)
        ->make();

    $dotenv = \Dotenv\Dotenv::create($repositoryRepository, __DIR__);
    $dotenv->load();
}
