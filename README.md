# php_IDWork
PHP实现的twitter-snowflake算法

# 用法

```
require 'IDWork.php';

$workid = 1; // 机器标识
$IDWor = new IDWork($workid);
$IDWork -> nextId();
```
