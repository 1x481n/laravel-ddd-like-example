<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 20¥2/6/6
 * Time: 10:11 AM
 */

declare(strict_types=1);

namespace App\Domain\Generic\BPM\Domain\Gateway;


use App\Models\UserAdmin;

class HttpClient extends Gateway
{
    /**
     * 获取bpm令牌
     *
     * @return array
     */
    public function getAccessToken(): array
    {
        return $this->post('/bpm/token/get', [
            'appKey' => config('bpm.app_key'),
            'appSecret' => config('bpm.app_secret'),
        ]);
    }

    /**
     * 发布流程后可获取的流程变量
     *
     * @param string $processDefinitionId 流程定义id
     * @return array
     */
    public function getProcessVars(string $processDefinitionId): array
    {
        return $this->get('/bpm/process/var/get', ['processDefinitionId' => $processDefinitionId]);
    }



    /**
     * 流程开启时所需表单
     *
     * @param string $processUniqueCode 流程定义id
     * @return array
     */
    public function getProcessStartForm(string $processUniqueCode): array
    {
        return $this->get('/bpm/form/start/get', ['processUniqueCode' => $processUniqueCode]);
    }

    /**
     * 流程开启时所需表单（聚合了基于taskId查询流程与节点标识）
     *
     * @param string $taskId 任务id
     * @return array
     */
    public function getProcessRuntimeForm(string $taskId): array
    {
        return $this->get('/bpm/form/runtime/get', ['taskId' => $taskId]);
    }

    /**
     * 获取流程开启时下一变量
     * @desc 流程发起时，依赖的接口
     *
     * @param string $processUniqueCode 流程模板code
     * @return array
     */
    public function getProcessStartVar(string $processUniqueCode): array
    {
        return $this->get('/bpm/process/start/var', ['processUniqueCode' => $processUniqueCode]);
    }

    /**
     * 获取流程运行时下一节点变量
     * @desc 审批通过时需要提前调用，进行角色转人
     *
     * @param string $taskId
     * @return array
     */
    public function getProcessRuntimeVar(string $taskId): array
    {
        return $this->get('/bpm/process/run/var', ['taskId' => $taskId]);
    }



    /**
     * 获取流程发起时表单
     * @desc 二次过滤给前端，客户填写表单内容
     *
     * @param string $processUniqueCode 流程模板code
     * @return array
     */
    public function getProcessStartFrom(string $processUniqueCode): array
    {
        return $this->get('/bpm/form/start/get', ['processUniqueCode' => $processUniqueCode]);
    }



    /**
     * 流程发起
     *
     * @param string $processUniqueCode
     * @param array $startUser
     * [
     *  'startUserId'=>,
     *  'startUserName'=>,
     *  'startGroupIds'=>,
     *  'startGroupNames'=>,
     *  'startDeptId'=>,
     *  'startDeptName'=>,
     * ]
     * @param array $formVars 表单+变量
     * @param array $ext 扩展参数
     * @param array $copyIds
     * @param bool $executeFirstNode 默认执行首节点
     * @return array
     */
    public function startProcess(string $processUniqueCode, array $startUser, array $formVars = [], array $ext = [], array $copyIds=[], bool $executeFirstNode = true): array
    {
        return $this->withRuntime(
            array_merge($formVars, $ext)
        )->post('/bpm/process/start',
            [
                'processUniqueCode' => $processUniqueCode,
                'startUserId' => (string)$startUser['startUserId'],
                'startUserName' => (string)$startUser['startUserName'],
                'startGroupIds' => (string)$startUser['startGroupIds'],
                'startGroupNames' => (string)$startUser['startGroupNames'],
                'startDeptId' => (string)$startUser['startDeptId'],
                'startDeptName' => (string)$startUser['startDeptName'],
                'copyIds' => $copyIds,
                'executeFirstNode' => $executeFirstNode,
            ], ['retry' => 3]
        );
    }

    /**
     * （被驳回到发起人）重新提交
     *
     * @param string $taskId
     * @param UserAdmin $startUser
     * @param array $formVars
     * @param string $remark
     * @param array $ext
     * @param array $copyIds
     * @return array
     *
     */
    public function resubmitTodoTask(string $taskId, UserAdmin $startUser, array $formVars = [], string $remark = '', array $ext = [],array $copyIds = []): array
    {
        return $this->withRuntime(
            array_merge($formVars, $ext)
        )->post('/bpm/task/todo/resubmit',
            [
                'taskId' => $taskId,
                'userId' => (string)$startUser->id,
                'userName' => (string)$startUser->nickname,
                'userGroups' => (string)$startUser->currentRole->id,
                'deptId' => (string)$startUser->currentRole->department->id,
                'remark' => $remark,
                'copyIds' => $copyIds,
            ]
        );
    }

    /**
     * 审批通过
     *
     * @param string $taskId
     * @param UserAdmin $operator
     * @param string $remark
     * @param array $formVars
     * @param array $ext
     * @param array $copyIds
     * @return array
     */
    public function agreeTodoTask(string $taskId, UserAdmin $operator, string $remark = '', array $formVars = [], array $ext = [], array $copyIds = []): array
    {
        return $this->withRuntime(
            array_merge($formVars, $ext)
        )->post(
            '/bpm/task/todo/agree',
            [
                'taskId' => $taskId,
                'userId' => $operator->id,
                'userName' => $operator->nickname,
                'userGroups' => $operator->currentRole->name,
                'deptId' => $operator->currentRole->department->id,
                'remark' => $remark,
                'copyIds' => $copyIds
            ]
        );
    }



    /**
     * 审批拒绝
     *
     * @param string $taskId
     * @param UserAdmin $operator
     * @param string $remark
     * @param array $formVars
     * @param array $ext
     * @param array $copyIds
     * @return array
     */
    public function refuseTodoTask(string $taskId, UserAdmin $operator, string $remark = '', array $formVars = [], array $ext = [], array $copyIds = []): array
    {
        return $this->withRuntime(
            array_merge($formVars, $ext)
        )->post('/bpm/task/todo/refuse',
            [
                'taskId' => $taskId,
                'userId' => $operator->id,
                'userName' => $operator->nickname,
                'userGroups' => $operator->currentRole->name,
                'deptId'=> $operator->currentRole->department->id,
                'remark' => $remark,
                'copyIds' => $copyIds,
            ]
        );
    }



    // 已移所有部分代码

    // ......
    // ......
    // ......


}
