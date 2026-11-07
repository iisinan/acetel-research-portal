<x-mail::message>

@if($isSeminarCourse)
# Official Appointment: Examiner for Seminar as a Course

Dear **{{ $notifiable->name }}**,

You have been officially appointed as an **Examiner** for the upcoming **Seminar as a Course** presentations on the ACETEL Thesis Monitoring Portal.

@if(!empty($disciplineGroupLabel))
You have been designated to examine candidate presentations in the **{{ $disciplineGroupLabel }}**.
@endif

@if($studentCount > 0)
You have been allocated **{{ $studentCount }}** candidate presentation(s) for examination.
@endif

<x-mail::panel>
## 📌 Appointment Overview

| | |
|---|---|
| **Role** | Seminar Examiner |
| **Milestone** | Seminar as a Course |
@if(!empty($disciplineGroupLabel))
| **Discipline Track** | {{ $disciplineGroupLabel }} |
@endif
| **Candidates Allocated** | {{ $studentCount > 0 ? $studentCount . ' student(s)' : 'Active Cohort' }} |
| **Examination Mode** | Online via Zoom & Portal Presentation Tab |
| **Presentation Page** | [Open Presentation Schedule]({{ $presentationUrl }}) |
</x-mail::panel>

@if(!empty($customMessage))
<x-mail::panel>
### 💬 Message from Academic Administration
{{ $customMessage }}
</x-mail::panel>
@endif

---

## 📋 EXAMINER GUIDE: WHAT TO DO

### 1️⃣ What to Do Today (Preparation Phase)
- **Confirm Portal Access:** Log in to the [ACETEL Research Portal]({{ config('app.url') }}) using your faculty credentials and ensure you can access your account.
- **Access Candidate Documents:** In the portal, navigate to the **Presentations** tab (or check the *Scheduled Presentations* section in the sidebar menu) to preview your allocated candidates' submitted seminar manuscripts and presentation slide decks in advance.
- **Review in Advance:** Review each candidate's write-up and slides before presentation day so you can prepare your observations, critiques, and questions.
- **Verify Timetable:** Check the presentation schedule on the Presentations tab to note the scheduled date, time slot, and order of presenters for your session.

### 2️⃣ On the Day of the Presentation
- **Where to Find Your Zoom Link:**
  - On the presentation day, log in to the portal and click the **"Presentations"** tab in the top navigation or sidebar (or click the button below).
  - Open the active **Seminar as a Course Presentation** session page.
  - The live **Zoom Meeting Link** is prominently displayed in the presentation header banner and directly on each presenter's row on the timetable.
  - Click **"Join Zoom Session"** to enter the virtual examination room. Please connect **10–15 minutes early** to test your microphone, camera, and network connection.
- **Conduct the Examination:**
  - Listen attentively to each candidate's live presentation and lead or contribute to the questioning and constructive feedback session during their allotted Q&A window.
- **Grade Each Student After Their Presentation on the Presentation Tab:**
  - Keep the **Presentations** tab open on your browser during the live session.
  - **Immediately after each candidate finishes their presentation**, locate the candidate on the roster on the **Presentations** tab.
  - Click the green **"Score"** (or **"Grade"**) button on their row.
  - Complete the scoring rubric matrix, enter your individual criterion marks and evaluative remarks, and submit the evaluation.
  - **Important:** Ensure every assigned student is evaluated and graded before the session concludes so that the candidate's final seminar grade can be recorded and their academic progression processed without delay.

<x-mail::button :url="$presentationUrl" color="primary">
Open Presentations Tab & Timetable →
</x-mail::button>

If you have any scheduling conflicts or technical questions, please contact the Program Coordinator or System Administrator through the portal messaging system.

Best regards,

**The ACETEL Directorate & Postgraduate Studies Committee**  
*National Open University of Nigeria (NOUN)*  
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You received this official academic notification because you were nominated as an Examiner for Seminar as a Course presentations on the ACETEL Thesis Monitoring Portal.
</x-mail::subcopy>

@else
# Official Nomination: Examiner for {{ $template->name ?? 'Presentations' }}

Dear **{{ $notifiable->name }}**,

You have been officially nominated as an **Examiner** for the upcoming **{{ $template->name ?? 'Presentations' }}** presentations on the ACETEL Thesis Monitoring System.

@if($studentCount > 0)
You have been assigned to evaluate **{{ $studentCount }}** student(s).
@endif

<x-mail::panel>
## 📌 Nomination Details

| | |
|---|---|
| **Role** | Examiner |
| **Milestone** | {{ $template->name ?? 'Presentations' }} |
| **Candidates Allocated** | {{ $studentCount > 0 ? $studentCount . ' student(s)' : 'Active Cohort' }} |
| **Portal Dashboard** | [Open Dashboard]({{ $dashboardUrl }}) |
</x-mail::panel>

@if(!empty($customMessage))
<x-mail::panel>
### 💬 Message from Coordinator / Administrator
{{ $customMessage }}
</x-mail::panel>
@endif

### 📋 Your Responsibilities
- **Attendance:** Please be present at the scheduled presentation sessions.
- **Review:** Review the candidates' manuscripts and presentation materials in your portal prior to the event.
- **Evaluation:** Score the students using the rubrics provided in your dashboard after their presentations.

<x-mail::button :url="$dashboardUrl" color="primary">
View Your Schedule & Dashboard →
</x-mail::button>

Thank you for your service and dedication to academic excellence.

Best regards,

**The ACETEL Directorate**  
*Thesis Monitoring & Research Excellence*

<x-mail::subcopy>
You received this notification because you were appointed as an examiner for {{ $template->name ?? 'Presentations' }} on the ACETEL Thesis Monitoring Portal.
</x-mail::subcopy>
@endif

</x-mail::message>
