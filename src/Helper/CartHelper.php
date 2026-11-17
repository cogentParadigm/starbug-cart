<?php
namespace Starbug\Cart\Helper;

use Starbug\Cart\Cart;

class CartHelper {
  public function __construct(Cart $cart) {
    $this->target = $cart;
  }
  public function helper() {
    return $this->target;
  }
}
