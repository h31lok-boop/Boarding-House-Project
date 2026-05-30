<?php
$src = file_get_contents("storage/framework/views/ba7b5a6bbbd2db507e4cc5114032fc93.php");
$toks = token_get_all($src);
$opens = []; $endif = 0;
for ($i = 0; $i < count($toks); $i++) {
    $t = $toks[$i];
    if (!is_array($t)) continue;
    if ($t[0] === T_ENDIF) { $endif++; echo "ENDIF line ".$t[2]."\n"; }
    if ($t[0] === T_IF) { echo "IF    line ".$t[2]."\n"; }
}
