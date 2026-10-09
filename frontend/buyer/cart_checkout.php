<?php
$portal = 'buyer'; $active = 'cart_checkout'; $pageTitle = 'Cart & Checkout';
$crumb = 'Review your items and complete payment'; $userRole = 'Buyer';
include __DIR__ . '/../includes/header.php';
?>

<div id="cartEmpty" class="gp-empty" style="display:none;">
  <i class="bi bi-cart3"></i>
  <h4>Your cart is empty</h4>
  <p class="muted">Find something fresh in <a href="<?= BASE_URL ?>/buyer/browse_listings.php">Browse Produce</a>.</p>
</div>

<div class="gp-grid cols-2" style="align-items:start;" id="cartLayout">
  <div class="gp-card">
    <h3 style="margin-bottom:14px;">Your Cart <span id="cartCount" class="muted"></span></h3>
    <div id="cartGroups"><p class="muted">Loading…</p></div>
    <hr class="divider">
    <p class="muted" style="font-size:12.5px;margin:0;">Each farmer's items are placed as a separate order. Delivery is arranged after the farmer confirms your order.</p>
  </div>

  <div class="gp-card">
    <h3 style="margin-bottom:14px;">Order Summary</h3>
    <div class="section-head"><span class="muted">Subtotal</span><strong data-cart-subtotal>LKR 0</strong></div>
    <hr class="divider">
    <div class="section-head" style="font-size:18px;"><span>Total</span><strong data-cart-total style="color:var(--forest);">LKR 0</strong></div>
    <div class="muted" style="font-size:12.5px;" id="cartOrderCount"></div>

    <hr class="divider">
    <h4 style="margin-bottom:10px;">Payment Method</h4>
    <div class="radio-card-group" style="grid-template-columns:1fr;">
      <label class="radio-card selected">
        <input type="radio" name="payment" id="cardBtn" value="card" checked> <strong>Card Payment</strong> <span class="muted"></span>
      </label>
      <label class="radio-card">
        <input type="radio" name="payment" value="cash_on_delivery"> <strong>Cash on Delivery</strong>
      </label>
    </div>

    <p id="checkoutError" style="display:none;color:#c0392b;font-size:13px;margin:14px 0 0;"></p>

    <button class="btn btn-primary btn-block" style="margin-top:18px;" id="placeOrderBtn" disabled>Place Order</button>
    <p class="muted" style="text-align:center;font-size:12px;margin-top:10px;">By placing this order you agree to Govi Paura's Terms of Service.</p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
