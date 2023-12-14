<?php


namespace Hso\TestApi\Test;

use Hso\TestApi\Models\SecurityApiManager;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;

class DeleteReportDataCommandTest extends TestCase
{
    use InteractsWithConsole;

    public function testCommandDeletesReportData()
    {
        // 添加测试数据
        SecurityApiManager::query()->insert(
            ['created_time' => now()->subDays(8)]
        );

        // 运行命令
        $this->artisan('report:DeleteReportData')
            ->expectsOutput('----删除了1行数据-----')
            ->assertExitCode(0); // 0 表示没有错误
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}