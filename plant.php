<?php
session_start();

// إعداد الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "plant";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// إضافة منتج إلى السلة
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $product_id;
    echo "<script>alert('تمت إضافة المنتج إلى السلة!');</script>";
}

// حذف منتج من السلة
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    if (($key = array_search($remove_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
    }
}

// إتمام الشراء
if (isset($_GET['checkout'])) {
    echo "<h2>إتمام الشراء:</h2>";
    if (!empty($_SESSION['cart'])) {
        echo "تمت عملية الشراء بنجاح!<br>إجمالي المبلغ: " . calculateTotal($conn) . " ريال.";
        unset($_SESSION['cart']); // إفراغ السلة بعد الشراء
    } else {
        echo "السلة فارغة.";
    }
    exit();
}

// دالة حساب إجمالي السعر
function calculateTotal($conn) {
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        $ids = implode(",", $_SESSION['cart']);
        $sql = "SELECT price FROM products WHERE id IN ($ids)";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $total += $row['price'];
        }
    }
    return $total;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجر النباتات</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; text-align: center; }
        .container { max-width: 800px; margin: auto; }
        .product, .cart-item { border: 1px solid #ddd; margin: 10px; padding: 10px; display: inline-block; width: 200px; }
        .product img, .cart-item img { width: 100%; height: auto; }
        button { background-color: green; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background-color: darkgreen; }
        .cart-container { margin-top: 30px; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌿 مرحباً بكم في متجر النباتات 🌿</h1>

        <h2>المنتجات المتاحة:</h2>
        <?php
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "
                <div class='product'>
                    <img src='" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "' />
                    <h3>" . htmlspecialchars($row['name']) . "</h3>
                    <p>" . htmlspecialchars($row['description']) . "</p>
                    <p>السعر: " . htmlspecialchars($row['price']) . " ريال</p>
                    <form method='POST'>
                        <input type='hidden' name='product_id' value='" . $row['id'] . "' />
                        <button type='submit' name='add_to_cart'>إضافة إلى السلة 🛒</button>
                    </form>
                </div>
                ";
            }
        } else {
            echo "<p>لا توجد منتجات متاحة حالياً.</p>";
        }
        ?>

        <div class="cart-container">
            <h2>🛒 سلة المشتريات</h2>
            <?php
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                echo "<p>السلة فارغة.</p>";
            } else {
                $cart_items = $_SESSION['cart'];
                $ids = implode(",", $cart_items);
                $sql = "SELECT * FROM products WHERE id IN ($ids)";
                $result = $conn->query($sql);
                $total = 0;

                while ($row = $result->fetch_assoc()) {
                    echo "
                    <div class='cart-item'>
                        <img src='" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "' />
                        <h3>" . htmlspecialchars($row['name']) . "</h3>
                        <p>السعر: " . htmlspecialchars($row['price']) . " ريال</p>
                        <a href='?remove=" . $row['id'] . "'>❌ إزالة</a>
                    </div>
                    ";
                    $total += $row['price'];
                }

                echo "<h3>إجمالي المبلغ: $total ريال</h3>";
                echo "<a href='?checkout' style='display: inline-block; padding: 10px; background: blue; color: white;'>إتمام الشراء</a>";
            }
            ?>
        </div>
    </div>
</body>
</html>
