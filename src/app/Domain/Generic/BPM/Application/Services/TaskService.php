<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/14
 * Time: 6:02 PM
 */

namespace App\Domain\Generic\BPM\Application\Services;

use App\Domain\Generic\BPM\Models\BPMTransaction;
//use App\Domain\Generic\User\Services\UserService; //已移除
use Exception;

/**
 * 任务相关服务
 */
class TaskService extends BaseService
{
    /**
     * 审批通过
     *
     * @param string $taskId
     * @param int $userId
     * @param array $formData
     * @param string $remark
     * @param array $copyIds
     * @return array
     * @throws Exception
     */
    public function agreeTodoTask(string $taskId, int $userId, array $formData = [], string $remark = '', array $copyIds = []): array
    {
        $this->preHandleTodoTask($taskId, $userId, $nextUserVars, $user,$formData,$copyIds);
        return $this->engine->agreeTodoTask($taskId, $user, $remark, ['variables' => $nextUserVars, 'formDataMap' => $formData], [], $copyIds);
    }


    /**
     * 被驳回重新提交
     *
     * @param string $taskId
     * @param int $userId
     * @param array $formData
     * @param string $remark
     * @param array $copyIds
     * @return array
     * @throws Exception
     */
    public function resubmitTodoTask(string $taskId, int $userId, array $formData = [], string $remark = '', array $copyIds = []): array
    {
        $this->preHandleTodoTask($taskId,$userId,$nextUserVars,$user,$formData,$copyIds);
        return $this->engine->resubmitTodoTask($taskId, $user, $formData, $remark, [], $copyIds);
    }


    /**
     * 审批拒绝
     *
     * @param string $taskId
     * @param int $userId
     * @param array $formData
     * @param string $remark
     * @param array $copyIds
     * @return array
     * @throws Exception
     */
    public function refuseTodoTask(string $taskId, int $userId, array $formData = [], string $remark = '',array $copyIds = []): array
    {
        $this->preHandleTodoTask($taskId, $userId, $nextUserVars, $user, $formData,$copyIds);
        return $this->engine->refuseTodoTask($taskId, $user, $remark, ['variables' => $nextUserVars, 'formDataMap' => $formData], [], $copyIds);
    }


    /**
     * 待办任务操作前置预处理
     *
     * @param $taskId
     * @param $userId
     * @param $nextUserVars
     * @param $user
     * @param $formData
     * @param $copyIds
     * @return void
     * @throws Exception
     */
    private function preHandleTodoTask($taskId, $userId, &$nextUserVars, &$user, &$formData, &$copyIds)
    {
        $result = $this->engine->getProcessRuntimeVar($taskId);
        $nodeId = $result['data']['nodeId'] ?? '';
        $processInstanceId = $result['data']['processInstanceId'] ?? '';
        $nextRoleVars = $result['data']['nextRoleVars'] ?? [];
        $copyRoleVar = $result['data']['copyRoleVar'] ?? [];
        $bpmTransaction = BPMTransaction::query()->whereTransactionNo($processInstanceId)->first();
        if (!$bpmTransaction) {
            abort(500,'本地审批交易数据不存在，请检查数据完整性！');
        }
        $nextUserVars = [];
        if ($nextRoleVars){
            $nextUserVars = $this->getNextUserVariables($nextRoleVars, $bpmTransaction->store_id, $bpmTransaction->area_id);
        }
        if($copyRoleVar){
            $copyIds = array_unique(
                array_merge(
                    $copyIds,
                    $this->getNextUserVariables($copyRoleVar, $bpmTransaction->store_id, $bpmTransaction->area_id)['copyIds'] ?? []
                )
            );
        }

        //$user = app(UserService::class)->getUserWithRoleDepartment($userId); // 已移除依赖的用户服务
        $this->handleForm($bpmTransaction, $nodeId, $formData);
    }


}
