<?php
namespace Starbug\Cart\Display;

use Starbug\Display\FormDisplay;

class AddToCartForm extends FormDisplay {
  public $model = "products";
  public $submit_label = "Add to Cart";
  public $url = "/cart/add";
  protected $layoutDisplay = AddToCartFormLayout::class;
  public function buildDisplay($ops) {
    $this->attr("style", "max-width:18rem");
    $this->attr("data-dojo-type", "cart/forms/AddToCartForm");
    $this->add([
      "qty",
      "input_type" => "text",
      "data-dojo-type" => "sb/form/Number",
      "nolabel" => true,
      "nodiv" => true,
      "style" => "width:5rem",
      "pane" => "qty",
      "default" => "1",
      "class" => "bg-brand-light-yellow-gray hover-bg-brand-light-yellow-gray bn"
    ]);
    if (!empty($ops["total"])) {
      $this->add(["total", "pane" => "total", "input_type" => "html", "value" => $ops["total"]]);
    }
    $this->actions->add([
      $this->defaultAction,
      "label" => $this->submit_label,
      "class"=> "w-100"
    ]);
  }
}
