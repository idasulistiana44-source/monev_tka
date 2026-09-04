<footer class="app-footer">
    <div class="footer-left">
        <strong>Monev TKA</strong>
        <span class="footer-separator">|</span>
        <span>Monitoring dan Evaluasi Pelaksanaan TKA SMA</span>
    </div>
    <div class="footer-right">
        <?= date('Y') ?>
    </div>
</footer>
<script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/js/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/js/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/chartjs/chart.umd.min.js') ?>"></script>
<script src="<?= base_url('assets/js/layout.js') ?>"></script>
<script src="<?= base_url('assets/js/notification.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<?= $this->renderSection('scripts') ?>
<script>
const BASE_URL="<?= base_url('/') ?>";
</script>
<script>
    window.BASE_URL = '<?= base_url() ?>/';
    window.CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
    window.CSRF_HASH = '<?= csrf_hash() ?>';
</script>
<?php if(!empty($pageAsset)): ?>
<script src="<?= base_url('assets/js/'.$pageAsset.'.js') ?>?v=<?= time() ?>"></script>
<?php endif; ?>
</body>
</html>

