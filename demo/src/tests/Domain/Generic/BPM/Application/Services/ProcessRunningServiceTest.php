<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/9
 * Time: 5:43 PM
 */

namespace Tests\Domain\Generic\BPM\Application\Services;

use App\Domain\Generic\BPM\Application\Services\ProcessRunningService;
use App\Domain\Generic\BPM\Examples\ConcreteHandler;
use App\Models\Business\BusinessType;
use Exception;
use Tests\TestCase;

class ProcessRunningServiceTest extends TestCase
{

    /**
     * @return void
     * @throws Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testStartProcess()
    {
        $result = app(ProcessRunningService::class)->startProcess(
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
            'enjoy yourself!'
        );

        $this->assertArrayHasKey('bpm_transaction_sn', $result,'发起流程测试用例运行失败！');
    }
}
