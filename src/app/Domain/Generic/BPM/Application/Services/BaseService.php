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
use App\Domain\Generic\User\Services\UserService;
use Closure;
use Exception;
use JetBrains\PhpStorm\ArrayShape;


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
     * @desc 引入的由他人编写的外部依赖方法
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
        $roleUserAdminIds = app(UserService::class)->getUserIdsByRole($storeId, $areaId, $roleIds);
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
     * @param BPMTransaction $bpmTransaction
     * @param $nodeId
     * @param $formData
     * @return void
     * @throws Exception
     * @deprecated 迁移至Engine
     * 处理表单
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

    /**
     * TODO:考虑使用interface作为规则契约，匿名类实现interface替换闭包
     *
     * 获取定制的变量规则
     *
     * @param $nextUserVars
     * @param $copyIds
     * @return Closure[]
     */
    #[ArrayShape(['nextRoleVarsRule' => "Closure", 'copyRoleVarRule' => "Closure"])]
    protected function getVarsRules(&$nextUserVars, &$copyIds): array
    {
        return [
            'nextRoleVarsRule' => function ($var, $storeId, $areaId) use (&$nextUserVars) {
                $var = $var ?: [];
                // 审批角色转审批人
                $nextUserVars = $this->getNextUserVariables($var, $storeId, $areaId);
            },
            'copyRoleVarRule' => function ($var, $storeId, $areaId) use (&$copyIds) {
                $var = $var ?: [];
                // 抄送角色转抄送人
                $copyIds = array_unique(
                    array_merge(
                        $copyIds,
                        $this->getNextUserVariables($var, $storeId, $areaId)['copyIds'] ?? []
                    )
                );
            }
        ];
    }

}
