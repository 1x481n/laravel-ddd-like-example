<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/14
 * Time: 6:02 PM
 */

namespace App\Domain\Generic\BPM\Application\Services;

use App\Domain\Generic\BPM\Models\BPMTransaction;
use App\Domain\Generic\BPM\Services\Engine;
use App\Domain\Generic\BPM\Services\ShouldValidateInputForm;
use App\Domain\Generic\BPM\Services\WithContextFormMap;
//use App\Domain\Generic\User\Services\UserService;

/**
 * 已办任务相关服务
 */
class BaseService
{
    protected Engine $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * 获取下个节点的审核用户
     *
     * @param array $nodeRoleVars
     * @param int $storeId
     * @param int $areaId
     * @return array
     */
    protected function getNextUserVariables(array $nodeRoleVars, int $storeId, int $areaId): array
    {
        $roleIds = [];
        foreach ($nodeRoleVars as $nodeRoleVar) {
            $roleIds = array_merge($roleIds, $nodeRoleVar['userGroups'] ?? []);
        }
        $roleIds = array_filter(array_unique($roleIds));
        //$roleUserAdminIds = app(UserService::class)->getUserIdsByRole($storeId, $areaId, $roleIds); //已移除
        $nextUserVariables = [];
        foreach ($nodeRoleVars as $nodeRoleVar) {
            $varName = $nodeRoleVar['varName'] ?? '未知';
            $varType = $nodeRoleVar['varType'] ?? 'or';
            $userGroups = $nodeRoleVar['userGroups'] ?? [];
            $userAdminIds = [];
            foreach ($userGroups as $roleId) {
                $userAdminIds = array_merge($userAdminIds, $roleUserAdminIds[$roleId] ?? []);
            }
            if ($varType == 'or') {
                $nextUserVariables[$varName] = implode(',', array_unique($userAdminIds));
            } else {
                $nextUserVariables[$varName] = array_unique($userAdminIds);
            }
        }
        return $nextUserVariables;
    }

    /**
     * 处理表单
     *
     * @param BPMTransaction $bpmTransaction
     * @param $nodeId
     * @param $formData
     * @return void
     * @throws \Exception
     */
    protected function handleForm(BPMTransaction $bpmTransaction, $nodeId, &$formData)
    {
        $sourceHandler = $bpmTransaction->source_handler;

        if ($sourceHandler instanceof ShouldValidateInputForm) {
            $sourceHandler->validateInputForm($formData);
        }

        if ($sourceHandler instanceof WithContextFormMap) {
            $formData = array_merge(
                $formData, $sourceHandler->mapFormFields($nodeId, $bpmTransaction)
            );
        }
    }

}
