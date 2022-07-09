<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/8
 * Time: 5:06 PM
 */

namespace App\Domain\Generic\BPM\Examples;

use App\Domain\Generic\BPM\Models\BPMTransaction;
use App\Domain\Generic\BPM\Services\ShouldSpecifyStore;
use App\Domain\Generic\BPM\Services\ShouldValidateInputForm;
use App\Domain\Generic\BPM\Services\SourceHandler;
use App\Domain\Generic\BPM\Services\WithContextFormMap;
use Log;

/**
 * 来源具体处理程序的例子
 * 结合自己的业务使用场景按需实现的interface接口，bpm逻辑均会自动处理，符合接口契约与bpm配置规则，关注自己的业务即可。
 */
class ConcreteHandler extends SourceHandler implements ShouldValidateInputForm, WithContextFormMap, ShouldSpecifyStore
{
    protected int $bizStoreId;

    /**
     * {@inheritdoc}
     */
    public function agreedAndFinished()
    {
        echo '审批通过已经结束流程，业务侧定制的处理逻辑' . PHP_EOL;
        dump($this->callbackDTO);
    }

    /**
     * {@inheritdoc}
     */
    public function refusedAndFinished()
    {
        echo '审批拒绝且已经结束流程，业务侧定制的处理逻辑' . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function validateInputForm(array $formData): void
    {
        Log::channel('bpm')->debug(var_export([__METHOD__, 'formData' => $formData], true));

        if (empty($formData['apply_amount']) || !is_double((double)$formData['apply_amount'])) {
            abort('申请金额错误');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormFields(string $nodeId, BPMTransaction $bpmTransaction): array
    {
        Log::channel('bpm')->debug(var_export([__METHOD__, 'nodeId' => $nodeId, 'bpmTransaction' => $bpmTransaction->toArray()], true));

        $transactionSnapshot = $bpmTransaction->transaction_snapshot ?? [];
        $startUser = $transactionSnapshot['startUser'] ?? [];

        return [
                'approval_submit' => [
                    'apply_id' => $bpmTransaction->source_id
                ],
                'approval_dept' => [
                    'dept_id' => $startUser['current_role']['department']['id'] ?? ''
                ],
                'hui2' => [
                    'intoSite_field3' => '3333333333333'
                ],
                '' => [
                    'apply_id' => '001',
                ],
            ][$nodeId] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecifiedStoreId(string $nodeId, BPMTransaction $bpmTransaction, int|string $dynamicVar = null): int
    {
        $storeId = 0;

        $intoSiteApplyId = $bpmTransaction->source_id;
        // ......

        // 当前使用同台变量进行示例： $dynamicVar的值不一定是1和2，自己根据审批流程配置时，约定的含义进行适配。
        if ($dynamicVar == 1) {
            // 调出门店
            $storeId = 15;
        }

        if ($dynamicVar == 2) {
            // 调入门店
            $storeId = 26;
        }


        // 可以使用节点ID进行门店ID定制化
        //$storeId = $this->bizStoreId = ['approval_submit' => 1, 'approval_dept' => 2][$nodeId] ?? 0;

        // 或抛异常（没有查询到上下文门店时，需要中止流程）
        // ......
        // 可以进行兜底操作，没有查询到动态门店，继续使用原流程门店

        $storeId = $storeId ?: $bpmTransaction->store_id;

        Log::channel('bpm')->debug(var_export([__METHOD__, 'nodeId' => $nodeId, 'dynamicVar' => $dynamicVar, 'storeId' => $storeId], true));

        return $storeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecifiedAreaId(string $nodeId, BPMTransaction $bpmTransaction, int|string $dynamicVar = null): int
    {
        $areaId = 0;
        // 根据bizStoreId 查找对应区域id
        if ($this->bizStoreId == 15) {
            $areaId = 11;
        }

        if ($this->bizStoreId == 16) {
            $areaId = 24;
        }
        // 或者其他非基于bizStoreId获取的区域ID的逻辑
        // ....

        // 可以进行兜底操作，没有查询到动态门店，继续使用原流程门店
        $areaId = $areaId ?: $bpmTransaction->area_id;

        // 或者不需要区域id，影响到审批角色转人时，查出用户集合的大小
        // ......

        Log::channel('bpm')->debug(var_export([__METHOD__, 'nodeId' => $nodeId, 'dynamicVar' => $dynamicVar, 'areaId' => $areaId], true));

        return $areaId;
    }
}

