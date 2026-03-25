<?php
declare(strict_types=1);
namespace OCA\AdminSync\Service;

use OCP\IGroupManager;
use OCP\ILogger;

class AdminSyncService {

    private IGroupManager $groupManager;
    private ILogger $logger;

    public function __construct(IGroupManager $groupManager, ILogger $logger) {
        $this->groupManager = $groupManager;
        $this->logger = $logger;
    }

    public function syncUser($user, array $adminGroups, array $protectedAdmins): void {
        $uid = $user->getUID();
        $userGroups = $this->groupManager->getUserGroupIds($user);
        $adminGroup = $this->groupManager->get('admin');
        if (!$adminGroup) {
            $this->logger->error('AdminSync: Admin group not found');
            return;
        }

        $isAdmin = $adminGroup->inGroup($user);
        $isProtected = in_array($uid, $protectedAdmins);
        $isInAdminGroup = count(array_intersect($adminGroups, $userGroups)) > 0;

        if ($isInAdminGroup && !$isAdmin) {
            $adminGroup->addUser($user);
            $this->logger->info('AdminSync: Added user to admin', ['user' => $uid]);
        }
        if (!$isInAdminGroup && $isAdmin && !$isProtected) {
            $adminGroup->removeUser($user);
            $this->logger->warning('AdminSync: Removed user from admin', ['user' => $uid]);
        }

        $this->logger->debug('AdminSync: Login sync completed', [
            'user' => $uid,
            'userGroups' => $userGroups,
            'adminGroups' => $adminGroups,
            'isProtected' => $isProtected
        ]);
    }
}
