<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/8
 * Time: 10:57 PM
 */

namespace App\Domain\Generic\BPM\Application\Middleware;


use Log;
use App\Domain\Generic\BPM\Application\DTO\CallbackDTO;
use App\Domain\Generic\BPM\Models\BPMTransaction;
use App\Domain\Generic\BPM\Services\SourceHandler;
use App\Utils\ObjectUtil;
use Closure;
use Illuminate\Http\Request;

class BeforeNotifyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->input();
        // 记录每次原始回调请求参数，便于溯源
        Log::channel('bpm')->info(json_encode(['BPM回调擎天的原始参数：' => $input], JSON_UNESCAPED_UNICODE));

        if (!$this->checkSign()) {
            abort(500,'无效的签名！');
        }

        $bpmTransaction = BPMTransaction::query()->where('transaction_no', '=', $input['processInstanceId'] ?? '')->first();

        if (!$bpmTransaction) {
            abort(500,'bpm回调失败，未找到交易号(transaction_no|processInstanceId)');
        }

        //$this->bindBPMTransaction($bpmTransaction);

        $this->bindCallbackDTO(array_merge($input, ['bpmTransaction' => $bpmTransaction]));

        $this->bindSourceHandler($bpmTransaction->source_handler);

        return $next($request);
    }

    /**
     * TODO:
     *
     * @return bool
     */
    private function checkSign(): bool
    {
        return true;
    }

    /**
     * 注入BPMTransaction数据模型实例
     *
     * @param $instance
     * @return void
     */
    public function bindBPMTransaction($instance)
    {
        app()->instance(BPMTransaction::class, $instance);
    }

    /**
     * 注入回调参数传输对象
     *
     * @param $raw
     * @return void
     */
    private function bindCallbackDTO($raw)
    {
        app()->singleton(CallbackDTO::class, function () use ($raw) {
            $callbackDTO = new CallbackDTO();
            ObjectUtil::copyFromArray($raw, $callbackDTO);
            return $callbackDTO;
        });
    }

    /**
     * 绑定具体来源事务对象并注入回调DTO
     *
     * @param SourceHandler $concrete
     * @return void
     */
    private function bindSourceHandler(SourceHandler $concrete)
    {
        app()->singleton(SourceHandler::class, function () use ($concrete) {
            $concrete->callbackDTO = app(CallbackDTO::class);
            return $concrete;
        });
    }

}
