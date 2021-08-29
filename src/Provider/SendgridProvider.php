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

namespace Nurschool\Bundle\NurschoolSendgridBundle\Provider;

use Nurschool\Bundle\NurschoolSendgridBundle\Event\SendgridEvent;
use Nurschool\Bundle\NurschoolSendgridBundle\EventDispatcher\SendgridEventDispatcherInterface;
use Nurschool\Bundle\NurschoolSendgridBundle\Exception\AccessDeniedSendgridException;
use Nurschool\Bundle\NurschoolSendgridBundle\Exception\BadRequestSendgridException;
use Nurschool\Bundle\NurschoolSendgridBundle\Exception\SendgridException;
use Nurschool\Bundle\NurschoolSendgridBundle\Exception\UnauthorizedSendgridException;
use SendGrid\Mail\Mail;
use SendGrid\Mail\MailSettings;
use SendGrid\Mail\Personalization;
use SendGrid\Mail\SandBoxMode;
use SendGrid\Mail\To;
use SendGrid\Response;

class SendgridProvider
{
    /** @var \SendGrid */
    private $apiClient;

    /** @var SendgridEventDispatcherInterface */
    private $eventDispatcher;

    /** @var mixed */
    private $redirectTo;

    /** @var bool */
    private $disableDelivery;

    /** @var bool */
    private $sandbox;

    /** @var Personalization[] */
    private $originalPersonalization;

    /** @var \ReflectionProperty */
    private $personalizationReflection;

    /** @var Personalization */
    private $redirectPersonalization;

    public function __construct(
        \SendGrid                        $apiClient,
        SendgridEventDispatcherInterface $eventDispatcher,
                                         $redirectTo = false,
        bool                             $disableDelivery = true,
        bool                             $sandbox = true
    ) {
        $this->apiClient = $apiClient;
        $this->eventDispatcher = $eventDispatcher;
        $this->redirectTo = $redirectTo;
        $this->disableDelivery = $disableDelivery;
        $this->sandbox = $sandbox;

        $this->configure();
    }

    /**
     * Creates an email with SendGrid REST API
     * @param $from
     * @param $to
     * @param $subject
     * @param $templateId
     * @param array $data
     * @return Mail
     * @throws \SendGrid\Mail\TypeException
     */
    public function createMail($from, $to, $subject, $templateId, array $data = []): Mail
    {
        $mailSettings = new MailSettings();
        $sandboxMode  = new SandBoxMode();
        $sandboxMode->setEnable($this->sandbox);
        $mailSettings->setSandboxMode($sandboxMode);
        $email = new Mail();
        $email->setMailSettings($mailSettings);

        if (is_array($from)) {
            list($fromEmail, $senderName) = $from;
            $email->setFrom($fromEmail, $senderName);
        } else {
            $email->setFrom($from);
        }

        $email->addTo($to);
        $email->setSubject($subject);
        $email->setTemplateId($templateId);

        foreach($data as $key => $value) {
            $email->addDynamicTemplateData($key, $value);
        }

        return $email;
    }

    /**
     * Sends an email with SendGrid REST API
     * @param Mail $mail
     * @return string|null
     * @throws SendGridException
     */
    public function sendMail(Mail $mail): ?string
    {
        $this->eventDispatcher->dispatch(new SendgridEvent($mail), SendgridEvent::STARTED);
        if ($this->disableDelivery) {
            $this->eventDispatcher->dispatch(new SendgridEvent($mail), SendgridEvent::FINISHED);
            return null;
        }

        try {
            $this->redirect($mail);

            $response = $this->apiClient->send($mail);

            $this->reverseRedirection($mail);
            $this->checkResponse($response);

            $messageId = $this->getMessageId($response);

            $this->eventDispatcher->dispatch(new SendGridEvent($mail, $messageId), SendGridEvent::FINISHED);

            return $messageId;

        } catch (\Exception $exception) {
            $this->reverseRedirection($mail);
            $this->eventDispatcher->dispatch(new SendGridEvent($mail), SendGridEvent::FAILED);
            if($exception instanceof SendGridException) {
                throw $exception;
            }

            throw new SendGridException($exception->getMessage());
        }
    }

    private function configure()
    {
        if (false !== $this->redirectTo) {
            $this->redirectPersonalization = new Personalization();
            $this->redirectPersonalization->addTo(new To($this->redirectTo));

            $this->personalizationReflection = new \ReflectionProperty(Mail::class, 'personalization');
            $this->personalizationReflection->setAccessible(true);
        }
    }


    private function redirect(Mail $mail): void
    {
        if (false !== $this->redirectTo) {
            $this->originalPersonalization = $mail->getPersonalizations();
            $this->personalizationReflection->setValue($mail, [$this->redirectPersonalization]);
        }
    }

    private function reverseRedirection(Mail $mail): void
    {
        if (false !== $this->redirectTo) {
            $this->personalizationReflection->setValue($mail, $this->originalPersonalization);
        }
    }

    /**
     * @param Response $response
     * @return string|null
     * @throws SendGridException
     */
    private function getMessageId(Response $response): ?string
    {
        try {
            return $response->headers(true)['X-Message-Id'];
        } catch (\Exception $e) {
            throw new SendGridException('X-Message-Id header not found in SendGrid API response');
        }
    }

    private function checkResponse(Response $response): void
    {
        if ($response->statusCode() == 401) {
            throw new UnauthorizedSendgridException($response->body());
        }

        if ($response->statusCode() == 403) {
            throw new AccessDeniedSendgridException($response->body());
        }

        if (preg_match('/5[0-9]{2}/', strval($response->statusCode()))) {
            throw new SendgridException($response->body());
        }

        if (preg_match('/4[0-9]{2}/', strval($response->statusCode()))) {
            throw new BadRequestSendgridException($response->body());
        }
    }
}