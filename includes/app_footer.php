<?php /* ─── app_footer.php ─── Scripts + Close body ─── */ ?>

<!-- Live Ticker (populated by JS) -->
<style>
@keyframes ticker-scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.ticker-item{display:inline-flex;align-items:center;gap:6px;padding:0 18px;color:rgba(255,255,255,.5);border-right:1px solid rgba(255,255,255,.06)}
.ticker-item strong{color:#fff}.ticker-item .game-tag{color:rgba(255,255,255,.4)}
.ticker-item .amount{color:#00e676;font-weight:700}
</style>

<!-- Main JS -->
<script src="/assets/js/main.js"></script>

<!-- Page-specific scripts -->
<?php if (isset($extraScripts)) echo $extraScripts; ?>

</body>
</html>
