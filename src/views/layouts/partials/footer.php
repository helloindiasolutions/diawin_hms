<footer class="footer mt-auto py-3 text-center">
    <div class="container">
        <span class="text-muted">
            Copyright © <span id="year"></span> 
            <a href="javascript:void(0);" class="text-dark fw-medium"><?= e($_ENV['APP_NAME'] ?? 'Melina HMS') ?></a>. 
            All rights reserved.
        </span>
    </div>
</footer>
<script>document.getElementById('year').textContent = new Date().getFullYear();</script>
