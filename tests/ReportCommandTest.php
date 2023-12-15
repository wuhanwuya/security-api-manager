<?php


namespace Hso\TestApi\Test;

use Hso\TestApi\Models\SecurityApiManager;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;

class ReportCommandTest extends TestCase
{
    use InteractsWithConsole;

    public function testCommandDeletesReportData()
    {
        // 运行命令
        $this->artisan('report:SecurityApiManager')
            ->assertExitCode(0); // 0 表示没有错误
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}