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


use Nurschool\Bundle\NurschoolSendGridBundle\Event\SendGridEventInterface;
use Nurschool\Bundle\NurschoolSendGridBundle\SendGrid\Logger\SendGridLoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendGridEventSubscriber implements SendGridEventSubscriberInterface, EventSubscriberInterface
{
    /** @var SendGridLoggerInterface */
    private $logger;

    /**
     * @param SendGridLoggerInterface $logger
     */
    public function setLogger(SendGridLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SendGridEventInterface::STARTED => 'onStarted',
            SendGridEventInterface::FINISHED => 'onFinished',
            SendGridEventInterface::FAILED => 'onFailed'
        ];
    }

    public function onFailed(SendGridEventInterface $event): void
    {
        if (null !== $this->logger) {
            $mail = $event->getMail();
            $this->logger->logSendingFailed($mail);
        }
    }

    public function onStarted(SendGridEventInterface $event): void
    {
        if (null !== $this->logger) {
            $mail = $event->getMail();
            $this->logger->logSendingStarted($mail);
        }
    }

    public function onFinished(SendGridEventInterface $event): void
    {
        if (null !== $this->logger) {
            $mail = $event->getMail();
            $messageId = $event->getMessageId();
            $this->logger->logSendingFinished($mail, $messageId);
        }
    }
}