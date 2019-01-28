<?php

/**
 * This is an example of integrating Trusted Shops Customer Reviews marked with structured data into your website
 * Make sure you replace value of $tsid with your Trusted Shops ID
 * Make sure you have a writing permission to the folder set in $cacheFileName
 * Requires version PHP 5.+
 */

$tsId = 'XA2A8D35838AF5F63E5EB0E05847B1CB8';
$cacheFileName = '/tmp/' . $tsId . '.json';
$cacheTimeOut = 43200; // half a day
$apiUrl = 'https://api.trustedshops.com/rest/public/v2/shops/'.$tsId.'/quality/reviews.json';
$reviewsFound = false;

if (!function_exists('cachecheck')) {
    function cachecheck($filename_cache, $timeout = 10800)
    {
        if (file_exists($filename_cache) && time() - filemtime($filename_cache) < $timeout) {
            return true;
        }
        return false;
    }
}

// check if cached version exists
if (!cachecheck($cacheFileName, $cacheTimeOut)) {
    // load fresh from API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    $output = curl_exec($ch);
    curl_close($ch);
    // Write the contents back to the file
    // Make sure you can write to file's destination
    file_put_contents($cacheFileName, $output);
}
if ($jsonObject = json_decode(file_get_contents($cacheFileName), true)) {
    $result = $jsonObject['response']['data']['shop']['qualityIndicators']['reviewIndicator']['overallMark'];
    $count = $jsonObject['response']['data']['shop']['qualityIndicators']['reviewIndicator']['activeReviewCount'];
    $shopName = $jsonObject['response']['data']['shop']['name'];
    $max = "5.00";

    if ($count > 0) {
        $reviewsFound = true;
    }
}
if ($reviewsFound) { ?>
    <div itemscope itemtype="http://schema.org/LocalBusiness">
        <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
            <meta itemprop="name" content="demoshop.trustedshops.com">
            <span itemprop="ratingValue"><?php echo $result;?></span> /
            <span itemprop="bestRating"><?php echo $max;?></span> of
            <span itemprop="ratingCount"><?php echo $count;?> </span>
            <a href="https://www.trustedshops.com/buyerrating/info_<?php echo $tsId?>.html" title="<?php echo $shopName;?> custom reviews" target="_blank"><?php echo $shopName;?> customer reviews | Trusted Shops</a>
        </div>
    </div>
<?php }
