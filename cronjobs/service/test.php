<?php


require_once(realpath(dirname(__FILE__) . '/../base.php'));

            $fh = @fopen("/home/alex/tmp.txt", "r");

            if (!$fh) {
                continue;
            }

            $cnt = 0;
            
            while (!feof($fh)) {
                $line = fgets($fh);
                if (strpos($line, "GET / ") !== false) {
                    $cnt++;
                }
            }
            fclose($fh);

            echo $cnt;
?>
