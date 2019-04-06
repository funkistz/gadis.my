<?php

$total = '100';
if($total_products == 0){
     $publish_products_percentage = '0';
     $pending_products_percentage = '0';
     $draft_products_percentage = '0';
     $trashed_products_percentage = '0';
}else {
    $publish_products_percentage = round((float)$publish_products_count / $total_products * $total, 2);
    $pending_products_percentage = round((float)$pending_products_count / $total_products * $total, 2);
    $draft_products_percentage = round((float)$draft_products_count / $total_products * $total, 2);
    $trashed_products_percentage = round((float)$trashed_products_count / $total_products * $total, 2);
}
if($total_products == 0) {
    _e('No products Available.', 'dc-woocommerce-multi-vendor');
} else {
?>
<div class="wcmp_product_stats_wrap">
    
    <div class="p_stats_data" style="border-top:0">
        <ul class="list-group">
            <li class="list-group-item justify-content-between">
                <div class="stat-left-border" style="background-color:#c35244;"></div>
                <p><?php _e('Published', 'dc-woocommerce-multi-vendor');?></p>
                <span class="badge badge-default badge-pill"><?php echo $publish_products_count; ?></span>
            </li>
            <li class="list-group-item justify-content-between">
                <div class="stat-left-border" style="background-color:#a75579;"></div>
                <p><?php _e('Pending', 'dc-woocommerce-multi-vendor');?></p>
                <span class="badge badge-default badge-pill"><?php echo $pending_products_count; ?></span>
            </li>
            <li class="list-group-item justify-content-between">
                <div class="stat-left-border" style="background-color:#d28c4d;"></div>
                <p><?php _e('Draft', 'dc-woocommerce-multi-vendor');?></p>
                <span class="badge badge-default badge-pill"><?php echo $draft_products_count; ?></span>
            </li>
            <li class="list-group-item justify-content-between">
                <div class="stat-left-border" style="background-color:#519a9e;"></div>
                <p><?php _e('Not Approved', 'dc-woocommerce-multi-vendor');?></p>
                <span class="badge badge-default badge-pill"><?php echo $trashed_products_count; ?></span>
            </li>
        </ul>
    </div>
</div>
<?php } 