<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Finite\Factory\PimpleFactory;
use Finite\Loader\ArrayLoader;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;
use Pimple\Container;

class Order
{
    // First state graph, payment status
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_ACCEPTED = 'accepted';
    const PAYMENT_REFUSED = 'refused';
    // second state graph, shipping status
    const SHIPPING_PENDING = 'pending';
    const SHIPPING_PARTIAL = 'partial';
    const SHIPPING_SHIPPED = 'shipped';

    /**
     * @var string
     */
    public $paymentStatus;

    /**
     * @var string
     */
    public $shippingStatus;

    public function setPaymentStatus($paymentStatus): void
    {
        $this->paymentStatus = $paymentStatus;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function setShippingStatus($shippingStatus)
    {
        $this->shippingStatus = $shippingStatus;
    }

    public function getShippingStatus(): string
    {
        return $this->shippingStatus;
    }
}

$order = new Order;

// Configure the payment graph
$paymentLoader = new ArrayLoader(
    [
        'class' => Order::class,
        'graph' => 'payment',
        'property_path' => 'paymentStatus',
        'states' => [
            Order::PAYMENT_PENDING => [
                'type' => StateInterface::TYPE_INITIAL,
            ],
            Order::PAYMENT_ACCEPTED => [
                'type' => StateInterface::TYPE_FINAL,
            ],
            Order::PAYMENT_REFUSED => [
                'type' => StateInterface::TYPE_FINAL,
            ],
        ],
        'transitions' => [
            'accept' => [
                'from' => [
                    Order::PAYMENT_PENDING,
                ],
                'to' => Order::PAYMENT_ACCEPTED,
            ],
            'refuse' => [
                'from' => [
                    Order::PAYMENT_PENDING,
                ],
                'to' => Order::PAYMENT_REFUSED,
            ],
        ],
    ]
);

// Configure the shipping graph
$shippingLoader = new ArrayLoader(
    [
        'class' => Order::class,
        'graph' => 'shipping',
        'property_path' => 'shippingStatus',
        'states' => [
            Order::SHIPPING_PENDING => [
                'type' => StateInterface::TYPE_INITIAL,
            ],
            Order::SHIPPING_PARTIAL => [
                'type' => StateInterface::TYPE_NORMAL,
            ],
            Order::SHIPPING_SHIPPED => [
                'type' => StateInterface::TYPE_FINAL,
            ],
        ],
        'transitions' => [
            'ship_partially' => [
                'from' => [
                    Order::SHIPPING_PENDING,
                ],
                'to' => Order::SHIPPING_PARTIAL,
            ],
            'ship' => [
                'from' => [
                    Order::SHIPPING_PENDING,
                    Order::SHIPPING_PARTIAL,
                ],
                'to' => Order::SHIPPING_SHIPPED,
            ],
        ],
    ]
);

// Configure the factory (Pimple factory is used here)

$container = new Container(
    [
        'finite.state_machine' => static function () {
            return new StateMachine();
        },
    ]
);
$factory = new PimpleFactory($container, 'finite.state_machine');
$factory->addLoader($paymentLoader);
$factory->addLoader($shippingLoader);

// Working with workflows

$paymentStateMachine = $factory->get($order, 'payment');

// Current state
var_dump($paymentStateMachine->getCurrentState()->getName());
var_dump($paymentStateMachine->getCurrentState()->getProperties());

// Available transitions
var_dump($paymentStateMachine->getCurrentState()->getTransitions());
var_dump($paymentStateMachine->can('accept'));
$paymentStateMachine->apply('accept');
var_dump($paymentStateMachine->getCurrentState()->getName());

