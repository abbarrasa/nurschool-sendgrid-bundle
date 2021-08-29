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

namespace Nurschool\Bundle\NurschoolSendGridBundle\SendGrid\Logger;


use SendGrid\Mail\Mail;

interface SendGridLoggerInterface
{
    public function logSendingStarted(Mail $mail): void;
    public function logSendingFinished(Mail $mail, ?string $messageId = null): void;
    public function logSendingFailed(Mail $mail): void;

}