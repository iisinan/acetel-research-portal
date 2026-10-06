<?php

function fixMilestoneWorkflowService() {
    $path = 'app/Services/MilestoneWorkflowService.php';
    $content = file_get_contents($path);

    $old = <<<PHP
        // Active supervisor assignments for this thesis
        \$assignments = \$milestone->thesis?->assignments()
            ->where('status', 'active')
            ->with(['supervisor.user'])
            ->get() ?? collect();

        if (\$assignments->isEmpty()) {
            \$isDirectlyApproved = !empty(\$milestone->getRawOriginal('is_supervisor_approved'))
                || \$milestone->submissions()->whereHas('feedbacks', fn(\$q) => \$q->where('decision', 'approved'))->exists();

            return [
                'has_supervisors' => false,
                'total_assigned' => 0,
                'approved_count' => \$isDirectlyApproved ? 1 : 0,
                'rejected_count' => 0,
                'pending_rereview_count' => 0,
                'pending_count' => 0,
                'is_eligible' => \$isDirectlyApproved,
                'status_label' => \$isDirectlyApproved ? 'Approved' : 'Pending',
                'supervisors' => [],
            ];
        }

        \$supervisorsList = [];
        \$approvedCount = 0;
        \$rejectedCount = 0;
        \$pendingRereviewCount = 0;
        \$pendingCount = 0;

        foreach (\$assignments as \$assignment) {
            \$supUser = \$assignment->supervisor?->user;
            if (!\$supUser) continue;

            \$userId = (string) \$supUser->id;
            \$review = \$reviews[\$userId] ?? null;

            \$status = 'pending';
            \$decision = \$review['decision'] ?? null;
            \$reviewedAt = \$review['reviewed_at'] ?? null;
            \$remarks = \$review['remarks'] ?? null;

            if (\$decision === 'approved') {
                \$status = 'approved';
                \$approvedCount++;
            } elseif (\$decision === 'rejected') {
                \$status = 'rejected';
                \$rejectedCount++;
            } elseif (\$decision === 'pending_re_review') {
                \$status = 'pending_re_review';
                \$pendingRereviewCount++;
            } else {
                // Fallback: check historical feedback for this user
                \$hasFeedbackApproved = \$milestone->submissions()
                    ->whereHas('feedbacks', fn(\$q) => \$q->where('created_by', \$supUser->id)->where('decision', 'approved'))
                    ->exists();

                \$hasFeedbackRejected = \$milestone->submissions()
                    ->whereHas('feedbacks', fn(\$q) => \$q->where('created_by', \$supUser->id)->where('decision', 'revision_required'))
                    ->exists();

                if (\$hasFeedbackRejected && \$milestone->status === 'revision_required') {
                    \$status = 'rejected';
                    \$rejectedCount++;
                } elseif (\$hasFeedbackApproved) {
                    \$status = 'approved';
                    \$approvedCount++;
                } else {
                    \$pendingCount++;
                }
            }
PHP;

    $new = <<<PHP
        // Active supervisor assignments for this thesis
        \$assignments = \$milestone->thesis?->relationLoaded('assignments')
            ? \$milestone->thesis->assignments->where('status', 'active')
            : (\$milestone->thesis?->assignments()->where('status', 'active')->with(['supervisor.user'])->get() ?? collect());

        if (\$assignments->isEmpty()) {
            \$hasAnyFeedbackApproved = false;
            if (\$milestone->relationLoaded('submissions')) {
                foreach (\$milestone->submissions as \$sub) {
                    if (\$sub->relationLoaded('feedback')) {
                        \$fbs = \$sub->feedback instanceof \Illuminate\Support\Collection ? \$sub->feedback : collect([\$sub->feedback])->filter();
                        if (\$fbs->contains(fn(\$f) => \$f->decision === 'approved')) { \$hasAnyFeedbackApproved = true; break; }
                    } elseif (\$sub->relationLoaded('feedbacks')) {
                        if (\$sub->feedbacks->contains(fn(\$f) => \$f->decision === 'approved')) { \$hasAnyFeedbackApproved = true; break; }
                    } else {
                        if (\$sub->feedbacks()->where('decision', 'approved')->exists()) { \$hasAnyFeedbackApproved = true; break; }
                    }
                }
            } else {
                \$hasAnyFeedbackApproved = \$milestone->submissions()->whereHas('feedbacks', fn(\$q) => \$q->where('decision', 'approved'))->exists();
            }

            \$isDirectlyApproved = !empty(\$milestone->getRawOriginal('is_supervisor_approved'))
                || \$hasAnyFeedbackApproved;

            return [
                'has_supervisors' => false,
                'total_assigned' => 0,
                'approved_count' => \$isDirectlyApproved ? 1 : 0,
                'rejected_count' => 0,
                'pending_rereview_count' => 0,
                'pending_count' => 0,
                'is_eligible' => \$isDirectlyApproved,
                'status_label' => \$isDirectlyApproved ? 'Approved' : 'Pending',
                'supervisors' => [],
            ];
        }

        \$supervisorsList = [];
        \$approvedCount = 0;
        \$rejectedCount = 0;
        \$pendingRereviewCount = 0;
        \$pendingCount = 0;

        foreach (\$assignments as \$assignment) {
            \$supUser = \$assignment->supervisor?->user;
            if (!\$supUser) continue;

            \$userId = (string) \$supUser->id;
            \$review = \$reviews[\$userId] ?? null;

            \$status = 'pending';
            \$decision = \$review['decision'] ?? null;
            \$reviewedAt = \$review['reviewed_at'] ?? null;
            \$remarks = \$review['remarks'] ?? null;

            if (\$decision === 'approved') {
                \$status = 'approved';
                \$approvedCount++;
            } elseif (\$decision === 'rejected') {
                \$status = 'rejected';
                \$rejectedCount++;
            } elseif (\$decision === 'pending_re_review') {
                \$status = 'pending_re_review';
                \$pendingRereviewCount++;
            } else {
                // Fallback: check historical feedback for this user
                \$hasFeedbackApproved = false;
                \$hasFeedbackRejected = false;
                
                if (\$milestone->relationLoaded('submissions')) {
                    foreach (\$milestone->submissions as \$sub) {
                        if (\$sub->relationLoaded('feedback')) {
                            \$fbs = \$sub->feedback instanceof \Illuminate\Support\Collection ? \$sub->feedback : collect([\$sub->feedback])->filter();
                            if (\$fbs->contains(fn(\$f) => \$f->created_by == \$supUser->id && \$f->decision === 'approved')) { \$hasFeedbackApproved = true; }
                            if (\$fbs->contains(fn(\$f) => \$f->created_by == \$supUser->id && \$f->decision === 'revision_required')) { \$hasFeedbackRejected = true; }
                        } elseif (\$sub->relationLoaded('feedbacks')) {
                            if (\$sub->feedbacks->contains(fn(\$f) => \$f->created_by == \$supUser->id && \$f->decision === 'approved')) { \$hasFeedbackApproved = true; }
                            if (\$sub->feedbacks->contains(fn(\$f) => \$f->created_by == \$supUser->id && \$f->decision === 'revision_required')) { \$hasFeedbackRejected = true; }
                        } else {
                            if (\$sub->feedbacks()->where('created_by', \$supUser->id)->where('decision', 'approved')->exists()) { \$hasFeedbackApproved = true; }
                            if (\$sub->feedbacks()->where('created_by', \$supUser->id)->where('decision', 'revision_required')->exists()) { \$hasFeedbackRejected = true; }
                        }
                    }
                } else {
                    \$hasFeedbackApproved = \$milestone->submissions()
                        ->whereHas('feedbacks', fn(\$q) => \$q->where('created_by', \$supUser->id)->where('decision', 'approved'))
                        ->exists();

                    \$hasFeedbackRejected = \$milestone->submissions()
                        ->whereHas('feedbacks', fn(\$q) => \$q->where('created_by', \$supUser->id)->where('decision', 'revision_required'))
                        ->exists();
                }

                if (\$hasFeedbackRejected && \$milestone->status === 'revision_required') {
                    \$status = 'rejected';
                    \$rejectedCount++;
                } elseif (\$hasFeedbackApproved) {
                    \$status = 'approved';
                    \$approvedCount++;
                } else {
                    \$pendingCount++;
                }
            }
PHP;

    $content = str_replace($old, $new, $content);
    file_put_contents($path, $content);
    echo "Replaced successfully!\n";
}

fixMilestoneWorkflowService();

