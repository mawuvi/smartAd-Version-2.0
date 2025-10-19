<?php
// Simple test API for template download - bypasses all authentication
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="rates_template_test.csv"');

// Create CSV content directly
$csvContent = "Publication Code,Publication Name,Ad Category,Ad Size,Page Position,Color Type,Base Rate,Currency,Effective Date,Expiry Date,Status,Notes\n";
$csvContent .= "DG,Daily Graphic,Display,Full Page,Front Page,Color,500.00,GHS,2024-01-01,2024-12-31,Active,Premium placement\n";
$csvContent .= "GT,Ghanaian Times,Display,Half Page,Inside,B&W,250.00,GHS,2024-01-01,2024-12-31,Active,Standard rate\n";
$csvContent .= "GM,Graphic Showbiz,Classified,Quarter Page,Back Page,Color,150.00,GHS,2024-01-01,2024-12-31,Active,Entertainment section\n";

echo $csvContent;
exit;
