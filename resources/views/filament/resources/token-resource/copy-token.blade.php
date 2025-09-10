<script>
    window.addEventListener('copy-token', event => {
        navigator.clipboard.writeText(event.detail.token);
    });
</script>
