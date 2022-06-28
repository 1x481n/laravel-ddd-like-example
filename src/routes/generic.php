<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/5/31
 * Time: 3:08 PM
 */

use App\Domain\Generic\BPM\Application\Middleware\BeforeNotifyMiddleware;
use App\Domain\Generic\BPM\Application\Middleware\BeforeHandleTaskMiddleware;
use Illuminate\Support\Facades\Route;


// 业务流程管理
Route::group(['prefix' => 'bpm', 'namespace' => '\\App\\Http\\Controllers\\Generic'], function () {
    Route::get('/test', 'BPMController@test');

    // BPM回调通知
    Route::post('notify', ['uses' => 'BPMController@notify', 'middleware' => BeforeNotifyMiddleware::class, 'as' => 'bpm.notify']);

    Route::group(['middleware' => 'admin_auth'], function () {

        Route::group(['prefix' => 'todo-task/{taskId}', 'middleware' => BeforeHandleTaskMiddleware::class], function () {
            // 审批通过
            Route::put('agree', 'BPMController@agreeTodoTask');

            // 审批拒绝
            Route::put('refuse', 'BPMController@refuseTodoTask');

            // 重新提交
            Route::put('resubmit', 'BPMController@resubmitTodoTask');

            // 移除部分路由
            // ......
        });

        // 移除部分路由
        // ......
    });

});





