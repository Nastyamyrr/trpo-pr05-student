<?php

declare(strict_types=1);

/**
 * Сервис каталога: загружает данные из JSON и работает с объектами Product / Category.
 * HTML сюда не попадает — только данные и бизнес-логика.
 */
final class CatalogService
{
    /** @var Product[] */
    private array $products = [];

    /** @var Category[] */
    private array $categories = [];

    public function __construct(
        private string $productsPath,
        private string $categoriesPath
    ) {
        $this->loadCategories();
        $this->loadProducts();
    }

    private function loadCategories(): void
    {
        if (!is_readable($this->categoriesPath)) {
            throw new RuntimeException('Файл categories.json не найден или недоступен: ' . $this->categoriesPath);
        }

        $raw = file_get_contents($this->categoriesPath);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new RuntimeException('Некорректный JSON в categories.json');
        }

        foreach ($data as $row) {
            $this->categories[] = new Category(
                (int) $row['id'],
                (string) $row['name']
            );
        }
    }

    private function loadProducts(): void
    {
        if (!is_readable($this->productsPath)) {
            throw new RuntimeException('Файл products.json не найден или недоступен: ' . $this->productsPath);
        }

        $raw = file_get_contents($this->productsPath);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new RuntimeException('Некорректный JSON в products.json');
        }

        foreach ($data as $row) {
            $this->products[] = new Product(
                (int) $row['id'],
                (string) $row['name'],
                (float) $row['price'],
                (int) $row['category_id'],
                (bool) ($row['active'] ?? true)
            );
        }
    }

    /** @return Product[] */
    public function getAllProducts(): array
    {
        return $this->products;
    }

    /** @return Category[] */
    public function getAllCategories(): array
    {
        return $this->categories;
    }

    public function findCategoryById(int $id): ?Category
    {
        foreach ($this->categories as $category) {
            if ($category->getId() === $id) {
                return $category;
            }
        }
        return null;
    }

    /**
     * Фильтрация по категории. Если $categoryId === null или 0 — возвращаем все.
     * @return Product[]
     */
    public function filterByCategory(?int $categoryId): array
    {
        if ($categoryId === null || $categoryId === 0) {
            return $this->products;
        }

        return array_values(array_filter(
            $this->products,
            fn(Product $p) => $p->belongsToCategory($categoryId)
        ));
    }

    /**
     * Фильтр: только активные товары (опционально + категория).
     * @return Product[]
     */
    public function filterActive(?int $categoryId = null): array
    {
        $list = $this->filterByCategory($categoryId);
        return array_values(array_filter($list, fn(Product $p) => $p->isActive()));
    }

    /** Средняя цена по переданному списку товаров */
    public function averagePrice(array $products): float
    {
        if (count($products) === 0) {
            return 0.0;
        }

        $sum = array_reduce(
            $products,
            fn(float $acc, Product $p) => $acc + $p->getPrice(),
            0.0
        );

        return round($sum / count($products), 2);
    }

    /** Количество товаров */
    public function count(array $products): int
    {
        return count($products);
    }

    /** Суммарная стоимость */
    public function totalPrice(array $products): float
    {
        return array_reduce(
            $products,
            fn(float $acc, Product $p) => $acc + $p->getPrice(),
            0.0
        );
    }

    /** Название категории по id (для вывода) */
    public function categoryName(int $categoryId): string
    {
        $cat = $this->findCategoryById($categoryId);
        return $cat ? $cat->getName() : 'Без категории';
    }
}
