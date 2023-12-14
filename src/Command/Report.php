<?php

namespace Hso\TestApi\Command;

use Hso\TestApi\Models\SecurityApiManager;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class Report extends Command
{
    protected $signature = 'report:SecurityApiManager';

    protected $description = '上报安全中心相关请求及响应';

    const MAX_RETRY_NUM = 5;

    public function handle()
    {
        $reportUrl = config('apimanger.report_url');
        if (empty($reportUrl)) {
            echo "----上报失败:缺少配置-----" . PHP_EOL;
            die;
        }
        $list = SecurityApiManager::query()
            ->where('sync_status', 0)
            ->where('created_time', '>=', date("Y-m-d H:i:s", time() - 86400))
            ->orderBy('id', 'asc')->limit(500)->get();
        $list = empty($list) ? [] : $list->toArray();
        $client = new Client();
        $successIds = [];
        foreach ($list as $v) {
            try {
                if ($v['retry_num'] >= self::MAX_RETRY_NUM) {
                    $this->updateToRetryMaxFail($v['id']);
                    continue;
                }
                $requestInfo = empty($v['request_info']) ? [] : json_decode($v['request_info'], true);
                $responseInfo = empty($v['response_info']) ? [] : json_decode($v['response_info'], true);
                $requestData = [
                    'jsonDataList' => [
                        json_encode([
                            'timeStamp' => $v['time_stamp'],
                            'clientIp' => $v['client_ip'],
                            'serviceName' => $v['service_name'],
                            'domain' => $v['domain'],
                            'method' => $v['method'],
                            'requestPath' => $v['request_path'],
                            'requestParam' => !empty($requestInfo['params']) ? json_encode($requestInfo['params'], JSON_UNESCAPED_UNICODE) : "",
                            'requestHeader' => $requestInfo['header'] ?? "",
                            'responseCode' => $responseInfo['code'] ?? "true",
                            'responseLength' => $v['response_length'] ?? 0,
                            'responseHeader' => $responseInfo['header'] ?? "",
                            'responseContent' => !empty($responseInfo['content']) ? json_encode($responseInfo['content'], JSON_UNESCAPED_UNICODE) : "",
                            'endpoint' => $v['hash_code']
                        ],JSON_UNESCAPED_UNICODE)
                    ]
                ];
                echo json_encode($requestData).PHP_EOL;
                $response = $client->request('POST', $reportUrl,
                    [
                        'json' => $requestData,
                        'headers' => ['Content-Type' => 'application/json']
                    ]);
                if ($response->getStatusCode() === 200) {
                    $successIds[] = $v['id'];
                } else {
                    throw new \Exception($response->getBody());
                }
            } catch (\Throwable $e) {
                $this->addRetryNum($v, $e->getMessage());
            }
        }
        $this->updateToSuccess($successIds);
    }

    private function updateToSuccess($ids)
    {
        if ($ids) {
            $successNum = SecurityApiManager::query()->whereIn('id', $ids)->update([
                'sync_status' => 1
            ]);
            $countIds = count($ids);
            echo "----上报成功:{$successNum}条,共{$countIds}条-----" . PHP_EOL;
        }
    }

    private function addRetryNum($info, $errorMsg)
    {
        $update = [
            'retry_num' => $info['retry_num'] + 1,
        ];
        if ($update['retry_num'] >= self::MAX_RETRY_NUM) {
            $update['sync_status'] = 2;
        }
        SecurityApiManager::query()->where('id', $info['id'])->update($update);
        echo "----id{$info['id']}上报失败:{$errorMsg}-----" . PHP_EOL;
    }

    private function updateToRetryMaxFail($id)
    {
        SecurityApiManager::query()->where('id', $id)->update([
            'sync_status' => 2
        ]);
        echo "===={$id}====重试次数超上限失败===" . PHP_EOL;
    }

}