<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Model/Category.php';
require_once __DIR__ . '/../src/Service/CatalogService.php';

$dataDir = __DIR__ . '/../data';
$productsPath = $dataDir . '/products.json';
$categoriesPath = $dataDir . '/categories.json';

$errorMessage = null;
$catalog = null;
$products = [];
$categories = [];
$selectedCategoryId = 0;
$showOnlyActive = false;
$avgPrice = 0.0;
$totalPrice = 0.0;
$count = 0;

try {
    $catalog = new CatalogService($productsPath, $categoriesPath);
    $categories = $catalog->getAllCategories();

    // Обработка фильтра (GET)
    if (isset($_GET['category'])) {
        $selectedCategoryId = (int) $_GET['category'];
    }
    $showOnlyActive = isset($_GET['active']) && $_GET['active'] === '1';

    if ($showOnlyActive) {
        $products = $catalog->filterActive($selectedCategoryId > 0 ? $selectedCategoryId : null);
    } else {
        $products = $catalog->filterByCategory($selectedCategoryId > 0 ? $selectedCategoryId : null);
    }

    $count = $catalog->count($products);
    $avgPrice = $catalog->averagePrice($products);
    $totalPrice = $catalog->totalPrice($products);
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Практическая работа 5 — Каталог товаров (PHP ООП)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="brand">Практическая работа 5</div>
    <span>ТРПО · PHP ООП</span>
</header>

<main class="wrap">
    <section class="hero">
        <span class="pill">каталог из dataset</span>
        <h1>PHP ООП. Классы, конструкторы и модели предметной области</h1>
        <p class="muted">Данные загружаются из <code>products.json</code> и <code>categories.json</code>. Объекты Product и Category создаются через конструкторы. Фильтрация и агрегация — в CatalogService.</p>
    </section>

    <?php if ($errorMessage !== null): ?>
        <section class="alert alert-error">
            <strong>Ошибка:</strong> <?= e($errorMessage) ?>
        </section>
    <?php else: ?>

        <section class="stats grid-3">
            <div class="card">
                <b>Товаров</b>
                <span class="stat-value"><?= $count ?></span>
            </div>
            <div class="card">
                <b>Средняя цена</b>
                <span class="stat-value"><?= number_format($avgPrice, 2, '.', ' ') ?> ₽</span>
            </div>
            <div class="card">
                <b>Сумма</b>
                <span class="stat-value"><?= number_format($totalPrice, 2, '.', ' ') ?> ₽</span>
            </div>
        </section>

        <section class="filter-panel card">
            <form method="get" action="" class="filter-form">
                <div class="field">
                    <label for="category">Категория</label>
                    <select name="category" id="category">
                        <option value="0" <?= $selectedCategoryId === 0 ? 'selected' : '' ?>>Все категории</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->getId() ?>" <?= $selectedCategoryId === $cat->getId() ? 'selected' : '' ?>>
                                <?= e($cat->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field checkbox-field">
                    <label>
                        <input type="checkbox" name="active" value="1" <?= $showOnlyActive ? 'checked' : '' ?>>
                        Только активные
                    </label>
                </div>
                <button type="submit" class="btn">Применить фильтр</button>
                <a href="?" class="btn btn-secondary">Сбросить</a>
            </form>
        </section>

        <?php if ($count === 0): ?>
            <section class="alert alert-empty">
                По выбранным условиям товары не найдены.
            </section>
        <?php else: ?>
            <section class="products-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card card">
                        <div class="product-header">
                            <span class="product-id">#<?= $product->getId() ?></span>
                            <span class="pill <?= $product->isActive() ? 'pill-ok' : 'pill-off' ?>">
                                <?= e($product->statusLabel()) ?>
                            </span>
                        </div>
                        <h2 class="product-name"><?= e($product->getName()) ?></h2>
                        <p class="product-category muted">
                            <?= e($catalog->categoryName($product->getCategoryId())) ?>
                        </p>
                        <p class="product-price"><?= e($product->formattedPrice()) ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <section class="info card">
            <h3>Схема данных</h3>
            <pre class="scheme">Product → CatalogService → index.php

• Product / Category — модели предметной области (состояние + методы)
• CatalogService — загрузка JSON, фильтрация, агрегаты
• index.php — представление (HTML), без бизнес-логики внутри моделей</pre>
        </section>
    <?php endif; ?>
</main>

<footer class="wrap muted">
    Практическая работа №5 · Технологии разработки программного обеспечения
</footer>
</body>
</html>
