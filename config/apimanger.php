<?php
return [
    //上报的url
    "report_url" => "https://datasafe-test.dxy.net/admin-api/skywalking/forward",
    //校验单日上报上限的方式，cache-使用缓存需要支持(Cache)，db(使用数据库-不建议)
    "check_type" => 'db',
    //单个接口单日最大次数
    "daily_max_report_num" => 20,
    //最大响应的长度，最大10万
    "max_response_length" => 100000,
    //单次上报最大次数
    "once_report_num" => 500,
    //取pk的serviceName
    "service_name" => "",
    //清理的周期 （xxx天之后把之前数据库数据清理掉）
    'clear_day' => 7
];