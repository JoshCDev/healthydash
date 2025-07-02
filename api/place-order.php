<?php
// Public endpoint for placing orders
// This file is accessible as /place-order.php

// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Delegate to the actual place order handler
require_once __DIR__ . '/includes/place_order.php'; 