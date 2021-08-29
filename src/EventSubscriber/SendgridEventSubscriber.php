<?php

/*
 * This file is part of the Nurschool project.
 *
 * (c) Nurschool <https://github.com/abbarrasa/nurschool>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nurschool\Bundle\NurschoolSendGridBundle\EventSubscriber;


use Nurschool\Bundle\NurschoolSendgridBundle\Event\SendgridEventInterface;
use Nurschool\Bundle\NurschoolSendgridBundle\SendGrid\Logger\SendgridLoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendgridEventSubscriber implements SendgridEventSubscriberInterface, EventSubscriberInterface
{
    /** @var SendgridLoggerInterface */
    private $logger;

    /**
     * @param SendgridLoggerInterface $logger
     */
    public function setLogger(SendgridLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SendgridEventInterface::STARTED => 'onStarted',
            SendgridEventInterface::FINISHED => 'onFinished',
            SendgridEventInterface::FAILED => 'onFailed'
        ];
    }

    public function onFailed(SendgridEventInterface $event): void
    {
        if (null !== $this->logger) {
            $mail = $event->getMail();
            $this->logger->logSendingFailed($mail);
        }
    }

    public function onStarted(SendgridEventInterface $event): void
    {
        if (null !== $this->logger) {
            $mail = $event->getMail();
            $this->logger->logSendingStarted($mail);
        }
    }

    public function onFinished(SendgridEventInterface $event): void
    {
        if (null !== $this->logger) {
            $mail = $event->getMail();
            $messageId = $event->getMessageId();
            $this->logger->logSendingFinished($mail, $messageId);
        }
    }
}