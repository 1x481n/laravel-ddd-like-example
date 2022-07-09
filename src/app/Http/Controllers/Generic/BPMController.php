<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/06/01
 * Time: 03:30 下午
 */

namespace App\Http\Controllers\Generic;


use App\Domain\Generic\BPM\Application\Services\TaskService;
use App\Domain\Generic\BPM\Services\NotifyHandler;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use stdClass;
use Throwable;


class BPMController extends Controller
{

    public function test(): string
    {
        //dispatch(function (){
        //    Log::debug('bpm.test');
        //});
        return 'bpm.test';
    }

    /**
     * 回调用通知
     *
     * @param NotifyHandler $notifyHandler
     * @return string|void
     * @throws Throwable
     */
    public function notify(NotifyHandler $notifyHandler)
    {
        if ($notifyHandler->handle()) {
            return 'success';
        }
        abort(500, '回调失败！');
    }

    /**
     * 审批通过
     *
     * @param $taskId
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function agreeTodoTask($taskId, Request $request): JsonResponse
    {
        $remark = (string)$request->input('remark', '');
        $formData = (array)$request->input('formDataMap', []);
        $copyIds = (array)$request->input('copyIds', []);
        // 替换为中间件，基于access_token转换为userId
        $userId = (int)$request->input('userId', 0);

        return $this->response(
            app(TaskService::class)->agreeTodoTask(
                $taskId, $userId, $formData, $remark, $copyIds
            )
        );
    }

    /**
     * @param $taskId
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function refuseTodoTask($taskId, Request $request): JsonResponse
    {
        $remark = (string)$request->input('remark', '');
        $formData = (array)$request->input('formDataMap', []);
        $copyIds = (array)$request->input('copyIds', []);
        $userId = (int)$request->input('userId', 0);

        return $this->response(
            app(TaskService::class)->refuseTodoTask(
                $taskId, $userId, $formData, $remark, $copyIds
            )
        );
    }

    /**
     * 被驳回重新提交
     *
     * @param $taskId
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function resubmitTodoTask($taskId, Request $request): JsonResponse
    {
        $remark = (string)$request->input('remark', '');
        $formData = (array)$request->input('formDataMap', []);
        $copyIds = (array)$request->input('copyIds', []);
        $userId = (int)$request->input('userId', 0);

        return $this->response(
            app(TaskService::class)->resubmitTodoTask(
                $taskId, $userId, $formData, $remark, $copyIds
            )
        );
    }


    // 已移除部分代码
    //  ......
    //  ......
    //  ......

    /**
     *
     * 已替换定制响应部分逻辑
     *
     * @param $bpmResponse
     * @return JsonResponse
     */
    private function response($bpmResponse): JsonResponse
    {
        $code = $bpmResponse['code'] ?? -1;
        $data = $bpmResponse['data'] ?? new stdClass();
        $bpmMessage = $bpmResponse['message'] ?? '';
        if ($code == 0) {
            return response()->json($data);
        } else {
            $bpmMessage = '：' . ($bpmMessage ?: 'bpm服务未知错误！');
            abort(500, '操作失败' . $bpmMessage);
        }
    }
}
