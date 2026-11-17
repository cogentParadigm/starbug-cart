<?php
namespace Starbug\Cart\Operation;

use Starbug\Db\DatabaseInterface;
use Starbug\Bundle\BundleInterface;
use Starbug\Cart\Repository\ProductsRepository;
use Starbug\Db\Operation\Save;

class AddProduct extends Save {
  protected $model = "products";
  protected $line;
  public function __construct(
    DatabaseInterface $db,
    protected ProductsRepository $products
  ) {
    parent::__construct($db);
  }
  public function handle(array $data, BundleInterface $state): BundleInterface {
    $this->line = $this->products->addProduct($data);
    return $this->getErrorState($state);
  }
  public function getLine() {
    return $this->line;
  }
}
