# Custom package patches

## spatie/laravel-pdf

File:
vendor/spatie/laravel-pdf/src/Drivers/DomPdfDriver.php

Added:

$options->setIsPhpEnabled(
$this->config['is_php_enabled'] ?? false
);

Reason:
Needed for DOMPDF page numbering:

<script type="text/php">
$pdf->page_text(...)
</script>
