<?php

/*
 * This file is part of the Nurschool project.
 *
 * (c) Nurschool <https://github.com/abbarrasa/nurschool>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nurschool\Bundle\NurschoolSendgridBundle\Event;


use SendGrid\Mail\Mail;

interface SendgridEventInterface
{
    public const STARTED = 'sendgrid.started';
    public const FINISHED = 'sendgrid.finished';
    public const FAILED = 'sendgrid.failed';

    /**
     * @return Mail
     */
    public function getMail(): Mail;

    /**
     * @param Mail $mail
     */
    public function setMail(Mail $mail);

    /**
     * @return string|null
     */
    public function getMessageId(): ?string;

    /**
     * @param string|null $messageId
     */
    public function setMessageId(?string $messageId);
}