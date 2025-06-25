<?php


namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
class ProductController extends BaseController {
    public function index(Request $request)
    {
        $allowedSortColumns = ['id', 'name', 'slug', 'order'];
        $sortBy = in_array($request->sort_by, $allowedSortColumns) ? $request->sort_by : 'order';

        $allowedOrders = ['asc', 'desc'];
        $order = in_array($request->order, $allowedOrders) ? $request->order : 'asc';

        $limit = (int) $request->input('limit', 10);

        $query = Product::query()->orderBy($sortBy, $order);

        $products = $query->paginate($limit);

        if ($products->isNotEmpty()) {
            return $this->sendResponse($products->items(), "Products listed successfully.", $products);
        } else {
            return $this->sendError("No product found", [], 404);
        }
    }
}
