<?php

/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/19
 * Time: 3:44 PM
 */

namespace App\Domain\Generic\BPM\Domain\Exception;

use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Http\Request;
use Log;
use Throwable;

class NotifyException extends \RuntimeException
{
    /**
     * @throws Throwable
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        Log::channel('bpm')->error($previous);

        app(GuzzleHttpClient::class)->post(config('bpm.ding_alarm_url'), [
            'json' => [
                "msgtype" => "text",
                "text" => [
                    "content" => implode(PHP_EOL, [
                        "【异常类型】： NotifyException",
                        "【请求参数】：" . json_encode(
                            app(Request::class)->input(),
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                        ),
                        "【异常信息】：" . mb_substr((string)$previous, 0, 1000) . '...',
                    ])
                ],
                "at" => [
                    "atMobiles" => [],
                    "isAtAll" => false
                ]
            ]
        ]);

        parent::__construct($message, $code, $previous);

        throw $previous;
    }


}
