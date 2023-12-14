<?php

namespace Hso\TestApi\Command;

use Hso\TestApi\Models\SecurityApiManager;
use Illuminate\Console\Command;

class DeleteReportData extends Command
{
    protected $signature = 'report:DeleteReportData';

    protected $description = '定期删除上报数据';

    public function handle()
    {
        $clearDay = config('apimanger.clear_day') ?? 7;
        //最少保存3天数据
        if ($clearDay < 3) {
            $clearDay = 3;
        }
        $deleteRow = SecurityApiManager::query()
            ->where('created_time', '<=', date("Y-m-d", time() - $clearDay * 86400))
            ->delete();
        echo "----删除了{$deleteRow}行数据-----" . PHP_EOL;
    }
}