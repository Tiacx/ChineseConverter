中文简繁体转换
============

说明
-----

功能：简繁体中文转换

说明：提取 [sqhlib/hanzi-convert](https://github.com/uutool/hanzi-convert) 包的繁体字，并使用 [opencc](https://github.com/BYVoid/OpenCC) 做转换，在此表示感谢。

支持的转换类型：s2t、s2tw、s2hk、t2s、tw2s、hk2s

**注：当系统已安装 `opencc` 命令行工具时，优先使用 `opencc` 翻译；否则，使用内置字典翻译。**

安装
-------

安装 `opencc` 命令行工具（可省略，不安装时使用内置字典翻译）

Docker / Alpine：

```bash
RUN apk add opencc
```

其他平台：

| 系统 | 安装方式 |
|------|----------|
| Debian / Ubuntu | `apt install opencc` |
| CentOS / Fedora | `yum install opencc`（需 EPEL）或 `dnf install opencc` |
| macOS | `brew install opencc` |
| Windows | 从 [OpenCC Releases](https://github.com/BYVoid/OpenCC/releases) 下载后，将 `opencc` 所在目录加入 `PATH` |

安装本扩展

```bash
composer require tiacx/chinese-converter
```

使用
-----

简繁转换

```php
use Tiacx\ChineseConverter;

// 简体转标准繁体
echo ChineseConverter::convert('心里', 's2t'), "\n";
// 简体转台湾繁体
echo ChineseConverter::convert('心里', 's2tw'), "\n";
// 简体转香港繁体
echo ChineseConverter::convert('心里', 's2hk'), "\n";
// 标准繁体转简体
echo ChineseConverter::convert('心裏', 't2s'), "\n";
// 台湾繁体转简体
echo ChineseConverter::convert('心裡', 'tw2s'), "\n";
// 香港繁体转简体
echo ChineseConverter::convert('心裏', 'hk2s'), "\n";
```

输出：

```
心裏
心裡
心裏
心里
心里
心里
```

获取全部简繁体

```php
use Tiacx\ChineseConverter;

print_r(ChineseConverter::convertGetAll('心里'));
print_r(ChineseConverter::convertGetAll('心裡'));
print_r(ChineseConverter::convertGetAll('心裏'));
```

输出：

```
Array
(
    [0] => 心里
    [1] => 心裏
    [2] => 心裡
)
Array
(
    [0] => 心裡
    [1] => 心里
    [2] => 心裏
)
Array
(
    [0] => 心裏
    [1] => 心里
    [2] => 心裡
)
```