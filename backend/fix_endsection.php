<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$search = <<<EOT
</div>
@endsection
<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="target_milestone_slug" id="global-jump-target">
</form>

@push('scripts')
<script>
    function jumpMilestone(studentId, targetSlug) {
        if (!confirm('Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.')) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("admin/students") }}/' + studentId + '/set-milestone';
        document.getElementById('global-jump-target').value = targetSlug;
        form.submit();
    }
</script>
@endpush
EOT;

$replace = <<<EOT
</div>

<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="target_milestone_slug" id="global-jump-target">
</form>

@push('scripts')
<script>
    function jumpMilestone(studentId, targetSlug) {
        if (!confirm('Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.')) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("admin/students") }}/' + studentId + '/set-milestone';
        document.getElementById('global-jump-target').value = targetSlug;
        form.submit();
    }
</script>
@endpush

@endsection
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
