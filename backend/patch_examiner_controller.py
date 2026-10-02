import re

with open('backend/app/Http/Controllers/Coordinator/ExaminerController.php', 'r') as f:
    content = f.read()

assign_program_pattern = re.compile(r'public function assignProgram.*?\}', re.DOTALL)

new_assign_external = """public function assignExternal(Request $request, ThesisProject $thesis)
    {
        $request->validate([
            'examiner_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $assignment = ExaminerAssignment::updateOrCreate(
                ['thesis_project_id' => $thesis->id, 'type' => 'external_examiner'],
                [
                    'examiner_id' => $request->examiner_id,
                    'assigned_by' => auth()->id(),
                    'active' => true
                ]
            );

            // Keep ThesisProject column synced
            $profile = \\App\\Models\\ExternalExaminerProfile::where('user_id', $request->examiner_id)->first();
            if ($profile) {
                $thesis->update(['external_examiner_profile_id' => $profile->id]);
            }

            // Optional: Auto-approve M10 if needed
            $m10 = $thesis->milestones()->whereHas('template', fn($q) => $q->where('order', 10))->first();
            if ($m10 && $m10->status !== 'approved') {
                $m10->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approvals' => ['Program Coordinator' => ['user_id' => auth()->id(), 'approved_at' => now()->toDateTimeString()]]
                ]);
                $this->workflowService->afterApproval($m10);
            }

            DB::commit();
            return redirect()->back()->with('success', 'External Examiner assigned successfully.');
        } catch (\\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }"""

content = assign_program_pattern.sub(new_assign_external.replace('\\', '\\\\'), content)

with open('backend/app/Http/Controllers/Coordinator/ExaminerController.php', 'w') as f:
    f.write(content)

