<script>
    $(function () {
        if (typeof Moengage?.track_event !== 'function') {
            return;
        }
        
        const track_event = Moengage.track_event;
        $(document).on('click', '[data-bb-toggle="add-to-cart-in-form"]', function (e) {
            var currentTarget = $(e.currentTarget);
            var form = currentTarget.closest('form');
            
            var price = currentTarget.data('product-price');
            var quantity = form.find('input[name="qty"]').val();

            track_event('Add To Cart', {
                product_id: currentTarget.data('product-id'),
                product_name: currentTarget.data('product-name'),
                quantity: quantity,
                value: price * quantity,
                category: currentTarget.data('product-category'),
                currency: '{{ get_application_currency()->title }}'
            });
        });
        
        $(document).on('click', '[data-bb-toggle="add-to-cart"]', function (e) {
            var currentTarget = $(e.currentTarget);

            track_event('Add To Cart', {
                product_id: currentTarget.data('product-id'),
                product_name: currentTarget.data('product-name'),
                quantity: 1,
                value: currentTarget.data('product-price'),
                category: currentTarget.data('product-category'),
                currency: '{{ get_application_currency()->title }}'
            });
        });
    });
</script>
