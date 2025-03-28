<?php
// Tiempo de inicio
$start = microtime(true);

// Prueba de CPU
$iterations = 1000000;
$a = 0;
for ($i = 0; $i < $iterations; $i++) {
    $a += $i;
}

// Prueba de memoria
$array = [];
for ($i = 0; $i < 10000; $i++) {
    $array[] = md5($i);
}

// Prueba de operaciones con strings
$string = '';
for ($i = 0; $i < 10000; $i++) {
    $string .= 'a';
}

// Tiempo total
$time = microtime(true) - $start;
echo "Tiempo de ejecución: " . number_format($time, 4) . " segundos\n";
echo "Memoria utilizada: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\n";
echo "PHP version: " . phpversion() . "\n";
