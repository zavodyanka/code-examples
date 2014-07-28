<?php

switch ($type) {
    case '1':
    case '2':
    case '5':
    case '4':
    case '6': $options['period'] = 'week'; break;
    case '3':
    case '7': 
    case '8': $options['period'] = 'day'; break;
}

$start_handle = time();

if (count($options)) {

}
