<?php
/**
 * Custom Affiliate Redirect Handler for WHMCS
 * Tracks affiliate click and redirects to the main website.
 */

use WHMCS\Affiliate\Referrer;
use WHMCS\Carbon;
use WHMCS\Cookie;

define("CLIENTAREA", true);
require("init.php");

$aff = $whmcs->get_req_var('aff');

// Track affiliate if ID is present
if ($aff) {
    // Update visitor count
    update_query("tblaffiliates", ["visitors" => "+1"], ["id" => (int)$aff]);

    // Set affiliate tracking cookie for 3 months
    Cookie::set('AffiliateID', $aff, '3m');

    // Get referrer
    $referrer = trim($_SERVER['HTTP_REFERER'] ?? '');

    // Log the hit
    Referrer::firstOrCreate([
        'affiliate_id' => $aff,
        'referrer' => $referrer,
    ])->hits()->create([
        'affiliate_id' => $aff,
        'created_at' => Carbon::now()->toDateTimeString(),
    ]);

    // Trigger the WHMCS hook
    run_hook("AffiliateClickthru", [
        'affiliateId' => $aff,
    ]);
}

// Always redirect to the main website
header("HTTP/1.1 301 Moved Permanently");
header("Location: https://www.yourdomain.com/", true, 301);
exit;
