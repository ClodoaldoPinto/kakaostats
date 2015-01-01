<?php

function cor_donor ($pontos7) {

    if ($pontos7 >= 700000) $cor7 = "c1";
    elseif ($pontos7 >= 350000) $cor7 = "c2";
    elseif ($pontos7 >= 70000) $cor7 = "c3";
    elseif ($pontos7 >= 35000) $cor7 = "c4";
    elseif ($pontos7 >= 7000) $cor7 = "c5";
    elseif ($pontos7 >= 700) $cor7 = "c6";
    elseif ($pontos7 >= 1) $cor7 = "c7";
    else $cor7 = "c8";
    return $cor7;
}

function cor_time ($pontos7) {

    if ($pontos7 >= 10000000) $cor7 = "c1";
    elseif ($pontos7 >= 5000000) $cor7 = "c2";
    elseif ($pontos7 >= 1000000) $cor7 = "c3";
    elseif ($pontos7 >= 500000) $cor7 = "c4";
    elseif ($pontos7 >= 100000) $cor7 = "c5";
    elseif ($pontos7 >= 50000) $cor7 = "c6";
    elseif ($pontos7 >= 1) $cor7 = "c7";
    else $cor7 = "c8";
    return $cor7;
}
?>