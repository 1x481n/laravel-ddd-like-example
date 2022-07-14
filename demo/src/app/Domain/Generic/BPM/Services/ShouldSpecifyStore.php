<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/22
 * Time: 9:01 PM
 */

namespace App\Domain\Generic\BPM\Services;

use App\Domain\Generic\BPM\Models\BPMTransaction;

/**
 * 流程中，获取每个节点需要特别指定的门店（比如：调入门店、调出门店）
 */
interface ShouldSpecifyStore
{
    /**
     * 获取指定门店ID
     *
     * @param string $nodeId 流程节点ID
     * @param BPMTransaction $bpmTransaction 本地bpm交易模型，可结合source_id等字段，作逻辑处理
     * @param integer|string|null $dynamicVar 动态变量
     * @return int 返回当前nodeId下，需要特殊指定的门店id，可结合已经约定并配置的动态变量和自己场景上下文生成。
     */
    public function getSpecifiedStoreId(string $nodeId, BPMTransaction $bpmTransaction, int|string $dynamicVar = null): int;

    /**
     * 获取指定区域ID
     *
     * @param string $nodeId 流程节点ID
     * @param BPMTransaction $bpmTransaction 本地bpm交易模型，可结合source_id等字段，作逻辑处理
     * @param integer|string|null $dynamicVar 动态变量
     * @return int 返回当前nodeId下，需要特殊指定的区域id，可结合已经约定并配置的动态变量和自己场景上下文生成。
     */
    public function getSpecifiedAreaId(string $nodeId, BPMTransaction $bpmTransaction, int|string $dynamicVar = null): int;
}
