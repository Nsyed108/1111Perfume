<?php

namespace Botble\Ecommerce\AdsTracking;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\SeoHelper\Facades\SeoHelper;
use Propaganistas\LaravelPhone\PhoneNumber;

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
        $phone = phone($order->address->phone, $order->address->country);
        $data = [
            'order_id' => str_replace('#','', $order->code),
            'coupon_used' => $order->coupon_code != null ? true : false,
            'coupon_code' => $order->coupon_code,
            'products_count' => $order->products->count('id'),
            'products_quantity' => $order->products->sum('qty'),
            'currency' => get_application_currency()->title,
            'subtotal' => $order->sub_total > 0 ? $order->sub_total*1 : 0,
            'shipping' => $order->shipping_amount > 0 ? $order->shipping_amount*1 : 0,
            'tax' => $order->tax_amount > 0 ? $order->tax_amount*1 : 0,
            'discount' => $order->discount_amount > 0 ? $order->discount_amount*1 : 0,
            'value' => $order->amount > 0 ? $order->amount*1 : 0,
            'customer_name' => $order->address->name,
            'customer_email' => $order->address->email,
            'customer_phone' => $phone->formatE164($order->address->country),
            'customer_address' => $order->address->address,
            'customer_city' => $order->address->city,
            'customer_country' => $order->address->country,

            /** Added for WhatsApp campaign as per business need */
            'SecondaryMobileNumber' => str_replace('+','',$phone->formatE164($order->address->country)),
        ];
        $this->pushEvent('Purchase - Order', $data);

        $order->products->each(function ($item) use ($order)  {
            $this->pushEvent('Purchase - Product', [
                'order_id' => str_replace('#','', $order->code),
                'product_name' => $item->product_name,
                'product_id' => $item->product_id,
                'price' => $item->price > 0 ? $item->price*1 : 0,
                'quantity' => $item->qty,
                'sku' => $item->options['sku'],
                'value' => $item->price * $item->qty,
            ]);
        });

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

    public function render(): string
    {
        if (empty($this->events)) {
            return '';
        }

        $content = '';

        foreach ($this->events as $event => $data) {
            $content .= "Moengage.track_event('$event', " . json_encode($data) . ');';
        }

        return <<<HTML
            <script>
                if (typeof Moengage?.track_event !== 'undefined') {
                    $content
                }
            </script>
        HTML;
    }

    public function pushScriptsToFooter(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        add_filter(THEME_FRONT_FOOTER, function (?string $html) {
            return $html . view(EcommerceHelper::viewPath('includes.moengage-pixel-script'))->render() . $this->render();
        }, 999);

        add_filter('ecommerce_checkout_footer', function (?string $html) {
            return $html . SeoHelper::meta()->getAnalytics()->render() . $this->render();
        }, 999);
    }
}
