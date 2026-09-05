<?php
$flashes = Flash::get();
if (!empty($flashes)):
    foreach ($flashes as $type => $messages):
        $styles = [
            'success' => ['bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300', 'icon' => 'fa-circle-check text-emerald-500', 'swal' => 'success'],
            'error' => ['bg' => 'bg-rose-50 text-rose-800 border-rose-300', 'icon' => 'fa-triangle-exclamation text-rose-500', 'swal' => 'error'],
            'info' => ['bg' => 'bg-sky-50 text-sky-800 border-sky-300', 'icon' => 'fa-circle-info text-sky-500', 'swal' => 'info'],
            'warning' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-300', 'icon' => 'fa-triangle-exclamation text-amber-500', 'swal' => 'warning'],
        ];
        $st = $styles[$type] ?? $styles['info'];
        foreach ($messages as $msg):
?>
<div class="mb-4 p-4 rounded-xl border flex items-start gap-3 shadow-xs <?= $st['bg'] ?>">
    <i class="fa-solid <?= $st['icon'] ?> text-lg mt-0.5"></i>
    <div class="flex-1 text-sm font-medium">
        <?= e($msg) ?>
    </div>
    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<script>
if (typeof SwalToast !== 'undefined') {
    SwalToast.fire({
        icon: '<?= $st['swal'] ?>',
        title: '<?= addslashes(e($msg)) ?>'
    });
}
</script>
<?php 
        endforeach;
    endforeach;
endif;
?>

