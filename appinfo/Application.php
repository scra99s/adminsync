<?php
declare(strict_types=1);

namespace OCA\AdminSync\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

use OCP\EventDispatcher\IEventDispatcher;
use OCP\User\Events\PostLoginEvent;

use OCA\AdminSync\Listener\LoginListener;
use OCA\AdminSync\Service\AdminSyncService;
use OCA\AdminSync\Controller\SettingsController;
use OCA\AdminSync\Command\SetConfig;

class Application extends App implements IBootstrap {

    public const APP_ID = 'adminsync';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    /**
     * Register services (DI container wiring)
     */
    public function register(IRegistrationContext $context): void {

        // Register service
        $context->registerService(AdminSyncService::class, function ($c) {
            return new AdminSyncService(
                $c->get(\OCP\IGroupManager::class),
                $c->get(\OCP\ILogger::class)
            );
        });

        // Register listener
        $context->registerService(LoginListener::class, function ($c) {
            return new LoginListener(
                $c->get(AdminSyncService::class),
                $c->get(\OCP\IConfig::class),
                $c->get(\OCP\ILogger::class)
            );
        });

        // Register controller
        $context->registerService(SettingsController::class, function ($c) {
            return new SettingsController(
                self::APP_ID,
                $c->get(\OCP\IRequest::class),
                $c->get(\OCP\IConfig::class)
            );
        });

        // Register OCC command
        $context->registerService(SetConfig::class, function ($c) {
            return new SetConfig(
                $c->get(\OCP\IConfig::class)
            );
        });
        $context->registerCommand(SetConfig::class);
    }

    /**
     * Runtime boot logic
     */
    public function boot(IBootContext $context): void {

        $container = $context->getServerContainer();

        /** @var IEventDispatcher $dispatcher */
        $dispatcher = $container->get(IEventDispatcher::class);

        $dispatcher->addServiceListener(
            PostLoginEvent::class,
            LoginListener::class
        );
    }
}
