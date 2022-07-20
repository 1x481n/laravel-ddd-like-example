<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/6
 * Time: 10:11 AM
 */

declare(strict_types=1);

namespace App\Domain\Generic\BPM\Domain\Driver;


use App\Domain\Generic\BPM\Domain\Interface\NetworkInterface;
use BadMethodCallException;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Utils;
use Log;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;
use App\Utils\UUID;
use function app;
use function config;


/**
 * @method get(string $path, array $params = [], array $options = [])
 * @method post(string $path, array $params = [], array $options = [])
 * @method put(string $path, array $params = [], array $options = [])
 * @method delete(string $path, array $params = [], array $options = [])
 */
abstract class Gateway implements NetworkInterface
{
    /**
     * @var string
     */
    public string $requestId;

    /**
     * @var string
     */
    public string $accessToken;

    /**
     * @var array
     */
    public array $input = [];

    // 默认重试次数
    protected int $retry = 1;
    // 默认超时时间(s)
    protected int $timeout = 2;
    // 默认重试间隔(s)
    protected int $sleep = 1;

    /**
     * @var array
     */
    private array $payload = [];

    /**
     * @var array
     */
    private array $request = [];

    /**
     * @var array
     */
    private array $response = [];

    /**
     * @var array
     */
    private array $httpMessage = [];

    /**
     * @var array
     */
    private array $alarmMessage = [];

    /**
     * @var ResponseInterface $psrResponse
     */
    private ResponseInterface $psrResponse;


    /**
     * 携带表单变量作为请求参数
     *
     * @param array $input
     * @return $this
     */
    public function withFormVars(array $input = []): self
    {
        $input && $this->input = $input;
        $this->payload = array_merge($this->payload, [
            'formDataMap' => (object)($this->input['formDataMap'] ?? []), // 表单
            'variables' => (object)($this->input['variables'] ?? []),   // 变量
        ]);
        return $this;
    }

    /**
     * 携带回调地址作为请求参数
     *
     * @param string $callbackUrl
     *
     * @return $this
     */
    public function withCallback(string $callbackUrl = ''): self
    {
        $callbackUrl && $this->input['callbackUrl'] = $callbackUrl;

        $this->payload = array_merge($this->payload, [
            'callbackUrl' => $this->input['callbackUrl'] ?? config('app.bpm.notify_url'),  // 回调通知url
        ]);
        return $this;
    }

    /**
     * 携带预留扩展项作为请求参数
     *
     * @param array $input
     * @return $this
     */
    public function withExtends(array $input = []): self
    {
        $input && $this->input = $input;

        $this->payload = array_merge($this->payload, [
            'ext1' => $this->input['ext1'] ?? [], // 扩展1
            'ext2' => $this->input['ext2'] ?? [], // 扩展2
            'ext3' => $this->input['ext3'] ?? [], // 扩展3
        ]);
        return $this;
    }

    /**
     * 携带所有流程运行时所需的参数项作为请求参数
     *
     * @param array $input
     * @return $this
     */
    public function withRuntime(array $input = []): self
    {
        $input && $this->input = $input;

        return $this->withFormVars()->withExtends();
    }

    /**
     * 发送请求
     *
     * @param string $method
     * @param string $path
     * @param array $params
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function request(string $method, string $path, array $params = [], array $options = []): array
    {
        try {
            $this->requestId = UUID::v4() . '-' . round(microtime(true) * 1000);
            $this->retry = $options['retry'] ?? $this->retry;
            $this->timeout = $options['timeout'] ?? $this->timeout;
            $this->sleep = $options['sleep'] ?? $this->sleep;

            $this->buffer('start');

            $this->payload = array_merge($this->payload, $params, ['bpm_trace_id' => $this->requestId]);


            if (!$inputType = (['post' => 'json', 'get' => 'query', 'delete' => 'query', 'put' => 'json'][strtolower($method)] ?? '')) {
                throw new BadMethodCallException('不支持的请求方式');
            }

            $url = config('bpm.engine_api') . $path;

            $headers = [
                'access_token' => $this->accessToken,
            ];

            $this->request = ['method' => $method, 'url' => $url, 'headers' => $headers, 'payload' => $this->payload];

            $this->buffer('request');

            retry(
                $this->retry,
                function () use ($method, $url, $headers, $inputType) {
                    $this->psrResponse = app(GuzzleHttpClient::class)->{strtolower($method)}($url, [
                        'headers' => $headers,
                        'timeout' => $this->timeout,
                        $inputType => $this->payload,
                    ]);
                },
                $this->sleep,
                function (Throwable $e) {
                    return $e instanceof ConnectException;
                }
            );

            $this->response = [
                'statusCode' => $this->psrResponse->getStatusCode(),
                'statusPhrase' => $this->psrResponse->getReasonPhrase(),
                'headers' => $this->psrResponse->getHeaders(),
                'content' => $this->psrResponse->getBody()->getContents()
            ];
            $this->buffer('response');
            return $this->parse();
        } catch (Exception $e) {
            $message = '未知错误,请稍后再试！';
            if ($e instanceof RequestException) {
                $message = '工作流接口请求异常，请稍后！';
            }
            if ($e instanceof ConnectException) {
                $message = 'bpm工作流接口连接异常[已重试：' . $this->retry . '次]，请稍后再试！';
            }
            if ($e instanceof ClientException) {
                $message = 'bpm工作流接口调用异常，请稍后！';
            }
            $this->buffer('exception', $e->getMessage() . '|' . $e->getTraceAsString());
            abort(500, $message);
        }
    }

    /**
     * 解析响应内容
     *
     * @return array
     */
    public function parse(): array
    {
        if (200 != $this->psrResponse->getStatusCode() ||
            false === stripos($this->psrResponse->getHeaderLine('Content-Type'), 'application/json')
        ) {
            $msg = sprintf('java二方bpm服务未正确响应:非200状态码或非json响应内容类型，bpm_trace_id === %s', $this->requestId);
            $this->alarmMessage[] = $msg;
            throw new RuntimeException($msg);
        }

        $data = json_decode($this->response['content'] ?? '', true);

        if (array_diff(['code', 'data'], array_keys($data))) {
            $msg = sprintf('java二方bpm服务未正确响应：响应格式异常，code或data不正确, bpm_trace_id === %s', $this->requestId);
            $this->alarmMessage[] = $msg;
            throw new RuntimeException($msg);
        }

        return $data;
    }

    /**
     * 暴露当前已缓存的http报文
     *
     * @return array
     */
    public function getHttpMessage(): array
    {
        return $this->httpMessage;
    }

    /**
     * @param $method
     * @param $parameters
     * @return array
     * @throws Throwable
     */
    public function __call($method, $parameters)
    {
        if (in_array(strtolower($method), ['get', 'post', 'delete', 'put'])) {
            return $this->request($method, ...$parameters);
        }

        throw new BadMethodCallException('不支持的方法名[' . $method . ']！');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(PHP_EOL, $this->httpMessage);
    }

    /**
     * @throws Throwable
     */
    public function __destruct()
    {
        $this->terminate();
    }

    /**
     * 请求内容作为日志格式化
     *
     * @param array $data
     * @return string
     */
    public function requestLogFormat(array $data): string
    {
        return implode(PHP_EOL, array_map(
                function ($v, $k) {
                    $v = match (true) {
                        is_array($v) && array_is_list($v) => implode(',', $v),
                        is_array($v) && !array_is_list($v), is_object($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                        is_bool($v) => $v ? 'true' : 'false',
                        default => $v,
                    };
                    return sprintf("%s: %s", $k, $v);
                },
                $data,
                array_keys($data)
            )
        );
    }

    /**
     *  缓冲
     *
     * @param string $step
     * @param string $message
     * @return void
     */
    private function buffer(string $step = 'start', string $message = ''): void
    {
        if ($step == 'start') {
            $this->httpMessage[] = sprintf("\n====== start: activiti-http-service bpm_trace_id[%s] ======", $this->requestId);
            return;
        }

        if ($step == 'request') {
            $method = strtoupper($this->request['method'] ?? '');
            $url = $this->request['url'] ?? '';
            $headers = $this->requestLogFormat($this->request['headers'] ?? []);
            $payload = $this->requestLogFormat($this->request['payload'] ?? []);
            $this->httpMessage[] = sprintf("\n=== request ===\nmethod: %s\nurl: %s\n== headers ==\n%s\n== payload ==\n%s",
                $method, $url, $headers, $payload
            );
            return;
        }

        if ($step == 'exception') {
            $this->httpMessage[] = sprintf("\n=== exception[retry_times:%d] === \n%s ======",
                $this->retry, $message
            );
            $this->alarmMessage[] = sprintf("【java二方bpm服务调用异常】: \n=== CONTEXT ===\n %s \n=== ERROR ===\n %s",
                'request:' . json_encode($this->request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                $message
            );
            return;
        }

        if ($step == 'response') {
            $statusCode = $this->response['statusCode'] ?? '';
            $statusPhrase = $this->response['statusPhrase'] ?? '';
            $responseHeaders = $this->requestLogFormat($this->response['headers'] ?? []);
            $content = $this->response['content'] ?? '';
            $this->httpMessage[] = sprintf(
                "\n=== response ===\nstatus: %s %s\n== headers ==\n%s\n== content ==\n%s\n",
                $statusCode, $statusPhrase, $responseHeaders, $content
            );
            $this->httpMessage[] = sprintf("====== end: activiti-http-service bpm_trace_id[%s] ======\n",
                $this->requestId
            );
        }
    }

    /**
     * 刷新缓冲
     *
     * @return void
     */
    private function flush(): void
    {
        Log::channel('bpm')->info($this);
    }

    /**
     * 告警
     *
     * @return void
     * @throws Throwable
     */
    private function alarm(): void
    {
        $promises = [];
        while ($message = array_shift($this->alarmMessage)) {
            $promises[] = app(GuzzleHttpClient::class)->postAsync(config('bpm.ding_alarm_url'), [
                'json' => [
                    "msgtype" => "text",
                    "text" => [
                        "content" => mb_substr($message, 0, 1500)
                    ],
                    "at" => [
                        "atMobiles" => [],
                        "isAtAll" => false
                    ]
                ]
            ]);
        }
        $promises && Utils::unwrap($promises);
    }

    /**
     * 终止
     *
     * @return void
     * @throws Throwable
     */
    private function terminate(): void
    {
        $this->flush();
        $this->alarm();
    }
}
