import re

with open('backend/app/Models/DefenceEvent.php', 'r') as f:
    content = f.read()

helper_method = """
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Check if a user is an authorized evaluator for this event.
     * Evaluators include: Panel Members, assigned Internal Examiner, and assigned External Examiner.
     */
    public function isAuthorizedEvaluator($userId)
    {
        // 1. Check Panel Members
        if ($this->panelMembers()->where('user_id', $userId)->exists()) {
            return true;
        }

        // 2. Check Thesis Examiners
        $thesis = $this->thesis()->with(['internalExaminerProfile.user', 'externalExaminerProfile.user'])->first();
        
        if ($thesis) {
            if ($thesis->internalExaminerProfile && $thesis->internalExaminerProfile->user_id == $userId) {
                return true;
            }
            if ($thesis->externalExaminerProfile && $thesis->externalExaminerProfile->user_id == $userId) {
                return true;
            }
        }

        return false;
    }
"""

content = content.replace("""    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }""", helper_method)

with open('backend/app/Models/DefenceEvent.php', 'w') as f:
    f.write(content)

