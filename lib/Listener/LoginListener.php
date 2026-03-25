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

  public function __construct(
    AdminSyncService $service,
    IConfig $config,
    ILogger $logger
  ) {
    $this->service = $service;
    $this->config = $config;
    $this->logger = $logger;
  }

  /**
   * Handle login event
   */
  public function handle(Event $event): void {
    if (!($event instanceof PostLoginEvent)) {
      return;
    }

    try {
      $user = $event->getUser();

      if ($user === null) {
        $this->logger->warning('AdminSync: Login event without user');
        return;
      }

      $uid = $user->getUID();

      // --- Load and decode config safely ---
      $adminGroups = $this->decodeJsonConfig(
        $this->config->getAppValue('adminsync', 'admin_groups', '[]'),
        'admin_groups'
      );

      $protectedAdmins = $this->decodeJsonConfig(
        $this->config->getAppValue('adminsync', 'protected_admins', '[]'),
        'protected_admins'
      );

      // --- Log config snapshot (debug only) ---
      $this->logger->debug('AdminSync: Loaded configuration', [
        'user' => $uid,
        'adminGroups' => $adminGroups,
        'protectedAdmins' => $protectedAdmins
      ]);

      // --- Execute sync logic ---
      $this->service->syncUser(
        $user,
        $adminGroups,
        $protectedAdmins
      );

    } catch (\Throwable $e) {
        // Never break login flow
        $this->logger->error('AdminSync: Exception during login sync', [
          'exception' => $e->getMessage(),
          'trace' => $e->getTraceAsString()
        ]);
    }
  }

  /**
   * Safely decode JSON config values
   */
  private function decodeJsonConfig(string $value, string $key): array {
    $decoded = json_decode($value, true);

    if (!is_array($decoded)) {
      $this->logger->warning('AdminSync: Invalid config, resetting to empty array', [
        'key' => $key,
        'value' => $value
      ]);
      return [];
    }

    // Normalize values (trim + remove empty)
    return array_values(array_filter(array_map('trim', $decoded)));
  }
}
