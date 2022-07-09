<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/8
 * Time: 5:14 PM
 */

namespace App\Domain\Generic\BPM\Application\DTO;

use App\Domain\Generic\BPM\Models\BPMTransaction;

/**
 * 审批回调收口梳理后，统一的所有参数列表
 */
class CallbackDTO
{
    /**
     * @var string 流程实例id
     */
    public string $processInstanceId;

    /**
     * @var string 流程实例名
     */
    public string $processInstanceName;

    /**
     * @var string 流程发起人id
     */
    public string $startUserId;

    /**
     * @var int 是否完成
     */
    public int $hasFinished;

    /**
     * @var string 任务处理人id
     */
    public string $dealUserId;

    /**
     * @var string 任务处理结果
     */
    public string $dealResult;

    /**
     * @var string 处理时间｜任务被处理的真实时间
     */
    public string $dealTime;

    /**
     * @var array 表单数据Map数组，
     * @desc 数组的key为bpm中台已配置的表单属性字段，业务侧基于已知规则按需获取和处理。
     */
    public array $formDataMap = [];

    /**
     * @var string 备注
     */
    public string $remark;

    /**
     * @var BPMTransaction|array
     * 可用属性参照
     * @see BPMTransaction::$fillable
     */
    public BPMTransaction|array $bpmTransaction;

}
