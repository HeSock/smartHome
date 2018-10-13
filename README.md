# 渠道统计

### 功能
- 数据汇总
- 充值详情
- 各区充值

### 配置环境
- PHP >= 5.6.4
- XML PHP扩展
- 安装Composer来管理依赖


### 安装步骤
- 1、composer init // 初始化composer
  - 1、创建包名;（Package name (<vendor>/<name>) [hwp/sc_bearjoy]: bearjoycom/biyue）
  - 2、描述;（Description []: biyue composer）
  - 3、其他的默认即可
  - 4、安装插件：（Enter package # to add, or the complete package name if it is not listed: monolog/monolog）
  - 5、插件的版本；（Enter the version constraint to require (or leave blank to use the latest version): 1.21.0）
  - 6、编辑composer.json ;在require{
  -                         "monolog/monolog":"1.23.0.*" // 插入的命令
  -                     } 
  - 7、安装；composer install
  - 8、安装laravel； composer create-project --prefer-dist laravel/laravel blog（blog为文件名）
  - 9、php artisan 查看命令
  - 10、创建数据的模型以及创建要迁移的数据；php artisan make:mode detail -m
  - 11、数据迁移；php artisan migrate 
  - 12、建立数据填充文件:php artisan make:seeder JobTableSeeder
  - 13、执行引入数据：php artisan db:seed --class=JobTableSeeder（单个表的） php artisan db:seed (批量引入)
  
### 依赖
- "php": ">=5.6.4",
- "laravel/framework": "5.4.*",
- "laravel/tinker": "~1.0",
- "monolog/monolog": "^1.23",
- "predis/predis": "^1.1",
- "spatie/laravel-pjax": "^1.3"


### 文件介绍
- 定时任务（channel/app/Console）
- C 控制器 （channel/app/Http/Controllers）
- M 模型 （channel/app/Model）
- 业务逻辑 （channel/app/Service）
- 配置（channel/config）
- 数据库 （channel/database）
- 入口 （channel/public/index.php）
- V 视图 （channel/resources/views）
- 路由配置 （channel/routes/web.php）
- 依赖  （channel/vendor）
