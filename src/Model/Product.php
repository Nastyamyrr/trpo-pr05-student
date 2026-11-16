<?php

declare(strict_types=1);

/**
 * Модель товара предметной области.
 * Обязательные свойства задаются только через конструктор.
 */
final class Product
{
    public function __construct(
        private int $id,
        private string $name,
        private float $price,
        private int $categoryId,
        private bool $active = true
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID товара должен быть положительным целым числом.');
        }
        if (trim($name) === '') {
            throw new InvalidArgumentException('Название товара не может быть пустым.');
        }
        if ($price < 0) {
            throw new InvalidArgumentException('Цена товара не может быть отрицательной.');
        }
        if ($categoryId <= 0) {
            throw new InvalidArgumentException('ID категории должен быть положительным целым числом.');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /** Форматированная цена для вывода */
    public function formattedPrice(): string
    {
        return number_format($this->price, 2, '.', ' ') . ' ₽';
    }

    /** Статус активности текстом */
    public function statusLabel(): string
    {
        return $this->active ? 'Активен' : 'Неактивен';
    }

    /** Проверка принадлежности к категории */
    public function belongsToCategory(int $categoryId): bool
    {
        return $this->categoryId === $categoryId;
    }

    /** Принадлежит ли товар к одной из переданных категорий */
    public function belongsToAnyCategory(array $categoryIds): bool
    {
        return in_array($this->categoryId, $categoryIds, true);
    }
}
