<?php
namespace OCA\AdminSync\Settings;

use OCP\Settings\ISettings;
use OCP\AppFramework\Http\TemplateResponse;

class Admin implements ISettings {

  public function getForm() {
    return new TemplateResponse('adminsync', 'settings');
  }

  public function getSection() {
    return 'security';
  }

  public function getPriority() {
    return 50;
  }
}
