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

namespace Nurschool\Bundle\NurschoolSendgridBundle\EventSubscriber;


use Nurschool\Bundle\NurschoolSendgridBundle\Event\SendgridEventInterface;

interface SendgridEventSubscriberInterface
{
    public function onStarted(SendgridEventInterface $event): void;

    public function onFinished(SendgridEventInterface $event): void;

    public function onFailed(SendgridEventInterface $event): void;
}