<?php
$a = file_get_contents("d:\\z_b_request.txt");
$a = gzuncompress($a);
$a = substr($a, 2);
print_r('request--->');print_r($a);echo "\r\n";


$b = file_get_contents("d:\\z_b_response.txt");
$b = gzuncompress($b);
print_r('response--->');print_r($b);echo "\r\n";

