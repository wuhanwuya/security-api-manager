<?php

namespace Hso\TestApi\Middleware;

use Closure;
use Hso\TestApi\Models\SecurityApiManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityApiManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (($response instanceof JsonResponse)) {
            try {
                $this->recordResponse($request, $response);
            } catch (\Throwable $e) {
                Log::info("error record securityApiManager", [
                    'error_msg' => $e->getMessage(),
                    'request_info' => [
                        'params' => $request->all(),
                        'path' => $request->path(),
                    ],
                    'response' => [
                        'content' => $response->getData(true)
                    ],
                ]);
            }
        }
        return $next($request);
    }

    private function getMaxResponseLength()
    {
        $maxResponseLength = empty(config('apimanger.max_response_length')) ? 100000 : config('apimanger.max_response_length');

        return min((int)$maxResponseLength, 100000);
    }

    /**
     * 返回体长度
     * @param $response
     * @return int
     */
    private function getResponseLength($response): int
    {
        $responseLength = $response->headers->get('Content-Length');
        if (!$responseLength) {
            $responseLength = strlen($response->content());
        }
        return (int)$responseLength;
    }

    /**
     * 记录返回体信息
     * @param Request $request
     * @param JsonResponse $response
     * @return void
     */
    private function recordResponse(Request $request, JsonResponse $response): void
    {
        $checkMaxResponseLengthRes = $this->checkMaxResponseLength($response);
        if (!$checkMaxResponseLengthRes) {
            return;
        }
        $checkDailyMaxReportNumRes = $this->checkDailyMaxNum($request);
        if (!$checkDailyMaxReportNumRes) {
            return;
        }
        $this->record($request, $response);
    }

    /**
     * 最大的返回长度限制
     * @param $response
     * @return  bool false表示不符合要求 true表示可以上报
     */
    private function checkMaxResponseLength($response): bool
    {
        $maxResponseLength = $this->getMaxResponseLength();
        echo $maxResponseLength . PHP_EOL;
        if ($this->getResponseLength($response) <= $maxResponseLength) {
            return true;
        }
        return false;
    }


    /**
     * 校验当日单接口上报上限
     * @param $request
     * @return bool false表示不符合要求 true表示可以上报
     */
    private function checkDailyMaxNum($request): bool
    {
        $checkType = config('apimanger.check_type') ?? 'db';
        $dailyMaxReportNum = config('apimanger.daily_max_report_num') ?? 20;
        $requestPath = $request->path();
        if ($checkType == 'db') {
            return $this->checkDailyMaxNumByDb($dailyMaxReportNum, $requestPath);
        } elseif ($checkType == 'cache') {
            return $this->checkDailyMaxNumByCache($dailyMaxReportNum, $requestPath);
        }
        return false;
    }

    /**
     * 通过缓存校验是否超过当日的上限
     * @param $dailyMaxReportNum
     * @param $requestPath
     * @return bool
     */
    private function checkDailyMaxNumByCache($dailyMaxReportNum, $requestPath): bool
    {
        $cacheKey = md5($requestPath) . '_' . date("Y-m-d");
        $repostNum = Cache::get($cacheKey);
        $repostNum = empty($repostNum) ? 0 : $repostNum;
        if ($repostNum >= $dailyMaxReportNum) {
            return false;
        }
        if(empty($repostNum)){
            //这里为了兼容是memcache的时候直接increment失效的情况，可能并发情况下导致多加数据，但问题不大
            Cache::set($cacheKey,0);
        }
        $repostNum = Cache::increment($cacheKey);
        if ($repostNum > $dailyMaxReportNum) {
            Cache::decrement($cacheKey);
            return false;
        }
        return true;
    }

    /**
     * 通过数据库校验是否超过当日的上限(有并发条件下插入超限的可能，不做考虑)
     * @param $dailyMaxReportNum
     * @param $requestPath
     * @return bool
     */
    private function checkDailyMaxNumByDb($dailyMaxReportNum, $requestPath): bool
    {
        $dailyCount = SecurityApiManager::query()
            ->where('request_path', $requestPath)
            ->where('created_time', '>=', date("Y-m-d"))
            ->where('created_time', '<=', date("Y-m-d 23:59:59"))
            ->count();
        return !($dailyCount >= $dailyMaxReportNum);
    }


    private function record(Request $request, JsonResponse $response)
    {
        $data = [
            'time_stamp' => microtime(true),
            'client_ip' => $this->getIp(),
            'service_name' => config('apimanger.service_name') ?? "",
            'domain' => $request->getSchemeAndHttpHost(),
            'method' => $request->getMethod(),
            'request_path' => $request->path(),
            'request_info' => json_encode([
                'params' => $request->all(),
                'header' => $this->getHeaders($request),
            ], JSON_UNESCAPED_UNICODE),
            'response_length' => $this->getResponseLength($response),
            'response_info' => json_encode([
                'code' => "true",
                'header' => $response->headers->get('Content-Type') ?? "application/json;charset=utf-8",
                'content' => $response->getData(true)
            ], JSON_UNESCAPED_UNICODE),
            'hash_code' => md5(microtime(true) . "_" . mt_rand(10000, 99999) . uniqid()),
            'sync_status' => 0,
        ];
        SecurityApiManager::query()->insert($data);
    }

    private function getHeaders($request)
    {
        $headers = $request->headers->all();
        return http_build_query($headers, '', ';');
    }


    private function getIp()
    {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "";
        }
        return $ip;
    }

}