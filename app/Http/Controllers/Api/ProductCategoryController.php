<?php


namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\ProductCategory;
use Illuminate\Http\Request;
class ProductCategoryController extends BaseController {
    public function index(Request $request)
    {
        $allowedSortColumns = ['id', 'name', 'slug', 'order'];
        $sortBy = in_array($request->sort_by, $allowedSortColumns) ? $request->sort_by : 'order';

        $allowedOrders = ['asc', 'desc'];
        $order = in_array($request->order, $allowedOrders) ? $request->order : 'asc';

        $limit = (int) $request->input('limit', 10);

        $query = ProductCategory::query()->orderBy($sortBy, $order);
        
        $categories = $query->paginate($limit);

        if ($categories->isNotEmpty()) {
            return $this->sendResponse($categories->items(), "Product categories listed successfully.", $categories);
        } else {
            return $this->sendError("No product categories found", [], 404);
        }
    }
}
