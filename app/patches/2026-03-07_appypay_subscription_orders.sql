-- AppyPay integration fields for subscription_orders
-- Run manually on the same DB used by /var/www/html/fatura/app/config/config.php

ALTER TABLE subscription_orders
  ADD COLUMN gateway VARCHAR(30) NULL AFTER status,
  ADD COLUMN payment_method VARCHAR(10) NULL AFTER gateway,
  ADD COLUMN merchant_transaction_id VARCHAR(80) NULL AFTER payment_method,
  ADD COLUMN gateway_charge_id VARCHAR(80) NULL AFTER merchant_transaction_id,
  ADD COLUMN gateway_status VARCHAR(40) NULL AFTER gateway_charge_id,
  ADD COLUMN gateway_response_raw MEDIUMTEXT NULL AFTER gateway_status;

CREATE INDEX idx_subscription_orders_merchant ON subscription_orders (merchant_transaction_id);
