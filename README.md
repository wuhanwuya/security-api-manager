<h1 align="center"> http </h1>

<p align="center"> 对接安全中心资产上报，适用laravel框架.</p>


## Installing

```shell
$ composer require dxy/security-api-manger -vvv
```

```sql
CREATE TABLE `security_api_manager` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `time_stamp` bigint NOT NULL DEFAULT '0' COMMENT '时间戳',
  `client_ip` varchar(100)  NOT NULL DEFAULT '' COMMENT 'ip',
  `service_name` varchar(50)  NOT NULL DEFAULT '' COMMENT '服务名称（pk里的值）',
  `domain` varchar(100)  NOT NULL DEFAULT '' COMMENT '域名',
  `method` varchar(50)  NOT NULL DEFAULT '' COMMENT '请求方式',
  `request_path` varchar(250)  NOT NULL DEFAULT '' COMMENT '请求urlpath',
  `request_info` mediumtext  COMMENT '请求信息（包含param和header）',
  `response_length` int NOT NULL DEFAULT '0' COMMENT '响应体长度',
  `response_info` mediumtext  COMMENT '响应信息（包含code header content）',
  `hash_code` varchar(64)  NOT NULL DEFAULT '' COMMENT '唯一值',
  `is_sync` tinyint NOT NULL DEFAULT '0' COMMENT '是否已同步 0否 1是',
  `created_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `modified_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_is_sunc_created_time` (`is_sync`,`created_time`),
  KEY `idx_request_path_created_time` (`request_path`,`created_time`)
)  COMMENT='Api资产上报数据';
```

## Description
laravel需要额外安装的模块：cache，log


## Usage
```php


```

## License

MIT