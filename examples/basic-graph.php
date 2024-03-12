<?php

declare(strict_types=1);

use Finite\Exception\StateException;
use Finite\Exception\TransitionException;

require_once __DIR__ . '/../vendor/autoload.php';

// Implement your document class
class Document implements Finite\StatefulInterface
{
    private $state;

    public function getFiniteState(): string
    {
        return (string)$this->state;
    }

    public function setFiniteState(string $state): void
    {
        $this->state = $state;
    }
}

// Configure your graph
$document = new Document();
$stateMachine = new Finite\StateMachine\StateMachine($document);
$loader = new Finite\Loader\ArrayLoader(
    [
        'class' => Document::class,
        'states' => [
            'draft' => [
                'type' => Finite\State\StateInterface::TYPE_INITIAL,
                'properties' => [
                    'deletable' => true,
                    'editable' => true,
                ],
            ],
            'proposed' => [
                'type' => Finite\State\StateInterface::TYPE_NORMAL,
                'properties' => [],
            ],
            'accepted' => [
                'type' => Finite\State\StateInterface::TYPE_FINAL,
                'properties' => [
                    'printable' => true,
                ],
            ],
        ],
        'transitions' => [
            'propose' => [
                'from' => ['draft'],
                'to' => 'proposed',
            ],
            'accept' => [
                'from' => ['proposed'],
                'to' => 'accepted',
            ],
            'reject' => [
                'from' => ['proposed'],
                'to' => 'draft',
            ],
        ],
    ]
);

$loader->load($stateMachine);
$stateMachine->initialize();

// Working with workflow

// Current state
var_dump($stateMachine->getCurrentState()->getName());
var_dump($stateMachine->getCurrentState()->getProperties());
var_dump($stateMachine->getCurrentState()->has('deletable'));
var_dump($stateMachine->getCurrentState()->has('printable'));

// Available transitions
var_dump($stateMachine->getCurrentState()->getTransitions());
var_dump($stateMachine->can('propose'));
var_dump($stateMachine->can('accept'));

// Apply transitions
try {
    $stateMachine->apply('accept');
} catch (StateException|TransitionException $e) {
    echo $e->getMessage(), "\n";
}

// Applying a transition
$stateMachine->apply('propose');
var_dump($stateMachine->getCurrentState()->getName());
var_dump($document->getFiniteState());
