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
     * @param string $nodeId
     * @param BPMTransaction $bpmTransaction
     * @return mixed
     */
    public function getSpecifiedStoreId(string $nodeId, BPMTransaction $bpmTransaction): int;

    /**
     * 获取指定区域ID
     *
     * @param string $nodeId
     * @param BPMTransaction $bpmTransaction
     * @return int
     */
    public function getSpecifiedAreaId(string $nodeId, BPMTransaction $bpmTransaction): int;
}
