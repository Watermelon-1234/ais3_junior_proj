<?php
$file = $_GET["file"];
echo file_get_contents($file);

# a fictious PDF generator just like third-party library in some framework
class PDFGenerator {
    public $callback;
    public $fileName;

    function __destruct() {
        if (is_callable($this->callback)) {
            echo call_user_func($this->callback, $this->fileName);
        }
    }
}



?>