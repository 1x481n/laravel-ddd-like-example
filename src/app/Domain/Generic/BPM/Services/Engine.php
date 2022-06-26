<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/6
 * Time: 10:11 AM
 */

declare(strict_types=1);

namespace App\Domain\Generic\BPM\Services;


use App\Domain\Generic\BPM\Models\BPMTransaction;
use Cache;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;

/* @mixin HttpClient */
class Engine
{
    public const PENDING_START = 'pending_start';

    public const PENDING_CONTINUE = 'pending_continue';

    public const PROCESSING = 'processing';

    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * 流程发起
     *
     * @param BPMTransaction $bpmTransaction
     * @return array
     * @throws \Exception
     */
    public function startProcess(BPMTransaction $bpmTransaction)
    {
        try {
            $transactionSnapshot = $bpmTransaction->transaction_snapshot ?? [];
            $startUser = $transactionSnapshot['startUser'] ?? [];
            $formData = $transactionSnapshot['formData'] ?? [];
            $variables = $transactionSnapshot['variables'] ?? [];
            $ext = $transactionSnapshot['ext'] ?? [];
            $copyIds = $transactionSnapshot['copyIds'] ?? [];
            $executeFirstNode = $transactionSnapshot['executeFirstNode'] ?? true;

            $result = $this->authorize()->startProcess(
                $bpmTransaction->process_code,
                [
                    'startUserId' => $startUser['id'] ?? '',
                    'startUserName' => $startUser['nickname'] ?? '',
                    'startGroupIds' => $startUser['current_role']['id'] ?? '',
                    'startGroupNames' => $startUser['current_role']['name'] ?? '',
                    'startDeptId' => $startUser['current_role']['department']['id'] ?? '',
                    'startDeptName' => $startUser['current_role']['department']['name'] ?? '',
                ],
                [
                    'variables' => $variables,
                    'formDataMap' => $formData
                ],
                $ext,
                $copyIds,
                $executeFirstNode
            );
            $startData['transaction_no'] = $result['data']['processInstanceId'] ?? '';
            if($startData['transaction_no']){
                $transactionState = self::PROCESSING;
            }
            return $result;
        } finally {
            $startData['transaction_state'] = $transactionState ?? self::PENDING_CONTINUE;
            $startData['bpm_trace_id'] = $this->httpClient->requestId;
            $startData['start_at'] = date('Y-m-d H:i:s');
            $bpmTransaction->fill($startData);
            $bpmTransaction->save();
        }
    }


    /**
     * 校验表单参数
     *
     * @param array $formData
     * @param string $processDefinitionId
     * @return void
     * @throws BindingResolutionException
     * @deprecated 无需使用，bpm自己校验
     */
    public function validateFormData(array $formData, string $processDefinitionId)
    {
        $data = $this->getProcessStartForm($processDefinitionId);
        $formDataSettings = $data['data']['formData'] ?? '';
        $rules = $messages = $customAttributes = [];

        foreach ($formDataSettings as $index => $setting) {
            $currentRules = [];
            (!empty($setting['required'])) && $currentRules[] = 'required';
            (!empty($setting['type'])) && $currentRules[] = $setting['type'];
            (!empty($setting['required'])) && $rules[$setting['id']] = implode('|', $currentRules);
            (!empty($setting['name'])) && $customAttributes[$setting['id']] = $setting['name'];
        }

        $validator = app('validator')->make($formData, $rules, [], $customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $name => $msgValues) {
                $messages[] = '【' . $name . '：' . implode('｜', $msgValues) . '】';
            }

            $messages = implode('', $messages);

            throw new \InvalidArgumentException('BPM流程引擎formData表单字段校验：' . $messages);
        }
    }

    /**
     * bpm授权或续租
     *
     * @return HttpClient
     * @throws \Exception
     */
    public function authorize(): HttpClient
    {
        $this->httpClient->accessToken = Cache::get('bpm:access_token');

        if (!$this->httpClient->accessToken = Cache::get('bpm:access_token')) {

            extract($this->httpClient->getAccessToken()['data'] ?? []);

            if (!isset($accessToken, $expireTime)) {
                throw new \Exception('access_token获取失败！');
            }

            $this->httpClient->accessToken = $accessToken;
            Cache::put('bpm:access_token', $accessToken, strtotime($expireTime) - time() - 3);
        }

        return $this->httpClient;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $parameters)
    {
        $this->httpClient->input = array_reduce($parameters, function ($result, $item) {
            return array_merge($result, Arr::wrap($item));
        }, []);

        if ('getAccessToken' != $method) {
            $this->authorize();
        }

        return $this->httpClient->{$method}(...$parameters);
    }

}
