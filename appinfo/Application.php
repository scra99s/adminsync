<?php
declare(strict_types=1);

namespace OCA\AdminSync\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\Console\IConsole;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\User\Events\PostLoginEvent;

use OCA\AdminSync\Listener\LoginListener;
use OCA\AdminSync\Service\AdminSyncService;
use OCA\AdminSync\Command\SetConfig;

class Application extends App implements IBootstrap {

    public const APP_ID = 'adminsync';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {

        // Register the core service
        $context->registerService(AdminSyncService::class, function($c) {
            return new AdminSyncService(
                $c->get(\OCP\IGroupManager::class),
                $c->get(\OCP\ILogger::class)
            );
        });

        // Register the login listener
        $context->registerService(LoginListener::class, function($c) {
            return new LoginListener(
                $c->get(AdminSyncService::class),
                $c->get(\OCP\IConfig::class),
                $c->get(\OCP\ILogger::class)
            );
        });

        // Register the OCC command
        $context->registerService(SetConfig::class, function($c) {
            return new SetConfig($c->get(\OCP\IConfig::class));
        });
    }

    public function boot(IBootContext $context): void {
        $container = $context->getServerContainer();

        // Register login event
        /** @var IEventDispatcher $dispatcher */
        $dispatcher = $container->get(IEventDispatcher::class);
        $dispatcher->addServiceListener(PostLoginEvent::class, LoginListener::class);

        // Register OCC command
        /** @var IConsole $console */
        $console = $container->get(IConsole::class);
        $console->addCommand($container->get(SetConfig::class));
    }
}
