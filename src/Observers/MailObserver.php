<?php

namespace Beam\Beam\Observers;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Beam\Beam\Payloads\MailPayload;
use Beam\BeamCore\Actions\{Config, Dumper};
use Beam\BeamCore\Beam;

class MailObserver
{
    public function register(): void
    {
        Event::listen(MessageSent::class, function (MessageSent $messageSent) {
            if (!$this->isEnabled()) {
                return;
            }

            $dumps = new Beam();

            $payload = new MailPayload($messageSent->sent, Dumper::dump($messageSent->data), $messageSent->sent->getMessageId());

            $dumps->send($payload);
        });

        Event::listen(NotificationSent::class, function (NotificationSent $notificationSent) {
            if (!$this->isEnabled()) {
                return;
            }

            if (is_null($notificationSent->response)) {
                return;
            }

            /** @var SentMessage $sentMessage */
            $sentMessage = $notificationSent->response;

            if (!$sentMessage instanceof SentMessage) {
                return;
            }

            $details = Dumper::dump([
                'notifiable'   => $notificationSent->notifiable,
                'notification' => $notificationSent->notification,
                'channel'      => $notificationSent->channel,
            ]);

            $dumps = new Beam();

            $payload = new MailPayload($sentMessage, $details, $sentMessage->getMessageId(), label: $notificationSent->channel);

            $dumps->send($payload);
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('observers.mail', false);
    }
}
