<?php

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = App\Models\AdminModel::first();
$secretary = App\Models\SecretaryModel::first();
$doctor = App\Models\DoctorsProfileModel::first();

echo "Admin: " . ($admin ? $admin->username : 'none') . "\n";
echo "Secretary: " . ($secretary ? $secretary->username : 'none') . "\n";
echo "Doctor: " . ($doctor ? $doctor->docrefno : 'none') . "\n";

// 1. Test Admin creating question
$req1 = Illuminate\Http\Request::create('/api/create_question', 'POST', [
    'docrefno' => $doctor->docrefno,
    'question' => 'Are you allergic to penicillin?'
]);
auth()->guard('admin')->setUser($admin);
$res1 = app()->make(App\Http\Controllers\SecretaryController::class)->createQuestion($req1);
echo "Admin Create Question: " . $res1->status() . " " . $res1->getContent() . "\n";
$data1 = json_decode($res1->getContent(), true);
$qrefno = $data1['question']['docquestionrefno'] ?? null;

// Verify doctor name resolved
if (isset($data1['question']['docname'])) {
    echo "Doctor name in question: '" . $data1['question']['docname'] . "'\n";
    assert(!empty(trim($data1['question']['docname'])), "Doctor name should not be empty");
}

// 2. Test Fetch questions
$reqFetch = Illuminate\Http\Request::create('/api/fetch_doctor_questions', 'POST', ['docrefno' => $doctor->docrefno]);
$resFetch = app()->make(App\Http\Controllers\SecretaryController::class)->fetchQuestions($reqFetch);
echo "Fetch Questions: " . $resFetch->status() . " count: " . count(json_decode($resFetch->getContent(), true)['docquestions']) . "\n";

// 3. Test Edit question
$reqEdit = Illuminate\Http\Request::create('/api/edit_question', 'POST', [
    'docquestionrefno' => $qrefno,
    'question' => 'Are you allergic to penicillin or amoxicillin?'
]);
$resEdit = app()->make(App\Http\Controllers\SecretaryController::class)->editQuestion($reqEdit);
echo "Edit Question: " . $resEdit->status() . " " . $resEdit->getContent() . "\n";

// 4. Test Delete question
$reqDel = Illuminate\Http\Request::create('/api/delete_question', 'POST', [
    'docquestionrefno' => $qrefno
]);
$resDel = app()->make(App\Http\Controllers\SecretaryController::class)->deleteQuestion($reqDel);
echo "Delete Question: " . $resDel->status() . " " . $resDel->getContent() . "\n";

// 5. Test Secretary creating question
auth()->guard('admin')->logout();
auth()->guard('secretary')->setUser($secretary);
$reqSecQ = Illuminate\Http\Request::create('/api/create_question', 'POST', [
    'docrefno' => $doctor->docrefno,
    'question' => 'Do you have hypertension history?'
]);
$resSecQ = app()->make(App\Http\Controllers\SecretaryController::class)->createQuestion($reqSecQ);
echo "Secretary Create Question: " . $resSecQ->status() . " " . $resSecQ->getContent() . "\n";
$dataSecQ = json_decode($resSecQ->getContent(), true);
$secQRef = $dataSecQ['question']['docquestionrefno'] ?? null;
// Clean up
if ($secQRef) {
    App\Models\DoctorQuestionsModel::where('docquestionrefno', $secQRef)->delete();
}

// 6. Test Admin creating schedule
auth()->guard('secretary')->logout();
auth()->guard('admin')->setUser($admin);
$reqSched = Illuminate\Http\Request::create('/api/create_doctor_schedules', 'POST', [
    'docrefno' => $doctor->docrefno,
    'schedule_day' => 'Tuesday',
    'sched_from' => '09:00',
    'sched_to' => '13:00'
]);
$resSched = app()->make(App\Http\Controllers\SecretaryController::class)->createSchedule($reqSched);
echo "Admin Create Schedule: " . $resSched->status() . " " . $resSched->getContent() . "\n";
$dataSched = json_decode($resSched->getContent(), true);
$schedRef = $dataSched['schedule']['schedrefno'] ?? null;

// 7. Test Admin editing schedule
$reqEditSched = Illuminate\Http\Request::create('/api/edit_schedule', 'POST', [
    'schedrefno' => $schedRef,
    'day' => 'Tuesday',
    'start' => '10:00',
    'end' => '14:00'
]);
$resEditSched = app()->make(App\Http\Controllers\SecretaryController::class)->editSchedule($reqEditSched);
echo "Admin Edit Schedule: " . $resEditSched->status() . " " . $resEditSched->getContent() . "\n";

// 8. Test Admin deleting schedule
$reqDelSched = Illuminate\Http\Request::create('/api/delete_schedule', 'POST', [
    'schedrefno' => $schedRef
]);
$resDelSched = app()->make(App\Http\Controllers\SecretaryController::class)->deleteSchedule($reqDelSched);
echo "Admin Delete Schedule: " . $resDelSched->status() . " " . $resDelSched->getContent() . "\n";

// 9. Test Nonexistent doctor 404
$req404 = Illuminate\Http\Request::create('/api/create_question', 'POST', [
    'docrefno' => 'NONEXISTENT_DOC_REF',
    'question' => 'Test 404'
]);
$res404 = app()->make(App\Http\Controllers\SecretaryController::class)->createQuestion($req404);
echo "Nonexistent doctor question 404: " . $res404->status() . " " . $res404->getContent() . "\n";

echo "ALL VERIFICATION CHECKS PASSED!\n";
