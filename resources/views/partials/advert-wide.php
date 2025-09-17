<?php

declare(strict_types=1);

$advert = null;

if (!empty($ads)) {
    foreach ($ads as $ad) {
        $now = new DateTime();
        $startDate = new DateTime($ad->start_date);
        $endDate = new DateTime($ad->end_date);
        $isActive = $now >= $startDate && $now <= $endDate;
        if ($ad->ad_type === 'landscape' && $ad->is_active && $isActive) {
            $advert = $ad;
            break; // stop at the first "wide" ad
        }
    }
}

?>

<?php if($advert): ?>
<!-- LANDSCAPE ADVERTISEMENT -->
<section class="ad-section">
    <div class="container">
        <div class="ad-container ad-landscape reveal">
            <span class="ad-label">Advertisement</span>
            <a href="<?php echo $advert->target_url ?>" class="ad-content">
                <img src="<?php echo get_image($advert->image_url, "dist/img/no_image.png") ?>" alt="<?php echo $advert->title ?? "Advertisement" ?>" class="w-100 h-100 object-fit-cover">
            </a>
        </div>
    </div>
</section>
<?php endif; ?>