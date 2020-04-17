<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Finite\Event\TransitionEvent;
use Finite\Exception\TransitionException;
use Finite\Loader\ArrayLoader;
use Finite\State\StateInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Implement your document class
class Document implements StatefulInterface
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
$document = new Document;
$stateMachine = new StateMachine($document);
$loader = new ArrayLoader(
    [
        'class' => Document::class,
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
                'from' => [
                    'draft',
                ],
                'to' => 'proposed',
            ],
            'accept' => [
                'from' => [
                    'proposed',
                ],
                'to' => 'accepted',
                'properties' => [
                    'count' => 0,
                ],
            ],
            'reject' => [
                'from' => ['proposed'],
                'to' => 'draft',
                'configure_properties' => static function (OptionsResolver $optionsResolver) {
                    $optionsResolver->setRequired('count');
                },
            ],
        ],
        'callbacks' => [
            'before' => [
                [
                    'do' => function (StatefulInterface $document, TransitionEvent $e) {
                        echo sprintf(
                            "Applying transition \"%s\", count is \"%s\"\n",
                            $e->getTransition()->getName(),
                            $e->get('count', 'undefined')
                        );
                    },
                ],
            ],
        ],
    ]
);

$loader->load($stateMachine);
$stateMachine->initialize();

try {
    // Trying with an undefined property
    $stateMachine->apply(
        'propose',
        [
            'count' => 1,
        ]
    );
} catch (TransitionException $e) {
    echo "Property \"propose\" does not exists.\n";
}
$stateMachine->apply('propose');

try {
    // Trying without a mandatory property
    $stateMachine->apply('reject');
} catch (TransitionException $e) {
    echo "Property \"count\" is mandatory.\n";
}

$stateMachine->apply(
    'reject',
    [
        'count' => 2,
    ]
);

$stateMachine->apply('propose');

// Default value is used
$stateMachine->apply('accept');
