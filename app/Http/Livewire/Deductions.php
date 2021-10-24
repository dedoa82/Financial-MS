<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Deductions extends Component
{
    protected $listeners = ['deleteConfirmed' =>'delete'];

    public $deductions;
    public $ids;
    public $employee_id;
    public $amount;
    public $reason;

    protected $rules = [
        'employee_id' => 'required',
        'amount'      => 'required',
        'reason'      => 'required',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules);
    }

    public function store ()
    {
        try{    
            $this->validate();
            session()->flash('Start-Loading', 'Loading');

            DB::table('salary_changes')->insert([
                'employee_id'   => $this->employee_id,
                'amount'        => -$this->amount,
                'reason'        => $this->reason,
                'status'        => 0,
            ]);

            $this->resetFields();

            session()->forget('Start-Loading');
            $this->emit('added-successfully');
            $this->emit('Success-Alert');
            
        } catch (\Exception $ex) {
            session()->forget('Start-Loading');
            $this->emit('added-successfully');
            $this->emit('Error-Alert');
        }
    }

    public function resetFields ()
    {
        $this->employee_id  = '';
        $this->amount       = '';
        $this->reason       = '';
    }

    public function edit ($id)
    {
        $extra = DB::table('salary_changes_emp')->where('change_id', $id)->first();

        $this->ids          = $extra->change_id;
        $this->employee_id  = $extra->employee_id;
        $this->amount       = $extra->amount;
        $this->reason       = $extra->reason;
    }

    public function update ()
    {
        $this->validate();

        try{    
            DB::table('salary_changes')
            ->where('id', $this->ids)
            ->update([
                'employee_id'   => $this->employee_id,
                'amount'        => $this->amount,
                'reason'        => $this->reason,
            ]);

            $this->resetFields();
            $this->emit('Toast-Alert');
            
        } catch (\Exception $ex) {
            $this->emit('Error-Alert');
        }

        $this->emit('updated-successfully');
    }

    function confirmDelete ($id)
    {
        $this->emit('Are-You-Sure');
        $this->ids = $id;
    }

    function delete ()
    {
        DB::table('salary_changes')
        ->where('id', $this->ids)->delete();
    }

    public function render()
    {
        $this->deductions = DB::table('salary_changes_emp')
        ->where('status', 0)->orderBy('change_id', 'desc')->get();

        return view('livewire.deductions');
    }
}
