<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\BoardingHouse;
use App\Models\ValidationEvidence;
use App\Models\ValidationFinding;
use App\Models\ValidationRecord;
use App\Models\ValidationTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class OsasController extends Controller
{
    public function dashboard()
    {
        $metrics = [
            'pending'    => ValidationTask::where('status', 'assigned')->count(),
            'progress'   => ValidationTask::where('status', 'in-progress')->count(),
            'submitted'  => ValidationRecord::where('status', 'submitted')->count(),
            'accredited' => Accreditation::where('status', 'Accredited')->count(),
            'critical'   => ValidationFinding::where('severity', 'Critical')->count(),
        ];

        $tasks = ValidationTask::with(['boardingHouse','validator','record'])
            ->orderByRaw("case priority when 'High' then 1 when 'Normal' then 2 else 3 end")
            ->orderBy('scheduled_at')
            ->take(6)
            ->get();

        $accreditations = Accreditation::with('boardingHouse')->latest()->take(6)->get();

        $findings = ValidationFinding::with(['record.task.boardingHouse'])
            ->latest()->take(6)->get();

        $workload = User::whereHas('roles', fn($q)=>$q->where('name','osas'))
            ->withCount(['validationTasks as tasks_total', 'validationTasks as tasks_active' => function($q){
                $q->whereIn('status',['assigned','in-progress']);
            }])->get();

        return view('osas.dashboard', compact('metrics','tasks','accreditations','findings','workload'));
    }

    public function validators()
    {
        $validators = User::whereHas('roles', fn($q)=>$q->where('name','osas'))->get();
        return view('osas.validators', compact('validators'));
    }

    public function validatorShow($id)
    {
        $validator = User::findOrFail($id);
        $tasks = ValidationTask::with('boardingHouse')->where('validator_id',$id)->get();
        return view('osas.validator-show', compact('validator','tasks'));
    }

    public function validatorToggle($id)
    {
        $validator = User::findOrFail($id);
        $validator->is_active = ! $validator->is_active;
        $validator->save();
        Session::flash('status','Validator status updated');
        return back();
    }

    public function assignTask(Request $request, $id)
    {
        $request->validate([
            'boarding_house_id'=>'required|exists:boarding_houses,id',
            'scheduled_at'=>'nullable|date',
            'priority'=>'nullable|string'
        ]);
        ValidationTask::create([
            'validator_id'=>$id,
            'boarding_house_id'=>$request->boarding_house_id,
            'scheduled_at'=>$request->scheduled_at,
            'priority'=>$request->priority ?? 'Normal',
            'status'=>'assigned'
        ]);
        Session::flash('status','Task assigned');
        return back();
    }

    public function workbench()
    {
        $tasks = ValidationTask::with(['boardingHouse','validator','record'])->get();
        return view('osas.workbench', compact('tasks'));
    }

    public function validationShow($id)
    {
        $task = ValidationTask::with(['boardingHouse','validator','record.findings','record.evidence'])->findOrFail($id);
        return view('osas.validation-show', compact('task'));
    }

    public function validationStart($id)
    {
        $task = ValidationTask::findOrFail($id);
        $task->status = 'in-progress';
        $task->save();
        $task->record()->firstOrCreate([]);
        Session::flash('status','Validation started');
        return back();
    }

    public function validationSave(Request $request, $id)
    {
        $task = ValidationTask::findOrFail($id);
        $record = $task->record()->firstOrCreate([]);
        $record->update([
            'status'=>'draft',
            'notes'=>$request->input('notes')
        ]);
        Session::flash('status','Draft saved');
        return back();
    }

    public function validationSubmit(Request $request, $id)
    {
        $task = ValidationTask::findOrFail($id);
        $record = $task->record()->firstOrCreate([]);
        $record->update(['status'=>'submitted','notes'=>$request->input('notes')]);
        $task->status = 'completed';
        $task->save();
        Session::flash('status','Findings submitted');
        return back();
    }

    public function validationFinding(Request $request, $id)
    {
        $request->validate([
            'type'=>'required','severity'=>'required','description'=>'required'
        ]);
        $task = ValidationTask::findOrFail($id);
        $record = $task->record()->firstOrCreate([]);
        ValidationFinding::create([
            'record_id'=>$record->id,
            'type'=>$request->type,
            'severity'=>$request->severity,
            'description'=>$request->description,
        ]);
        Session::flash('status','Finding added');
        return back();
    }

    public function validationEvidence(Request $request, $id)
    {
        $request->validate(['evidence'=>'required|file']);
        $task = ValidationTask::findOrFail($id);
        $record = $task->record()->firstOrCreate([]);
        $path = $request->file('evidence')->store('evidence','public');
        ValidationEvidence::create([
            'record_id'=>$record->id,
            'uploaded_by'=>$request->user()->id,
            'path'=>$path,
            'type'=>$request->file('evidence')->getClientOriginalExtension(),
        ]);
        Session::flash('status','Evidence uploaded');
        return back();
    }

    public function accreditation()
    {
        $accreditations = Accreditation::with('boardingHouse')->get();
        return view('osas.accreditation', compact('accreditations'));
    }

    public function accredApprove($id)
    {
        $acc = Accreditation::findOrFail($id);
        $acc->status = 'Accredited';
        $acc->decision_log = 'Approved '.now();
        $acc->save();
        Session::flash('status','Accredited');
        return back();
    }

    public function accredSuspend($id)
    {
        $acc = Accreditation::findOrFail($id);
        $acc->status = 'Suspended';
        $acc->decision_log = 'Suspended '.now();
        $acc->save();
        Session::flash('status','Suspended');
        return back();
    }

    public function accredReinstate($id)
    {
        $acc = Accreditation::findOrFail($id);
        $acc->status = 'Accredited';
        $acc->decision_log = 'Reinstated '.now();
        $acc->save();
        Session::flash('status','Reinstated');
        return back();
    }

    public function reports()
    {
        $metrics = [
            'compliance' => 85,
            'accredited' => Accreditation::where('status','Accredited')->count(),
            'flagged' => ValidationFinding::where('severity','Critical')->count(),
            'avg_time' => 3.5,
        ];
        $tasks = ValidationTask::with('boardingHouse')->get();
        return view('osas.reports', compact('metrics','tasks'));
    }

    public function reportsExport(Request $request)
    {
        Session::flash('status','Report export triggered (CSV)');
        return back();
    }

    public function settings()
    {
        return view('osas.settings');
    }
}
