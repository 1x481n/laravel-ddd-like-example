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
    public function getUserIdsByRole(int $storeId, int $areaId, array $roleIds): array
    {
        // 假数据
        return [
            18 => [1, 2, 3],
            30 => [4, 5, 6],
        ];
    }

    /**
     * 用户带部门信息
     */
    public function getUserWithRoleDepartment(int $userAdminId): object
    {
        // 假数据
        return new class {
            public object $currentRole;
            public object $department;
            public object $startUser;

            public function __construct()
            {
                $this->department = (object)[
                    "id" => 1,
                    "name" => "总裁办",
                ];
                $this->currentRole = (object)[
                    "id" => 1,
                    "name" => "超级管理员",
                    "department_id" => 1,
                    "type" => 1,
                    "department" => $this->department,
                ];
                $this->startUser = (object)[
                    "id" => 1,
                    "dingtalk_user_id" => "",
                    "user_id" => 1,
                    "store_id" => 0,
                    "role_id" => 1,
                    "dhf_area_id" => 0,
                    "identity" => 1,
                    "card_num" => "",
                    "email" => "11111111@qq.com",
                    "address" => "",
                    "user_admin_id" => 0,
                    "username" => "admin",
                    "nickname" => "admin",
                    "level" => 1,
                    "status" => 1,
                    "ban_time" => null,
                    "wechat_id" => "",
                    "is_auth" => 2,
                    "auth_id" => 0,
                    "id_card_back" => "",
                    "id_card_front" => "",
                    "gender" => 0,
                    "real_name" => "",
                    "created_at" => "2020-03-09 13:30:35",
                    "updated_at" => "2022-03-30 09:24:18",
                    "level_name" => "A",
                    "current_role" => $this->currentRole,
                ];
            }

            public function __get($key)
            {
                $data = $this->startUser;

                if ($key == 'department') {
                    $data = $this->department;
                }

                if ($key == 'currentRole') {
                    $data = $this->currentRole;
                }

                return $data->$key;
            }

            public function toArray(): array
            {
                return (array)$this->startUser;
            }
        };
    }

}
