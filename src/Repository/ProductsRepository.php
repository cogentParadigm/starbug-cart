<?php
namespace Starbug\Cart\Repository;

use Starbug\Cart\Cart;
use Starbug\Files\ImagesInterface;
use Starbug\Db\DatabaseInterface;
use Starbug\Db\Query\EntityInterface;
use Starbug\Price\FormatterInterface;

class ProductsRepository {
  public function __construct(
    protected Cart $cart,
    protected EntityInterface $entity,
    protected DatabaseInterface $db,
    protected FormatterInterface $price,
    protected ImagesInterface $images
  ) {
  }
  public function getProducts($conditions = []) {
    $order = $this->cart->getOrder();
    if (empty($order)) {
      return [];
    }
    $lines = $this->db->query("product_lines")
      ->innerJoin("lines")->on("lines.id=product_lines.lines_id")
      ->condition("lines.orders_id", $order["id"])
      ->select("lines.*")
      ->select("product_lines.*");
    $lines->innerJoin("products")->on("products.id=product_lines.product");
    $lines->select(["sku", "width", "length", "height", "weight"], "products");
    if (!empty($conditions)) {
      $lines->conditions($conditions);
    }
    $photos = $lines->query("products_photos")
      ->innerJoin("files")->on("files.id=products_photos.photos_id")
      ->where("products_id=product_lines.product")
      ->select("GROUP_CONCAT(CONCAT(files.location, '://', files.id,'_',files.filename))");
    $lines->addSelection($photos->getQuery(), "photos");
    $lines = $lines->all();
    foreach ($lines as &$line) {
      $options = $this->db->query("product_lines_options")
        ->select(["value", "options_id.slug"], "product_lines_options")
        ->condition("product_lines_id", $line["id"])->all();
      foreach ($options as $option) {
        $line["options"][$option["slug"]] = $option["value"];
      }
      $line["total"] = $line["price"] * $line["qty"];
      $line["total_formatted"] = $this->price->format($line["total"]);
      $line["price_formatted"] = $this->price->format($line["price"]);
      $line["photos"] = empty($line["photos"]) ? [] : explode(",", $line["photos"]);
      foreach ($line["photos"] as $idx => $photo) {
        $line["photos"][$idx] = (string) $this->images->thumb($photo, ["w" => 200, "h" => 200]);
      }
    }
    return $lines;
  }
  public function addProduct($input) {
    $product = $this->getProductById(array_merge(["deleted" => "0"], $input["id"]));
    if (empty($product)) {
      $this->db->error("The product was not found.", "global", "products");
      return [];
    }
    $lines = $this->getProducts(["product" => $product["id"]]);
    $this->cart->init();
    $order = $this->cart->getOrder();
    if (empty($input["qty"])) {
      $input["qty"] = 1;
    }
    foreach ($lines as $line) {
      if (empty($line["options"]) && empty($input["options"])) {
        // Product is in cart without options and is being added without options.
        $this->entity->store("product_lines", ["id" => $line["id"], "qty" => $line["qty"] + $input["qty"]]);
        return $line;
      }
    }
    $line = [
      "orders_id" => $order["id"],
      "type" => "product_lines",
      "product" => $product["id"],
      "description" => $product["name"],
      "price" => $product["price"],
      "qty" => $input["qty"]
    ];
    $this->entity->store("product_lines", $line);
    $line['id'] = $this->db->getInsertId("product_lines");
    if (!empty($input["options"])) {
      foreach ($input["options"] as $option => $value) {
        $conditions = ["product_lines_id" => $line["id"], "options_id" => $option];
        $exists = $this->db->query("product_lines_options")->conditions($conditions)->one();
        if ($exists) {
          $this->db->store("product_lines_options", ["id" => $exists["id"], "value" => $value]);
        } else {
          $this->db->store("product_lines_options", $conditions + ["value" => $value]);
        }
      }
    }
    return $line;
  }
  protected function getProductById($conditions) {
    if (empty($conditions)) {
      return [];
    } elseif (!is_array($conditions)) {
      $conditions = ["id" => $conditions];
    }
    $query = $this->db->query('products')->conditions($conditions);
    return $query->one();
  }
}
