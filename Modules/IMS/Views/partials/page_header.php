<div class="admin-page-header">
    <div>
        <span class="admin-kicker">IMS WORKSPACE</span>
        <h1><?= esc($pageTitle ?? 'IMS Workspace') ?></h1>
        <p><?= esc($pageSubtitle ?? '') ?></p>
    </div>
</div>

<?php if (session('message')) : ?>
<div style="position:fixed;top:0;left:0;right:0;z-index:9999;">
    <div id="ims-flash-toast"
         class="toast align-items-center text-white border-0 w-100"
         style="background-color:<?= session('message_type') === 'danger' ? '#dc3545' : '#3aab6d' ?>;border-radius:0 0 .6rem .6rem;box-shadow:0 4px 16px rgba(0,0,0,.18);"
         role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" style="font-size:.875rem;font-weight:500;"><?= esc(session('message')) ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var t=document.getElementById('ims-flash-toast');if(t)new bootstrap.Toast(t,{delay:5000}).show();});</script>
<?php endif ?>
