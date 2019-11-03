<?php
    include(dirname(__FILE__) . '/TestBot.php');
    $bot = new TestBot();
    while(true) {
        try {
            $bot->Check();
        } catch (Exception $e) {
            echo (string)$e;
        }
    }
?>                           
