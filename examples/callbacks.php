<?php

declare(strict_types=1);

use Finite\Event\Callback\CallbackBuilder;
use Finite\Event\FiniteEvents;
use Finite\Event\TransitionEvent;
use Finite\Loader\ArrayLoader;
use Finite\State\StateInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;

require_once __DIR__ . '/../vendor/autoload.php';

// Implement your document class
class Document implements Finite\StatefulInterface
{
    private $state;

    public function getFiniteState(): string
    {
        return (string)$this->state;
    }

    public function setFiniteState($state): void
    {
        $this->state = $state;
    }

    public function display(): void
    {
        echo 'Hello, I\'m a document and I\'m currently at the ', $this->state, ' state.', "\n";
    }
}

// Configure your graph
$document = new Document();
$stateMachine = new StateMachine($document);
$loader = new ArrayLoader(
    [
        'class' => Document::class,
        'states' => [
            'draft' => [
                'type' => StateInterface::TYPE_INITIAL,
                'properties' => [
                    'deletable' => true,
                    'editable' => true,
                ],
            ],
            'proposed' => [
                'type' => StateInterface::TYPE_NORMAL,
                'properties' => [],
            ],
            'accepted' => [
                'type' => StateInterface::TYPE_FINAL,
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
        'callbacks' => [
            'before' => [
                [
                    'from' => '-proposed',
                    'do' => static function (StatefulInterface $document, TransitionEvent $e) {
                        echo 'Applying transition ' . $e->getTransition()->getName(), ' to document with state ', $document->getFiniteState(), "\n";
                    },
                ],
                [
                    'from' => 'proposed',
                    'do' => static function () {
                        echo 'Applying transition from proposed state', "\n";
                    },
                ],
            ],
            'after' => [
                [
                    'to' => ['accepted'],
                    'do' => [$document, 'display'],
                ],
            ],
        ],
    ]
);

$loader->load($stateMachine);
$stateMachine->initialize();

$stateMachine->getDispatcher()->addListener(
    FiniteEvents::PRE_TRANSITION,
    static function (TransitionEvent $e) {
        echo 'This is a pre <', $e->getTransition()->getName(), '> transition', "\n";
    }
)
;

$foobar = 42;
$stateMachine->getDispatcher()
    ->addListener(
        FiniteEvents::POST_TRANSITION,
        CallbackBuilder::create($stateMachine)
            ->setCallable(
                static function () use ($foobar) {
                    echo "\$foobar is ${foobar} and this is a post transition\n";
                }
            )
            ->getCallback()
    )
;

$stateMachine->apply('propose');
$stateMachine->apply('reject');
$stateMachine->apply('propose');
$stateMachine->apply('accept');
