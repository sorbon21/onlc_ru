<?php

require_once 'Discount.php';
$goods = [
        ['name' => 'A', 'price' => 10],
        ['name' => 'B', 'price' => 11],
        ['name' => 'C', 'price' => 12],
        ['name' => 'D', 'price' => 13],
        ['name' => 'E', 'price' => 14],
        ['name' => 'F', 'price' => 15],
        ['name' => 'G', 'price' => 16],
        ['name' => 'H', 'price' => 12],
        ['name' => 'I', 'price' => 11],
        ['name' => 'J', 'price' => 12],
        ['name' => 'M', 'price' => 12],
        ['name' => 'A', 'price' => 12],
    ];

    $discount = new Discount($goods);
    $discount->calculate('couple', ['A', 'B'], 10);
    $discount->calculate('couple', ['D', 'E'], 6);
    $discount->calculate('couple', ['E', 'F','G'], 3);
    $discount->calculate('oneof', ['A', ['K', 'L', 'M']], 5);
    $discount->calculate('together', [3, ['A', 'C']], 5);
    $discount->calculate('together', [4, ['A', 'C']], 10);
    $discount->calculate('together', [5, ['A', 'C']], 20);
    echo '<pre>';
    print_r($discount->goods);
    echo '</pre>';
    echo 'Сумма заказа: '.$discount->getOrderSumm().' Руб.';
