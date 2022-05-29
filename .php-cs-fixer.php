<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude(['.gitlab-ci-local', 'ci', 'coverage', 'docker', 'Resources', 'vendor'])
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'     => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
