<?php

namespace Botble\Ecommerce\AdsTracking;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Location\Models\Country;
use Illuminate\Support\Facades\Log;

class MoengagePixel
{
    protected array $events = [];

    public function view(Product $product): static
    {
        $this->pushEvent('View Product', [
            'product_category' => $product->categories()->first()->name ?? '',
            'product_name' => $product->name,
            'product_id' => $product->id,
            'currency' => get_application_currency()->title,
            'value' => $product->price,
        ]);

        return $this;
    }

    public function checkout(array $items, float $value): static
    {
        $this->pushEvent('Initiate Checkout', [
            'products_count' => count($items),
            'currency' => get_application_currency()->title,
            'value' => $value,
        ]);

        return $this;
    }

    public function purchase(Order $order): static
    {
        $rawPhone = $order->address->phone ?? null;
        $countryCode = 'IQ';

        if (!empty($order->address->country)) {
            $countryModel = Country::query()
            ->where('name', $order->address->country)
            ->orWhere('id', $order->address->country)
            ->first();

            if ($countryModel && $countryModel->code) {
                $countryCode = $countryModel->code;
            }
        }

        try {
            $phone = phone($rawPhone, $countryCode)->formatE164();
        } catch (\Exception $e) {
            Log::error('Phone format failed', [
                'phone' => $rawPhone,
                'country' => $countryCode,
                'error' => $e->getMessage(),
            ]);
            $phone = '';
        }

        $products = [];

        foreach ($order->products as $item) {
            $products[] = [
                'product_name' => $item->product_name,
                'product_id' => $item->product_id,
                'price' => $item->price > 0 ? $item->price * 1 : 0,
                'quantity' => $item->qty,
                'sku' => $item->options['sku'] ?? null,
                'value' => $item->price * $item->qty,
            ];
        }

        $orderData = [
            'order_id' => str_replace('#', '', $order->code),
            'coupon_used' => $order->coupon_code != null,
            'coupon_code' => $order->coupon_code,
            'products_count' => $order->products->count('id'),
            'products_quantity' => $order->products->sum('qty'),
            'currency' => get_application_currency()->title,
            'subtotal' => $order->sub_total > 0 ? $order->sub_total * 1 : 0,
            'shipping' => $order->shipping_amount > 0 ? $order->shipping_amount * 1 : 0,
            'tax' => $order->tax_amount > 0 ? $order->tax_amount * 1 : 0,
            'discount' => $order->discount_amount > 0 ? $order->discount_amount * 1 : 0,
            'value' => $order->amount > 0 ? $order->amount * 1 : 0,
            'products' => $products,
            'customer_name' => $order->address->name,
            'customer_email' => $order->address->email,
            'customer_phone' => $phone,
            'customer_address' => $order->address->address,
            'customer_city' => $order->address->city,
            'customer_country' => $countryCode,
            'SecondaryMobileNumber' => ltrim($phone, '+'),
        ];

        $userData = [
            'name' => $order->address->name ?? '',
            'email' => $order->address->email ?? '',
            'phone' => $phone ?? '',
            'secondary_phone' => ltrim($phone, '+') ?? '',
        ];

        // Push main order event
        $this->pushEvent('Purchase - Order', $orderData);

        // Push single product-level event containing all products together
        $this->pushEvent('Purchase - Product', [
            'order_id' => str_replace('#', '', $order->code),
            'products' => $products,
            'currency' => get_application_currency()->title,
            'value' => $order->amount > 0 ? $order->amount * 1 : 0,
        ]);

        $this->pushScriptsToFooter($userData);

        return $this;
    }


    public function addToCart(Product $product, int $quantity, float $value): self
    {
        $this->pushEvent('Add To Cart', [
            'product_name' => $product->name,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'currency' => get_application_currency()->title,
            'value' => $value * $quantity,
            'category' => $product->categories()->first()->name ?? '',
        ]);

        return $this;
    }

    public function isEnabled(): bool
    {
        return get_ecommerce_setting('moengage_pixel_enabled', false) && get_ecommerce_setting('moengage_pixel_id');
    }

    public function pushEvent(string $event, array $data = []): void
    {
        $this->events[$event] = $data;
    }

    public function render(array $userData = []): string
    {
        if (empty($this->events)) {
            return '';
        }

        $content = '';
        if (!empty($userData)) {
            if (!empty($userData['phone'])) {
                $content .= "Moengage.add_mobile('" . addslashes($userData['phone']) . "');";
                $content .= "Moengage.add_unique_user_id('" . addslashes($userData['phone']) . "');";
            }
            if (!empty($userData['name'])) {
                $nameParts = explode(' ', trim($userData['name']), 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                $content .= "Moengage.add_user_name('" . addslashes($userData['name']) . "');";

                if (!empty($firstName)) {
                    $content .= "Moengage.add_first_name('" . addslashes($firstName) . "');";
                }
                if (!empty($userData['email'])) {
                    $content .= "Moengage.add_email('" . addslashes($userData['email']) . "');";
                }
                if (!empty($lastName)) {
                    $content .= "Moengage.add_last_name('" . addslashes($lastName) . "');";
                }
            }
            if (!empty($userData['secondary_phone'])) {
                $content .= "Moengage.add_user_attribute('SecondaryMobileNumber', '" . addslashes(ltrim($userData['phone'], '+') ) . "');";
            }
        }

        foreach ($this->events as $event => $data) {
            $content .= "Moengage.track_event('$event', " . json_encode($data) . ");";
        }

        return <<<HTML
        <script>
        if (typeof Moengage !== 'undefined') {
            $content
        }
        </script>
        HTML;
    }

    public function pushScriptsToFooter(array $userData = []): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        add_filter(THEME_FRONT_FOOTER, function (?string $html) use ($userData) {
            return $html . view(EcommerceHelper::viewPath('includes.moengage-pixel-script'))->render() . $this->render($userData);
        }, 999);

        add_filter('ecommerce_checkout_footer', function (?string $html) use ($userData) {
            return $html . SeoHelper::meta()->getAnalytics()->render() . $this->render($userData);
        }, 999);
    }
}
