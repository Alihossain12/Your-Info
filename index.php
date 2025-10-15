/**
 * WooCommerce: "Your Information" section above Delivery Details
 * Fields: sender_name, sender_email, sender_phone
 */

/* 1) UI রেন্ডার: Delivery Details এর ওপরে সেকশন দেখাই */
add_action('woocommerce_checkout_before_customer_details', function () {
    echo '<div class="sender-info wc-block your-information" style="margin:25px 0;padding:18px;border:1px solid #e5e5e5;border-radius:8px;background:#fafafa">';
    echo '<h3 style="margin-top:0">Your Information</h3>';

    $fields = [
        'sender_name'  => [
            'type'        => 'text',
            'label'       => __('Name', 'your-textdomain'),
            'required'    => true,
            'class'       => ['form-row-wide'],
            'autocomplete'=> 'name',
            'placeholder' => __('Your full name', 'your-textdomain'),
        ],
        'sender_email' => [
            'type'        => 'email',
            'label'       => __('Email', 'your-textdomain'),
            'required'    => true,
            'class'       => ['form-row-first'],
            'autocomplete'=> 'email',
            'placeholder' => __('you@example.com', 'your-textdomain'),
        ],
        'sender_phone' => [
            'type'        => 'tel',
            'label'       => __('Phone number', 'your-textdomain'),
            'required'    => true,
            'class'       => ['form-row-last'],
            'autocomplete'=> 'tel',
            'placeholder' => __('e.g. +1 555-123-4567', 'your-textdomain'),
        ],
    ];

    foreach ($fields as $key => $args) {
        $value = isset($_POST[$key]) ? wc_clean(wp_unslash($_POST[$key])) : '';
        woocommerce_form_field($key, $args, $value);
    }

    echo '<div style="clear:both"></div></div>';
});

/* 2) ভ্যালিডেশন: Place order ক্লিক করলেই চেক হবে */
add_action('woocommerce_checkout_process', function () {
    if (empty($_POST['sender_name'])) {
        wc_add_notice(__('Please enter your name.'), 'error');
    }
    if (empty($_POST['sender_email']) || !is_email($_POST['sender_email'])) {
        wc_add_notice(__('Please enter a valid email.'), 'error');
    }
    if (empty($_POST['sender_phone'])) {
        wc_add_notice(__('Please enter your phone number.'), 'error');
    }
});

/* 3) অর্ডারে সেভ */
add_action('woocommerce_checkout_create_order', function ($order, $data) {
    $map = [
        'sender_name'  => 'Sender Name',
        'sender_email' => 'Sender Email',
        'sender_phone' => 'Sender Phone',
    ];
    foreach ($map as $post_key => $label) {
        if (isset($_POST[$post_key])) {
            $order->update_meta_data($post_key, wc_clean(wp_unslash($_POST[$post_key])));
        }
    }
}, 10, 2);

/* 4) Admin Order পেইজে দেখাও */
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    $name  = $order->get_meta('sender_name');
    $email = $order->get_meta('sender_email');
    $phone = $order->get_meta('sender_phone');

    if ($name || $email || $phone) {
        echo '<div class="order_data_column"><h4>Sender / Your Information</h4>';
        if ($name)  echo '<p><strong>Name:</strong> '  . esc_html($name)  . '</p>';
        if ($email) echo '<p><strong>Email:</strong> ' . esc_html($email) . '</p>';
        if ($phone) echo '<p><strong>Phone:</strong> ' . esc_html($phone) . '</p>';
        echo '</div>';
    }
});

/* 5) ইমেইলে দেখাও (customer + admin) */
add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
    $fields['sender_name'] = [
        'label' => __('Sender Name'),
        'value' => $order->get_meta('sender_name'),
    ];
    $fields['sender_email'] = [
        'label' => __('Sender Email'),
        'value' => $order->get_meta('sender_email'),
    ];
    $fields['sender_phone'] = [
        'label' => __('Sender Phone'),
        'value' => $order->get_meta('sender_phone'),
    ];
    return $fields;
}, 10, 3);
