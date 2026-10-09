<?php
$portal = 'buyer'; $active = 'orders'; $pageTitle = 'Card Payment';
$crumb = 'Secure checkout'; $userRole = 'Buyer';
$orderId = (int) ($_GET['id'] ?? 0);
include __DIR__ . '/../includes/header.php';
?>
<script>window._paymentOrderId = <?= $orderId ?>;</script>

<div id="paymentLoading" class="gp-empty">
	<i class="bi bi-hourglass-split"></i>
	<h4>Loading order…</h4>
</div>

<div id="paymentUnavailable" class="gp-empty" style="display:none;">
	<i class="bi bi-exclamation-circle"></i>
	<h4 id="paymentUnavailableMessage">Payment details could not be loaded.</h4>
	<a href="<?= BASE_URL ?>/buyer/orders.php" class="btn btn-outline" style="margin-top:12px;"><-</a>
</div>

<div id="paymentLayout" class="gp-grid cols-2" style="align-items:start;display:none;">
	<section class="gp-card">
		<div class="section-head" style="margin-bottom:18px;">
			<div>
				<h3 style="margin-bottom:3px;">Card details</h3>
				<p class="muted" style="margin:0;">Enter the card details for this payment.</p>
			</div>
		</div>

		<form id="paymentForm" novalidate>
			<div class="field">
				<label for="cardholderName">Name on card</label>
				<input type="text" id="cardholderName" name="cardholder_name" autocomplete="cc-name" maxlength="100" required>
			</div>
			<div class="field">
				<label for="cardNumber">Card number</label>
				<input type="text" id="cardNumber" name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="0000 0000 0000 0000" maxlength="23" required>
			</div>
			<div class="field-row">
				<div class="field">
					<label for="cardExpiry">Expiry date</label>
					<input type="text" id="cardExpiry" name="card_expiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM / YY" maxlength="7" required>
				</div>
				<div class="field">
					<label for="cardCvc">Security code</label>
					<input type="password" id="cardCvc" name="card_cvc" inputmode="numeric" autocomplete="cc-csc" placeholder="CVV" maxlength="4" required>
				</div>
			</div>
			<p id="paymentError" class="field-error" style="display:none;margin:0 0 14px;"></p>
			<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px;">
				<a href="<?= BASE_URL ?>/buyer/orders.php" class="btn btn-outline" style="flex:1;"><-</a>
				<button type="submit" class="btn btn-gold" id="payNowButton" style="flex:1;min-width:350px;">Pay Now</button>
			</div>
		</form>
	</section>

	<aside class="gp-card" aria-live="polite">
		<h3 style="margin-bottom:16px;">Order summary</h3>
		<p class="muted" style="margin-bottom:8px;">Order <strong id="paymentOrderNumber" style="color:var(--ink);"></strong></p>
		<div id="paymentItems" style="display:grid;gap:10px;margin-bottom:16px;"></div>
		<hr class="divider">
		<div class="section-head" style="font-size:18px;margin:0;">
			<span>Total due</span>
			<strong id="paymentTotal" style="color:var(--forest);"></strong>
		</div>
	</aside>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
