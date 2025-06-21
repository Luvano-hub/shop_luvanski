<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "shop_luvanski");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//Yohh this line below fixed the error i had for 4 days!!!!
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

// Fetch user's cart
$cart = [];
$cart_query = $conn->prepare("SELECT c.*, p.name, p.price, p.seller_id FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$result = $cart_query->get_result();
while ($row = $result->fetch_assoc()) {
    $cart[] = [
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'seller_id' => $row['seller_id']
    ];
}
$cart_query->close();

if (empty($cart)) {
    header("Location: cart.php?error=empty_cart");
    exit();
}

// Current step in the checkout process
$current_step = isset($_POST['step']) ? intval($_POST['step']) : 1;

// Process form submissions for each step
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1 → Step 2: Save delivery details
    if ($current_step === 2 && isset($_POST['delivery_method'])) {
        $_SESSION['delivery'] = [
            'method' => $_POST['delivery_method'],
            'street' => trim($_POST['street'] ?? ''),
            'suburb' => trim($_POST['suburb'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'speed' => $_POST['speed'] ?? '',
            'date' => $_POST['delivery_date'] ?? '',
        ];
    }

    // Step 2 → Step 3: Save payment info
    if ($current_step === 3 && isset($_POST['card_name'])) {
        $_SESSION['payment'] = [
            'card_name' => trim($_POST['card_name']),
            'card_number' => preg_replace('/\D/', '', $_POST['card_number']),
            'expiry' => $_POST['expiry'],
            'cvv' => preg_replace('/\D/', '', $_POST['cvv']),
        ];
    }

    // Final confirmation and order placement
    if (isset($_POST['confirm_order'])) {
        $delivery = $_SESSION['delivery'] ?? [];
        $payment = $_SESSION['payment'] ?? [];

        if (empty($delivery) || empty($payment)) {
            header("Location: checkout.php?error=missing_info");
            exit();
        }

        // Calculate totals
        $total_amount = 0;
        foreach ($cart as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        $delivery_fee = ($delivery['method'] === 'courier' && $delivery['speed'] === 'fast') ? 90 : 
                        (($delivery['method'] === 'courier') ? 50 : 0);
        $grand_total = $total_amount + $delivery_fee;

        $address = ($delivery['method'] === 'courier')
            ? "{$delivery['street']}, {$delivery['suburb']}, {$delivery['city']}, {$delivery['postal_code']}"
            : 'Collection';

        $card_last4 = substr($payment['card_number'], -4);

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, delivery_method, address, speed, delivery_date, total_amount, card_last4, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issssds", $user_id, $delivery['method'], $address, $delivery['speed'], $delivery['date'], $grand_total, $card_last4);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert order items (CRITICAL FIX HERE)
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, seller_id) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($cart as $item) {
            // Changed from "iisiid" to "iisidi" to match data types
            $stmt_item->bind_param("iisidi", 
                $order_id, 
                $item['product_id'], 
                $item['name'], 
                $item['quantity'], 
                $item['price'], 
                $item['seller_id']
            );
            $stmt_item->execute();
        }
        $stmt_item->close();

        // Clear cart
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        $clear_stmt->close();

        // Clear session checkout data
        unset($_SESSION['delivery'], $_SESSION['payment']);

        // Show success animation and redirect
        ?>
        
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Order Successful</title>
            <style>
                .success-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.95);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                    animation: fadeOut 1.5s ease-in forwards 2s;
                }
                .success-message {
                    text-align: center;
                    transform: translateY(-50%);
                }
                .checkmark {
                    font-size: 4rem;
                    color: #4CAF50;
                    margin-bottom: 1rem;
                    animation: checkmark 0.5s ease-in-out;
                }
                @keyframes checkmark {
                    from { transform: scale(0); }
                    to { transform: scale(1); }
                }
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            </style>
        </head>
        <body>
            <div class="success-overlay">
                <div class="success-message">
                    <div class="checkmark">✓</div>
                    <h2>Order Placed Successfully!</h2>
                    <p>Redirecting to dashboard...</p>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'buyer_dashboard.php';
                }, 3000);
            </script>
        </body>
        </html>
        <?php
        exit();
    }
}

// Calculate minimum delivery date: next Wednesday or later, skipping weekends
$min_delivery_date = date('Y-m-d', strtotime('next Wednesday'));
function is_weekend($date) {
    $day = date('N', strtotime($date));
    return ($day >= 6); // 6 = Saturday, 7 = Sunday
}
while (is_weekend($min_delivery_date)) {
    $min_delivery_date = date('Y-m-d', strtotime($min_delivery_date . ' +1 day'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            color: #333;
        }
        .checkout-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h2 {
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #4CAF50;
        }
        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="month"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .button-row {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .button {
            padding: 12px 25px;
            font-size: 1rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
        }
        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .progress-bar div {
            flex: 1;
            text-align: center;
            padding: 10px 0;
            border-bottom: 3px solid #ccc;
            color: #aaa;
            font-weight: bold;
            user-select: none;
        }
        .progress-bar div.active {
            border-color: #4CAF50;
            color: #4CAF50;
        }
    </style>
</head>
<body>
<div class="checkout-container">
    <div class="progress-bar">
        <div class="<?= $current_step == 1 ? 'active' : '' ?>">1. Delivery</div>
        <div class="<?= $current_step == 2 ? 'active' : '' ?>">2. Payment</div>
        <div class="<?= $current_step == 3 ? 'active' : '' ?>">3. Confirmation</div>
    </div>

    <?php if ($current_step == 1): ?>
        <h2>Step 1: Choose Delivery Method</h2>
        <form method="POST" novalidate>
            <label for="delivery_method">Delivery Method</label>
            <select id="delivery_method" name="delivery_method" required onchange="toggleDeliveryFields(this.value)">
                <option value="">--Select--</option>
                <option value="courier" <?= (isset($_SESSION['delivery']['method']) && $_SESSION['delivery']['method'] === 'courier') ? 'selected' : '' ?>>Courier Delivery</option>
                <option value="collection" <?= (isset($_SESSION['delivery']['method']) && $_SESSION['delivery']['method'] === 'collection') ? 'selected' : '' ?>>Collection</option>
            </select>

            <div id="courierFields" style="display: <?= (isset($_SESSION['delivery']['method']) && $_SESSION['delivery']['method'] === 'courier') ? 'block' : 'none' ?>;">
                <label for="street">Street</label>
                <input type="text" id="street" name="street" value="<?= htmlspecialchars($_SESSION['delivery']['street'] ?? '') ?>" required />

                <label for="suburb">Suburb</label>
                <input type="text" id="suburb" name="suburb" value="<?= htmlspecialchars($_SESSION['delivery']['suburb'] ?? '') ?>" required />

                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?= htmlspecialchars($_SESSION['delivery']['city'] ?? '') ?>" required />

                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($_SESSION['delivery']['postal_code'] ?? '') ?>" required />
            </div>

            <label for="speed">Delivery Speed</label>
            <select id="speed" name="speed" required>
                <option value="">--Select--</option>
                <option value="fast" <?= (isset($_SESSION['delivery']['speed']) && $_SESSION['delivery']['speed'] === 'fast') ? 'selected' : '' ?>>Fast Delivery (R90)</option>
                <option value="normal" <?= (isset($_SESSION['delivery']['speed']) && $_SESSION['delivery']['speed'] === 'normal') ? 'selected' : '' ?>>Normal Delivery (R50)</option>
            </select>

            <label for="delivery_date">Delivery Date</label>
            <input type="date" id="delivery_date" name="delivery_date" min="<?= $min_delivery_date ?>"
                value="<?= htmlspecialchars($_SESSION['delivery']['date'] ?? $min_delivery_date) ?>"
                required
            />

            <input type="hidden" name="step" value="2" />
            <div class="button-row">
                <div></div> <!-- no back button on step 1 -->
                <button type="submit" class="button">Next: Payment</button>
            </div>
        </form>

    <?php elseif ($current_step == 2): ?>
        <h2>Step 2: Payment Information</h2>
        <form method="POST" novalidate>
            <label for="card_name">Cardholder Name</label>
            <input type="text" id="card_name" name="card_name" required
                value="<?= htmlspecialchars($_SESSION['payment']['card_name'] ?? '') ?>" />

            <label for="card_number">Card Number</label>
            <input type="text" id="card_number" name="card_number" pattern="\d{13,19}" maxlength="19" required
                placeholder="Only numbers, no spaces"
                value="<?= htmlspecialchars($_SESSION['payment']['card_number'] ?? '') ?>" />

            <label for="expiry">Expiry Date</label>
            <input type="month" id="expiry" name="expiry" required
                value="<?= htmlspecialchars($_SESSION['payment']['expiry'] ?? '') ?>" />

            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" pattern="\d{3,4}" maxlength="4" required
                value="<?= htmlspecialchars($_SESSION['payment']['cvv'] ?? '') ?>" />

            <input type="hidden" name="step" value="3" />
            <div class="button-row">
                <button type="submit" name="back" formaction="checkout.php" formmethod="post" value="1" class="button" style="background:#ccc;color:#333;">Back</button>
                <button type="submit" class="button">Next: Confirm</button>
            </div>
        </form>

    <?php elseif ($current_step == 3): ?>
        <h2>Step 3: Confirm Order</h2>
        <h3>Delivery Details</h3>
        <p><strong>Method:</strong> <?= htmlspecialchars(ucfirst($_SESSION['delivery']['method'] ?? '')) ?></p>
        <?php if (isset($_SESSION['delivery']['method']) && $_SESSION['delivery']['method'] === 'courier'): ?>
            <p><strong>Address:</strong>
                <?= htmlspecialchars($_SESSION['delivery']['street'] ?? '') ?>,
                <?= htmlspecialchars($_SESSION['delivery']['suburb'] ?? '') ?>,
                <?= htmlspecialchars($_SESSION['delivery']['city'] ?? '') ?>,
                <?= htmlspecialchars($_SESSION['delivery']['postal_code'] ?? '') ?>
            </p>
        <?php else: ?>
            <p><strong>Pickup Location:</strong> Store Collection</p>
        <?php endif; ?>
        <p><strong>Delivery Speed:</strong> <?= htmlspecialchars(ucfirst($_SESSION['delivery']['speed'] ?? '')) ?></p>
        <p><strong>Delivery Date:</strong> <?= htmlspecialchars($_SESSION['delivery']['date'] ?? '') ?></p>

        <h3>Payment Information</h3>
        <p><strong>Cardholder Name:</strong> <?= htmlspecialchars($_SESSION['payment']['card_name'] ?? '') ?></p>
        <p><strong>Card Number:</strong> **** **** **** <?= htmlspecialchars(substr($_SESSION['payment']['card_number'] ?? '', -4)) ?></p>
        <p><strong>Expiry:</strong> <?= htmlspecialchars($_SESSION['payment']['expiry'] ?? '') ?></p>

        <h3>Order Summary</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4CAF50; color: white;">
                    <th style="padding: 8px; border: 1px solid #ddd;">Product</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">Quantity</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">Price</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_amount = 0;
                foreach ($cart as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total_amount += $subtotal;
                ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= htmlspecialchars($item['name']) ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">R<?= number_format($item['price'], 2) ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">R<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="padding: 8px; border: 1px solid #ddd; font-weight: bold; text-align: right;">Subtotal</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">R<?= number_format($total_amount, 2) ?></td>
                </tr>
                <?php
                $delivery_fee = 0;
                if ($_SESSION['delivery']['method'] === 'courier') {
                    $delivery_fee = ($_SESSION['delivery']['speed'] === 'fast') ? 90 : 50;
                }
                ?>
                <tr>
                    <td colspan="3" style="padding: 8px; border: 1px solid #ddd; font-weight: bold; text-align: right;">Delivery Fee</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">R<?= number_format($delivery_fee, 2) ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 8px; border: 1px solid #ddd; font-weight: bold; text-align: right;">Total</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">R<?= number_format($total_amount + $delivery_fee, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <form method="POST">
            <input type="hidden" name="step" value="3" />
            <div class="button-row" style="margin-top: 30px;">
                <button type="submit" name="back" formaction="checkout.php" formmethod="post" value="2" class="button" style="background:#ccc;color:#333;">Back</button>
                <button type="submit" name="confirm_order" class="button">Place Order</button>
            </div>
        </form>

    <?php endif; ?>
</div>

<script>
function toggleDeliveryFields(value) {
    var courierFields = document.getElementById('courierFields');
    if (value === 'courier') {
        courierFields.style.display = 'block';
    } else {
        courierFields.style.display = 'none';
    }
}

// Handle back buttons (post back to step)
document.querySelectorAll('button[name="back"]').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const targetStep = this.value;
        const form = this.closest('form');
        const inputStep = form.querySelector('input[name="step"]');
        inputStep.value = targetStep;
        form.submit();
    });
});
</script>
</body>
</html>