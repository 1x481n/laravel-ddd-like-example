<?php

namespace Tests\Unit;

use App\Domain\Generic\BPM\Application\Services\ProcessRunningService;
use App\Domain\Generic\BPM\Examples\ConcreteHandler;
use App\Models\Business\BusinessType;
//use PHPUnit\Framework\TestCase;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Tests\TestCase;

class BPMServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws Exception|GuzzleException
     */
    public function test_start_process()
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
