<?php

/**
 * Демонстрация проверки некорректного конструктора Product.
 * Запуск: php docs/invalid-constructor-demo.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/Model/Product.php';

echo "=== Демо: InvalidArgumentException в конструкторе Product ===\n\n";

$cases = [
    'Отрицательная цена' => fn() => new Product(1, 'Тест', -10.0, 1),
    'Пустое имя'         => fn() => new Product(1, '   ', 100.0, 1),
    'Нулевой ID'         => fn() => new Product(0, 'Тест', 100.0, 1),
    'Невалидная категория' => fn() => new Product(1, 'Тест', 100.0, 0),
];

foreach ($cases as $title => $factory) {
    echo "— $title: ";
    try {
        $factory();
        echo "ОШИБКА: исключение не выброшено\n";
    } catch (InvalidArgumentException $e) {
        echo "OK → " . $e->getMessage() . "\n";
    }
}

echo "\n— Корректное создание: ";
try {
    $p = new Product(42, 'Клавиатура', 3500.0, 2, true);
    echo "OK → " . $p->getName() . ', ' . $p->formattedPrice() . "\n";
} catch (Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

echo "\nГотово.\n";
