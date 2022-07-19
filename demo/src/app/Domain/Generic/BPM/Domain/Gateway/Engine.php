<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/6
 * Time: 10:11 AM
 */

declare(strict_types=1);

namespace App\Domain\Generic\BPM\Domain\Gateway;


use App\Domain\Generic\BPM\Models\BPMTransaction;
use App\Domain\Generic\BPM\Domain\Interface\NetworkInterface;
use Cache;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;

/* @mixin NetworkInterface */
class Engine
{
    public const PENDING_START = 'pending_start';

    public const PENDING_CONTINUE = 'pending_continue';

    public const PROCESSING = 'processing';

    // 允许使用的变量
    private const ALLOWED_VARIABLES = [
        // 动态变量先行，偏移始终为0
        'dynamicVar',
        // 从下一个环节开始所有需要角色转人的变量
        'nextRoleVars',
        // 抄送人变量
        'copyRoleVar'
    ];

    private NetworkInterface $httpClient;

    public function __construct(NetworkInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * 流程发起
     *
     * @param BPMTransaction $bpmTransaction
     * @return array
     * @throws Exception
     */
    public function startProcess(BPMTransaction $bpmTransaction): array
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
            if ($startData['transaction_no']) {
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
     * @throws Exception
     */
    public function authorize(): NetworkInterface
    {
        if (!$this->httpClient->accessToken = (string)Cache::get('bpm:access_token')) {

            extract($this->httpClient->getAccessToken()['data'] ?? []);

            if (!isset($accessToken, $expireTime)) {
                throw new Exception('access_token获取失败！');
            }

            $this->httpClient->accessToken = $accessToken;
            Cache::put('bpm:access_token', $accessToken, strtotime($expireTime) - time() - 3);
        }

        return $this->httpClient;
    }

    /**
     * 处理表单
     *
     * @param BPMTransaction $bpmTransaction
     * @param array $varData
     * @param $formData
     * @return void
     * @throws Exception
     */
    public function handleForm(BPMTransaction $bpmTransaction, array $varData, &$formData): void
    {
        $sourceHandler = $bpmTransaction->source_handler;

        $nodeId = $varData['nodeId'] ?? '';

        if ($sourceHandler instanceof ShouldValidateInputForm) {
            $sourceHandler->validateInputForm($formData);
        }

        if ($sourceHandler instanceof WithContextFormMap) {
            $formData = array_merge(
                $formData, $sourceHandler->mapFormFields($nodeId, $bpmTransaction)
            );
        }
    }

    /**
     * 处理变量规则
     *
     * @param BPMTransaction $bpmTransaction
     * @param array $varData
     * @param array $ruleClosures
     * @return void
     */
    public function handleVariables(BPMTransaction $bpmTransaction, array $varData, array $ruleClosures): void
    {
        // 流程节点ID
        $nodeId = $varData['nodeId'] ?? '';
        // 默认取发起时指定的门店/大区
        $storeId = $bpmTransaction->store_id;
        $areaId = $bpmTransaction->area_id;
        // 来源处理程序
        $sourceHandler = $bpmTransaction->source_handler;

        // 处理允许的变量规则
        collect($varData)->sortBy(function ($varVal, $varName) {
            return array_flip(self::ALLOWED_VARIABLES)[$varName] ?? -1;
        })->each(function ($varVal, $varName) use ($bpmTransaction, $sourceHandler, $ruleClosures, $nodeId, &$storeId, &$areaId) {
            if (!in_array($varName, self::ALLOWED_VARIABLES)) {
                return;
            }
            if ($varName == self::ALLOWED_VARIABLES[0]) {
                // 流程运行中需要动态指定的门店/区域
                if ($sourceHandler instanceof ShouldSpecifyStore) {
                    $storeId = $sourceHandler->getSpecifiedStoreId($nodeId, $bpmTransaction, $varVal);
                    $areaId = $sourceHandler->getSpecifiedAreaId($nodeId, $bpmTransaction, $varVal);
                }
            } else {
                $ruleClosure = $ruleClosures[$varName . 'Rule'] ?? null;
                if ($ruleClosure instanceof \Closure) {
                    $ruleClosure($varVal, $storeId, $areaId);
                }
            }
        });
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws Exception
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

    public function __destruct()
    {
        app()->forgetInstance(NetworkInterface::class);
    }
}
