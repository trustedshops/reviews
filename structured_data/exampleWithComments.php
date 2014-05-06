<?php

/**
 * This is an example of integrating Trusted Shops Customer Reviews marked with structured data into your website
 * Make sure you replace value of $tsid with your Trusted Shops ID
 * Make sure you have a writing permission to the folder set in $cacheFileName
 * Requires version PHP 5.+
 */

$tsId = 'XA2A8D35838AF5F63E5EB0E05847B1CB8';
$cacheFileName = '/tmp/' . $tsId . '.xml';
$cacheTimeOut = 43200; // half a day
$apiUrl = 'http://www.trustedshops.com/api/ratings/v1/' . $tsId . '.xml';
$xmlFound = false;

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
if ($xml = simplexml_load_file($cacheFileName)) {
    $xPath = "/shop/ratings/result[@name='average']";
    $result = $xml->xpath($xPath);
    $result = (float)$result[0];
    $max = "5.00";
    $count = $xml->ratings["amount"];
    $shopName = $xml->name;
    ?>
    <div itemscope itemtype="http://schema.org/LocalBusiness">
        <a href="http://www.trustedshops.eu/customer-review/" target="_blank">Trusted Shops Customer Reviews</a>:<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"> <span itemprop="ratingValue" class="ratingNote"><?php echo $result;?></span> / <span itemprop="bestRating"><?php echo $max;?> </span> of <span itemprop="ratingCount"><?php echo $count;?></span></span> <a href="https://www.trustedshops.eu/buyerrating/info_<?php echo $tsId; ?>.html" title="<?php echo $shopName;?> customer reviews" target="_blank"><span itemprop="name"><?php echo $shopName;?></span> customer reviews</a>    <?php

    /* Set Locale for Date output */
    setlocale(LC_ALL, 'de_DE');

    foreach ($xml->ratings->opinions[0] as $review) {
        $reviewDateRichSnippets = date('Y-m-d', strtotime($review->date));
        $reviewDateFormatted = strftime('%d. %B %Y', strtotime($review->date));
        $reviewComment = $review->comment;
        $reviewRating = $review->rating[0];
        $reviewRatingString = $review->rating[1];
        if ($review->reaction) {
            $shopReply = $review->reaction->reply;
        }
        ?>
        <div itemprop="review" itemscope itemtype="http://schema.org/Review">
            <meta itemprop="itemreviewed" value="<?php echo $shopName ?>" />
            <span itemprop="dateCreated" content="<?php echo $reviewDateRichSnippets; ?>"><?php echo $reviewDateFormatted; ?></span>.
            <span itemprop="reviewbody"><?php echo $reviewComment; ?></span>
            <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
            <span itemprop="ratingValue"><?php echo $reviewRating; ?></span>/5</span>
        </div>
    <?php
    } ?>
    </div>
    <?php
}
