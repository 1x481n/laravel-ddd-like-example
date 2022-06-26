<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/8
 * Time: 9:35 AM
 */

namespace App\Domain\Generic\BPM\Services;

use App\Domain\Generic\BPM\Application\DTO\CallbackDTO;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Log;
use Throwable;

class NotifyHandler
{
    const IS_FINISHED = 1;

    const AGREED = 'agree';

    const REFUSED = 'refuse';

    const CANCEL = 'cancel';

    const PROCESSED = 'processed';

    /**
     * @var  CallbackDTO bpm回调数据传输对象
     */
    public CallbackDTO $callbackDTO;

    /**
     * @var SourceHandler 源处理程序
     */
    private SourceHandler $sourceHandler;


    public function __construct(SourceHandler $sourceHandler, CallbackDTO $callbackDTO)
    {
        $this->sourceHandler = $sourceHandler;

        $this->callbackDTO = $callbackDTO;
    }

    /**
     * 回调主逻辑
     * 最终一致性，支持重试，整个回调链路必须做到幂等
     *
     * @return bool
     * @throws GuzzleException
     * @throws Throwable
     */
    public function handle()
    {
        try {
            $callbackDTO = $this->callbackDTO;
            $bpmTransaction = $this->callbackDTO->bpmTransaction;
            $this->callbackDTO->bpmTransaction = $bpmTransaction->toArray();

            if ($callbackDTO->hasFinished == self::IS_FINISHED) {

                $bpmTransaction->transaction_state = self::PROCESSED;
                $bpmTransaction->process_result = $this->callbackDTO->dealResult;
                $bpmTransaction->finish_at = date('Y-m-d H:i:s');
                $bpmTransaction->save();

                if ($callbackDTO->dealResult == self::AGREED) {
                    $this->sourceHandler->agreedAndFinished();
                }

                if ($callbackDTO->dealResult == self::REFUSED) {
                    $this->sourceHandler->refusedAndFinished();
                }

                if ($callbackDTO->dealResult == self::CANCEL) {
                    $this->sourceHandler->canceledAndFinished();
                }
            }

            return true;
        } catch (Throwable $e) {

            $message = implode(PHP_EOL, [
                "【Error】： bpm回调",
                "【请求参数】：" . json_encode(app(Request::class)->input(), JSON_UNESCAPED_UNICODE),
                "【异常信息】：" . mb_substr($e->getMessage(), 0, 500) . '...',
            ]);

            Log::error($message);

            app(GuzzleHttpClient::class)->post(config('app.bpm.ding_alarm_url'), [
                'json' => [
                    "msgtype" => "text",
                    "text" => ["content" => $message],
                    "at" => [
                        "atMobiles" => [],
                        "isAtAll" => false
                    ]
                ]
            ]);

            throw $e;
        }
    }

}
