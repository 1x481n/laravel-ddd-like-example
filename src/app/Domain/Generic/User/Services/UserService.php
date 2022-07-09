<?php
/**
 * mock
 */

namespace App\Domain\Generic\User\Services;

use App\Models\User;

/**
 * mock
 */
class UserService
{

    /**
     * 角色列表
     */
    public function roleList()
    {

    }


    /**
     * 根据角色获取用户ID
     *
     */
    public function getUserIdsByRole(int $storeId, int $areaId, array $roleIds)
    {

    }

    /**
     * 用户带部门信息
     */
    public function getUserWithRoleDepartment(int $userAdminId)
    {
        // 假数据
        $startUser = User::query()->find(1);
    }

}
