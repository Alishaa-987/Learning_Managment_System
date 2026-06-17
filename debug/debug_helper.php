<?php
// Debug helper
function logDebug($msg) {
    file_put_contents('debug_log.txt', date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}
?>
