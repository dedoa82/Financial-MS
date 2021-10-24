<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Imports extends Component
{
    protected $listeners = ['deleteConfirmed' =>'delete'];

    public $imports;
    public $ids;
    public $client;
    public $amount;
    public $reason;
    public $receipt;

    protected $rules = [
        'amount'      => 'required',
        'client'      => 'required',
        'reason'      => 'required',
        'receipt'     => 'required',
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

            DB::table('financial_operations')->insert([
                'amount' => $this->amount,
                'client' => $this->client,
                'reason' => $this->reason,
                'receipt' => $this->receipt,
                'status' => 1,
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
        $this->amount   = '';
        $this->client   = '';
        $this->reason   = '';
        $this->receipt  = '';
    }

    public function edit ($id)
    {
        $export = DB::table('financial_operations')->where('id', $id)->first();

        $this->ids      = $export->id;
        $this->amount   = $export->amount;
        $this->client   = $export->client;
        $this->reason   = $export->reason;
        $this->receipt  = $export->receipt;
    }

    public function update ()
    {
        $this->validate();

        try{    
            DB::table('financial_operations')
            ->where('id', $this->ids)
            ->update([
                'amount' => $this->amount,
                'client' => $this->client,
                'reason' => $this->reason,
                'receipt'=> $this->receipt,
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
        DB::table('financial_operations')
        ->where('id', $this->ids)->delete();
    }

    public function render()
    {
        $this->imports = DB::table('financial_operations')
        ->where('status', 1)->orderBy('id', 'desc')->get();      

        return view('livewire.imports');
    }
}
