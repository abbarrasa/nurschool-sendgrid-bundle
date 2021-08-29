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

namespace Nurschool\Bundle\NurschoolSendgridBundle\Logger;


use Psr\Log\LoggerInterface;
use SendGrid\Mail\Attachment;
use SendGrid\Mail\Content;
use SendGrid\Mail\EmailAddress;
use SendGrid\Mail\Mail;

final class SendgridLogger implements SendgridLoggerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logSendingStarted(Mail $mail): void
    {
        $info = $this->transform($mail);
        $this->logger->info(sprintf('Sending mail to: %s', implode(', ', $info['tos'])));
    }

    public function logSendingFinished(Mail $mail, ?string $messageId = ''): void
    {
        $info = $this->transform($mail, $messageId);
        $message = !empty($messageId) ?
            sprintf('Mail %s sent to: %s', $messageId, implode(', ', $info['tos'])) :
            sprintf('Mail sent to: %s', implode(', ', $info['tos']))
        ;

        $this->logger->info($message);
    }

    public function logSendingFailed(Mail $mail): void
    {
        $info = $this->transform($mail);
        $this->logger->error(sprintf('Failed sending mail to: %s', implode(', ', $info['tos'])));
    }

    private function transform(Mail $mail, string $messageId = '')
    {
        return [
            'subject' => $mail->getGlobalSubject()->getSubject(),
            'from' => $this->formatAddress($mail->getFrom()),
            'tos' => $this->getRecipients($mail, 'tos'),
            'bccs' => $this->getRecipients($mail, 'bccs'),
            'ccs' => $this->getRecipients($mail, 'ccs'),
            'contents' => !empty($mail->getContents()) ? array_map(function (Content $content) {
                return [
                    'type' => $content->getType(),
                    'content' => $content->getValue(),
                ];
            }, $mail->getContents()) : [],
            'attachments' => !empty($mail->getAttachments()) ? array_map(function(Attachment $attachment) {
                return [
                    'filename' => $attachment->getFilename(),
                    'mime' => $attachment->getType(),
                    'cid' => $attachment->getContentID(),
                    'disposition' => $attachment->getDisposition()
                ];
            }, $mail->getAttachments()) : [],
            'messageId' => $messageId,
        ];
    }

    private function getRecipients(Mail $mail, string $type)
    {
        $recipients = [];

        foreach ($mail->getPersonalizations() as $personalization) {
            if(empty($personalization->{'get'.$type}())) {
                continue;
            }
            /** @var EmailAddress $address */
            foreach ($personalization->{'get'.$type}() as $address) {
                $recipients[] = $this->formatAddress($address);
            }
        }

        return $recipients;
    }

    private function formatAddress(EmailAddress $address)
    {
        return $address->getName() . ' <' . $address->getEmailAddress() . '>';
    }
}