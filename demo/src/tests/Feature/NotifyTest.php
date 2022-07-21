<?php

namespace Tests\Feature;

use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NotifyTest extends TestCase
{
    /**
     * A basic feature test example.
     * 注意新版的laravel使用artisan生成的测试用例，将使用下划线命名取代小驼峰，似乎是新标准
     * @link https://github.com/laravel/laravel/pull/5574
     * @link https://github.com/alexeymezenin/laravel-best-practices/issues/28
     *
     * @return void
     */
    public function test_bpm_notify()
    {
        $bpmSecret = config('bpm.app_secret');
        $input = [
            'dealResult' => 'agree',
            'dealUserId' => '666',
            'hasFinished' => 1,
            'nextTasks' => [],
            'processInstanceId' => 'mock_processInstanceId_007_002',
            'processInstanceName' => 'XX申请',
            'remark' => '1781审批通过',
            'startUserId' => '2',
            'taskId' => '111111',
            'taskName' => '总经理审批',
            'formDataMap' => [
                'count' => 2,
                'apply_id' => 1,
            ],
        ];
        $sortedInput = Arr::sortRecursive($input);

        $input['timestamp'] = time();
        $input['sign'] = hash('sha256', $bpmSecret . json_encode($sortedInput, JSON_UNESCAPED_UNICODE));

        $response = $this->post('/api/bpm/notify', $input);

        dump($response->getContent());

        $response->assertStatus(200);
    }
}
