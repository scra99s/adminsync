<?php
namespace OCA\AdminSync\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\IConfig;
use OCP\AppFramework\Http\JSONResponse;

class SettingsController extends Controller {

  private IConfig $config;

  public function __construct($appName, IRequest $request, IConfig $config) {
    parent::__construct($appName, $request);
    $this->config = $config;
  }

  /**
   * @NoAdminRequired
   */
  public function getSettings(): JSONResponse {
    return new JSONResponse([
      'admin_groups' => $this->config->getAppValue('adminsync', 'admin_groups', '[]'),
      'protected_admins' => $this->config->getAppValue('adminsync', 'protected_admins', '[]')
    ]);
  }

  /**
   * @AdminRequired
   * @CSRFRequired
   */
  public function saveSettings(string $admin_groups, string $protected_admins): JSONResponse {

    // Normalize input (comma → JSON array)
    $groups = array_map('trim', explode(',', $admin_groups));
    $users = array_map('trim', explode(',', $protected_admins));

    $this->config->setAppValue('adminsync', 'admin_groups', json_encode($groups));
    $this->config->setAppValue('adminsync', 'protected_admins', json_encode($users));

    return new JSONResponse(['status' => 'success']);
  }
}
