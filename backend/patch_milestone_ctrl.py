import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

# Replace index method
old_index = """    public function index()
    {
        $templates = MilestoneTemplate::with('program')->with(['studentMilestones' => function($q) { $q->whereIn('status', ['in_progress', 'submitted', 'revision_required'])->with(['thesis.student.user', 'thesis.defenceEvents.panelMembers.user', 'submissions']); }])->orderBy('order')->get();
        $supervisors = \App\Models\SupervisorProfile::with('user')->get();
        return view('admin.milestone-templates.index', compact('templates', 'supervisors'));
    }"""

new_index = """    public function index()
    {
        $user = auth()->user();
        $isCoordinator = $user->hasRole('Program Coordinator');
        $coordinatorProgramId = null;

        if ($isCoordinator) {
            $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
            $coordinatorProgramId = $coordinatorProfile ? $coordinatorProfile->program_id : -1;
        }

        $templates = MilestoneTemplate::with('program')
            ->with(['studentMilestones' => function($q) use ($isCoordinator, $coordinatorProgramId) { 
                $q->whereIn('status', ['in_progress', 'submitted', 'revision_required'])
                  ->with(['thesis.student.user', 'thesis.defenceEvents.panelMembers.user', 'submissions']); 
                
                if ($isCoordinator) {
                    $q->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                        $sq->where('program_id', $coordinatorProgramId);
                    });
                }
            }])->orderBy('order')->get();
            
        $supervisors = \\App\\Models\\SupervisorProfile::with('user')->get();
        return view('admin.milestone-templates.index', compact('templates', 'supervisors', 'isCoordinator'));
    }"""

content = content.replace(old_index, new_index)

old_export = """    public function exportStudents(MilestoneTemplate $template)
    {
        $milestones = \\App\\Models\\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required'])
            ->with('thesis.student.user')
            ->get();"""

new_export = """    public function exportStudents(MilestoneTemplate $template)
    {
        $user = auth()->user();
        $isCoordinator = $user->hasRole('Program Coordinator');
        $coordinatorProgramId = null;

        if ($isCoordinator) {
            $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
            $coordinatorProgramId = $coordinatorProfile ? $coordinatorProfile->program_id : -1;
        }

        $query = \\App\\Models\\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required'])
            ->with('thesis.student.user');
            
        if ($isCoordinator) {
            $query->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                $sq->where('program_id', $coordinatorProgramId);
            });
        }
        
        $milestones = $query->get();"""

content = content.replace(old_export, new_export)

with open(path, 'w') as f:
    f.write(content)
