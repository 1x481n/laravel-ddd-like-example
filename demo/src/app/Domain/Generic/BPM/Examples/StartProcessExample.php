<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/4
 * Time: 09:35 AM
 */

namespace App\Domain\Generic\BPM\Examples;

use App\Domain\Generic\BPM\Application\Services\ProcessRunningService;
use App\Models\Business\BusinessType;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

/**
 * 业务侧发起审批流程调用示例
 */
class StartProcessExample
{
    /**
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['bpm_transaction_sn' => "\Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed|string", 'bpm_result' => "array"])]
    public function startProcess(): array
    {
        return app(ProcessRunningService::class)->startProcess(
            BusinessType::find(1),
            app(ConcreteHandler::class),
            1,
            1,
            '111111111111',
            11,
            11,
            [
                //'apply_id'=>1,
                'apply_amount' => '6000.00'
            ],
            ['ext1' => '扩展1'],
            '地振高冈'
        );
    }
}
