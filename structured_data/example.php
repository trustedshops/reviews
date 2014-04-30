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
    $xmlFound = true;
}
if ($xmlFound) { ?>
    <a href="http://www.trustedshops.eu/customer-review/" target="_blank">Trusted Shops customer reviews</a>:<span xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Review-aggregate"> <span rel="v:rating"><span property="v:value"><?php echo $result;?> </span> </span> / <span property="v:best"><?php echo $max;?> </span> of <span property="v:votes"><?php echo $count;?> </span> <a href="https://www.trustedshops.com/buyerrating/info_<?php echo $tsId?>.html" title="<?php echo $shopName;?> custom reviews" target="_blank"><?php echo $shopName;?> reviews</a></span>
<?php }
