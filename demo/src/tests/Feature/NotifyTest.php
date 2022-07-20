<?php

namespace Tests\Feature;

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
        $response = $this->post('/api/bpm/notify',[
            'dealResult' => 'agree',
            'dealUserId' => '666',
            'hasFinished' => 1,
            'nextTasks' =>[],
            'processInstanceId' => 'mock_processInstanceId_007_002',
            'processInstanceName' => 'XX申请',
            'remark' => '1781审批通过',
            'startUserId' => '2',
            'taskId' => '111111',
            'taskName' => '总经理审批',
        ]);

        $response->assertStatus(200);
    }
}
