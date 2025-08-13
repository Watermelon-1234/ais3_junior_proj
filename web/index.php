<?php
    $data = file_get_contents($_GET['file']);
    echo "File contents: $data";