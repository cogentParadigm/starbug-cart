<?php
namespace Starbug\Cart\Display;

use Starbug\Display\LayoutDisplay;

class AddToCartFormLayout extends LayoutDisplay {
  public $template = "add-to-cart-form-layout.html";
  public function buildDisplay($options) {
    $this->put("div", "", "qty");
    $this->put("div", "", "total");
  }
}
