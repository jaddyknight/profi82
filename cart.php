<?php
require 'config.php';

// Обработка действий
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'add') {
        addToCart($_GET['zakaz'], urldecode($_GET['name']), floatval($_GET['price']));
        header('Location: /cart.php'); exit;
    }
    if ($_GET['action'] === 'remove') {
        removeFromCart($_GET['zakaz']);
        header('Location: /cart.php'); exit;
    }
    if ($_GET['action'] === 'clear') {
        clearCart();
        header('Location: /cart.php'); exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Корзина</title>
</head>
<body>
<h2>Корзина</h2>
<?php if (!empty($_SESSION['cart'])): ?>
<table>
    <tr><th>Код</th><th>Наименование</th><th>Цена</th><th>Кол-во</th><th>Сумма</th><th>Действие</th></tr>
    <?php foreach ($_SESSION['cart'] as $i): ?>
    <tr>
        <td><?= htmlspecialchars($i['zakaz'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($i['name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= number_format($i['price'], 2, ',', ' ') ?></td>
        <td><?= intval($i['qty']) ?></td>
        <td><?= number_format($i['price'] * $i['qty'], 2, ',', ' ') ?></td>
        <td><a href="?action=remove&zakaz=<?= urlencode($i['zakaz']) ?>">Удалить</a></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="4"><strong>Итого</strong></td>
        <td><strong><?= number_format(cartTotal(), 2, ',', ' ') ?></strong></td>
        <td><a href="?action=clear">Очистить</a></td>
    </tr>
</table>

<p>
    <a href="invoice.php">Печатать накладную</a>
    &nbsp;|&nbsp;
    <a href="order.php"><button type="button">Оформить заказ</button></a>
</p>
<?php else: ?>
<p>Корзина пуста.</p>
<?php endif; ?>
<p><a href="/search.php">Назад к поиску</a></p>
</body>
</html>