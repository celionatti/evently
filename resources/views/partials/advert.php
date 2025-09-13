<?php

?>

<!-- PORTRAIT ADVERTISEMENT -->
<section class="ad-section">
    <div class="container">
        <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="ad-container ad-portrait reveal">
            <span class="ad-label">Advertisement</span>
            <a href="#" class="ad-content">
                <img src="<?php echo get_image("dist/img/no_image.png", "dist/img/no_image.png") ?>" alt="<?php echo $advert->title ?? "Advertisement" ?>" class="w-100 h-100 object-fit-cover">
            </a>
            </div>
        </div>
        </div>
    </div>
</section>