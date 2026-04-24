<?php

declare(strict_types=1);

namespace App\Plugins\Demo\EventSubscriber;

use App\Modules\Auth\Application\Write\Handler\ChangePasswordHandler;
use App\Modules\Plugin\Application\PluginManager\Read\Handler\IsPluginEnabledHandler;
use App\Modules\User\Domain\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetDefaultPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IsPluginEnabledHandler $isPluginEnabledHandler,
        private ChangePasswordHandler $changePasswordHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedEvent::class => 'onUserCreated',
        ];
    }

    public function onUserCreated(UserCreatedEvent $event): void
    {
        if ($this->isPluginEnabledHandler->handle('Demo')) {
            $user = $event->getUser();
            $this->changePasswordHandler->handle($user, 'demo');
        }
    }
}
