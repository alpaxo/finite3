<?php

declare(strict_types=1);

use Finite\Loader\ArrayLoader;
use Finite\State\StateInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;

require_once __DIR__ . '/../vendor/autoload.php';

// Implement your document class
class Document implements StatefulInterface
{
    private $state;

    public function getFiniteState(): string
    {
        return (string)$this->state;
    }

    public function setFiniteState($state)
    {
        $this->state = $state;
    }
}

$passGuard = function (StateMachine $stateMachine) {
    echo "Pass guard called\n";

    return true;
};

$failGuard = function (StateMachine $stateMachine) {
    echo "Fail guard called\n";

    return false;
};

// Configure your graph
$document = new Document;
$stateMachine = new StateMachine($document);
$loader = new ArrayLoader(
    [
        'class' => 'Document',
        'states' => [
            'draft' => [
                'type' => StateInterface::TYPE_INITIAL,
                'properties' => [],
            ],
            'proposed' => [
                'type' => StateInterface::TYPE_NORMAL,
                'properties' => [],
            ],
            'accepted' => [
                'type' => StateInterface::TYPE_FINAL,
                'properties' => [],
            ],
        ],
        'transitions' => [
            'propose' => [
                'from' => ['draft'],
                'to' => 'proposed',
                'guard' => $passGuard,
            ],
            'accept' => [
                'from' => ['proposed'],
                'to' => 'accepted',
                'guard' => $failGuard,
            ],
        ],
    ]
);

$loader->load($stateMachine);
$stateMachine->initialize();

// testing the guard
echo "Can we apply propose? \n";
var_dump($stateMachine->can('propose'));
$stateMachine->apply('propose');

echo "\nCan we apply accept? \n";
var_dump($stateMachine->can('accept'));
