<?php
declare(strict_types=1);

namespace OCA\AdminSync\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\PostLoginEvent;
use OCP\IConfig;
use OCP\ILogger;
use OCA\AdminSync\Service\AdminSyncService;

class LoginListener implements IEventListener {

    private AdminSyncService $service;
    private IConfig $config;
    private ILogger $logger;

    public function __construct(AdminSyncService $service, IConfig $config, ILogger $logger) {
        $this->service = $service;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function handle(Event $event): void {
        if (!($event instanceof PostLoginEvent)) {
            return;
        }

        $user = $event->getUser();
        if (!$user) {
            $this->logger->warning('AdminSync: Login event without user');
            return;
        }

        $adminGroups = json_decode($this->config->getAppValue('adminsync', 'admin_groups', '[]'), true) ?: [];
        $protectedAdmins = json_decode($this->config->getAppValue('adminsync', 'protected_admins', '[]'), true) ?: [];

        $this->service->syncUser($user, $adminGroups, $protectedAdmins);
    }
}
