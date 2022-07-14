<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/26
 * Time: 5:24 PM
 */

return [
    'network_interface' => env('BPM_NETWORK_INTERFACE', 'http'),
    'app_key' => env('BPM_APP_KEY', ''),
    'app_secret' => env('BPM_APP_SECRET', ''),
    'engine_api' => env('BPM_ENGINE_URL', ''),
    'notify_url' => env('BPM_NOTIFY_URL', ''),
    'ding_alarm_url' => env('BPM_DING_ALARM_URL', '')
];
