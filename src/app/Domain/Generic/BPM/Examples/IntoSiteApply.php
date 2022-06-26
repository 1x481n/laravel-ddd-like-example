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

class IntoSiteApply extends SourceHandler implements ShouldValidateInputForm, WithContextFormMap, ShouldSpecifyStore
{
    protected int $intoStoreId;

    public function agreedAndFinished()
    {
        echo 'IntoSiteApply' . PHP_EOL;
        dump($this->callbackDTO);
    }

    public function refusedAndFinished()
    {
        echo 'refusedAndFinished' . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function validateInputForm(array $formData): void
    {
        if (empty($formData['apply_amount']) || !is_double($formData['apply_amount'])) {
            abort(500, '申请金额错误');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormFields(string $nodeId, BPMTransaction $bpmTransaction): array
    {
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
            ][$nodeId] ?? [];
    }

    public function getSpecifiedStoreId(string $nodeId, BPMTransaction $bpmTransaction): int
    {
        $intoSiteApplyId = $bpmTransaction->source_id;
        // ....

        $this->intoStoreId = ['approval_submit' => 1, 'approval_dept' => 2][$nodeId] ?? 0;

        return $this->intoStoreId;
    }

    public function getSpecifiedAreaId(string $nodeId, BPMTransaction $bpmTransaction): int
    {
        // 根据intoStoreId 查找对应区域id
        //if($this->intoStoreId){
        //    return 1;
        //}
        // 或者不需要区域id，影响到审批角色转人时，查出用户集合的大小
        return 0;
    }
}

