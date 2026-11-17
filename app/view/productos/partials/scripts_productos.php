<script id="productosData" type="application/json">
<?= json_encode(
    isset($productos) && is_array($productos) ? array_values($productos) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>
